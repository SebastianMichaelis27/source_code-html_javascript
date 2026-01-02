<style>
 .multiple-select
  { position : relative;
    font-family : Arial, sans-serif;
    width : max-content;
  }
 .multiple-select::after
  { content : "▼";
    position : absolute;
    right : 8px;
    top : 50%;
    transform : translateY(-50%);
    font-size : 12px;
    pointer-events : none;
    color : #555;
  }
 .multiple-select .selected-tags
  { display : flex;
    flex-wrap : wrap;
    gap : 4px;
    padding : 6px 24px 6px 6px;
    border : 1px solid #ccc;
    min-height : 36px;
    cursor : text;
    box-sizing : border-box;
  }
 .multiple-select .selected-tags input
  { border : none;
    outline : none;
    flex : 1;
    min-width : 60px;
    font-size : 14px;
  }
 .multiple-select .tag
  { background : #e0e0e0;
    padding : 2px 6px;
    border-radius : 4px;
    font-size : 12px;
    display : flex;
    align-items : center;
  }
 .multiple-select .tag span
  { margin-left : 4px;
    cursor : pointer;
    font-weight : bold;
  }
 .multiple-select .options
  { list-style : none;
    margin : 0;
    padding : 0;
    border : 1px solid #ccc;
    max-height : 160px;
    overflow-y : auto;
    display : none;
    position : absolute;
    width : 100%;
    background : #fff;
    z-index : 99;
  }
 .multiple-select .options li
  { padding : 8px 28px 8px 8px;
    cursor : pointer;
    position : relative;
  }
 .multiple-select .options li:hover
  { background : #f0f0f0;
  }
 .multiple-select .options li.active
  { background : #e0e0e0;
  }
 .multiple-select .options li.highlight
  { background : #d0ebff;
  }
 .multiple-select .options li.active::after
  { content : "✔";
    position : absolute;
    right : 6px;
    color : green;
  }
</style>

<select id='kota' name='kota' multiple>
 <option value='1'>Jakarta</option>
 <option value='2'>Bandung</option>
 <option value='3'>Surabaya</option>
 <option value='4'>Medan</option>
 <option value='5'>Makassar</option>
</select>

<script>

 initMultipleSelect('kota');

 function initMultipleSelect(id, allow_new = false, url = null, parameter = {})
  { const select = document.getElementById(id);
    if(!select) return;

    const wrapper = document.createElement('div');
    wrapper.className = 'multiple-select';
    wrapper.style.width = select.style.width;

    const tagBox = document.createElement('div');
    tagBox.className = 'selected-tags';

    const input = document.createElement('input');
    input.type = 'text';
    input.placeholder = 'Pilih...';
    input.autocomplete = 'off';

    tagBox.appendChild(input);

    const hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = select.name;
    hidden.id = select.id;
    hidden.value = '[]';

    const ul = document.createElement('ul');
    ul.className = 'options';

    [...select.options].forEach(opt => {
     const li = document.createElement('li');
     li.textContent = opt.text;
     li.dataset.value = opt.value;
     ul.appendChild(li);
    });

    wrapper.append(tagBox, hidden, ul);
    select.replaceWith(wrapper);

    let activeIndex = -1;

    const getData = () => JSON.parse(hidden.value || '[]');
    const setData = data => hidden.value = JSON.stringify(data);

    function getVisibleItems()
     { return [...ul.querySelectorAll('li')].filter(li => li.style.display !== 'none');
     }

    function showOptions()
     { ul.style.display = 'block';
       sync();
     }

    tagBox.addEventListener('click', () => {
     input.focus();
     showOptions();
    });

    input.addEventListener('focus', showOptions);

    if(url == null)
     { input.addEventListener('input', () => {
        //menambahkan item baru
        if(ul.querySelectorAll('.additional-option').length > 0)
         { ul.querySelector('.additional-option').remove();
         }
        if(allow_new && input.value !== "")
         { const li = document.createElement('li');
           li.textContent = input.value;
           li.dataset.value = input.value;
           li.className = 'additional-option';
           ul.appendChild(li);
         }

        const q = input.value.toLowerCase();
        ul.querySelectorAll('li').forEach(li => {
         li.style.display = li.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
        activeIndex = -1;
        showOptions();
       });
     }
    else
     { let searchTimeout = null;
       input.addEventListener('input', () => {

        const keyword = input.value.trim();
        clearTimeout(searchTimeout);
        parameter['search'] = input.value;

        searchTimeout = setTimeout(() => {
         if(keyword.length === 0)
          { ul.innerHTML = '';
            return;
          }
         ul.innerHTML = '<li>Loading...</li>';
         fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams(parameter).toString()
         })
         .then(res => {
          if(!res.ok)
           { throw new Error('HTTP error ' + res.status);
           }
          return res.json();
         })
         .then(data => {
          ul.innerHTML = '';
          if(!Array.isArray(data) || !data.length)
           { const li = document.createElement('li');
             li.textContent = 'Tidak ditemukan';
             li.style.pointerEvents = 'none';
             ul.appendChild(li);
             return;
           }
          data.forEach(row => {
           const li = document.createElement('li');
           li.textContent = row.label;
           li.dataset.value = row.value;
           ul.appendChild(li);
          });
          activeIndex = -1;
         })
         .catch(err => {
          ul.innerHTML = `
           <li style="pointer-events:none;color:red" class='option-error'>
            Koneksi Gagal
           </li>
          `;
          ul.style.display = 'block';
         });
        }, 300);

        showOptions();
       });
     }

    /* ===== KEYBOARD NAVIGATION ===== */
    input.addEventListener('keydown', e => {
     const items = getVisibleItems();
     if(!items.length) return;

     if(e.key === 'ArrowDown')
      { if(ul.style.display == 'none')
         { showOptions();
         }
        e.preventDefault();
        activeIndex = (activeIndex + 1) % items.length;
        setHighlight(items);
      }

     if(e.key === 'ArrowUp')
      { if(ul.style.display == 'none')
         { showOptions();
         }
        e.preventDefault();
        activeIndex = (activeIndex - 1 + items.length) % items.length;
        setHighlight(items);
      }

     if(e.key === 'Enter')
      { if(ul.style.display == 'none')
         { showOptions();
         }
        e.preventDefault();
        if(activeIndex > -1) {
          items[activeIndex].click();
          hideOptions();
        }
      }

     if((e.key === 'Backspace' || e.key === 'Delete') && input.value === '')
      { e.preventDefault();
        removeLastSelected();
      }
    });

    function setHighlight(items)
     { items.forEach(li => li.classList.remove('highlight'));
       const active = items[activeIndex];
       active.classList.add('highlight');
       active.scrollIntoView({ block: 'nearest' });
     }

    /* ===== CLICK OPTION ===== */
    ul.addEventListener('click', e => {
     if(e.target.tagName !== 'LI') return;

     const value = e.target.dataset.value;
     const text = e.target.textContent;

     let data = getData();
     const idx = data.findIndex(d => d.value === value);

     if(idx > -1)
      { data.splice(idx, 1);
        e.target.classList.remove('active');
      }
     else
      { data.push({ value, text });
        e.target.classList.add('active');
      }

     setData(data);
     input.value = '';
     activeIndex = -1;

     resetOptions();
     renderTags();
    });

    input.addEventListener('blur', () => {
     setTimeout(() => {
      input.value = '';
     }, 150); // delay kecil agar klik tetap kebaca
    });

    document.addEventListener('click', e => {
     if(!e.target.closest('.multiple-select'))
      { ul.style.display = 'none';
        activeIndex = -1;
      }
    });

    function renderTags()
     { // hapus tag lama
       tagBox.querySelectorAll('.tag').forEach(t => t.remove());
       const data = getData();
       data.forEach(item => {
        // cari option di list
        let li = ul.querySelector(`li[data-value="${item.value}"]`);
        // JIKA TIDAK ADA (misalnya dari hidden / additional)
        if(!li)
         { li = document.createElement('li');
           li.textContent = item.text;
           li.dataset.value = item.value;
           li.className = 'active additional-option';
           ul.appendChild(li);
         }
        li.classList.add('active');
        const tag = document.createElement('div');
        tag.className = 'tag';
        tag.innerHTML = `${item.text}<span>×</span>`;
        tag.querySelector('span').onclick = e => {
         e.stopPropagation();
         setData(getData().filter(d => d.value !== item.value));
         li.classList.remove('active');
         renderTags();
        };
        tagBox.insertBefore(tag, input);
       });
     }

    function sync()
     { const data = getData();
       ul.querySelectorAll('li').forEach(li => {
        li.classList.toggle(
         'active',
         data.some(d => d.value === li.dataset.value)
        );
        li.classList.remove('highlight');
       });
       renderTags();
     }

    function hideOptions()
     { ul.style.display = 'none';
       activeIndex = -1;
     }

    function removeLastSelected()
     { const data = getData();
       if(!data.length) return;
       const last = data[data.length - 1];
       const li = ul.querySelector(`li[data-value="${last.value}"]`);
       data.pop();
       setData(data);
       if(li) li.classList.remove('active');
       renderTags();
     }

    function resetOptions()
     { // hapus additional option
       ul.querySelectorAll('.additional-option').forEach(li => li.remove());
       // tampilkan semua option
       ul.querySelectorAll('li').forEach(li => {
        li.style.display = '';
        li.classList.remove('highlight');
       });
       activeIndex = -1;
       input.value = '';
     }
  }
</script>