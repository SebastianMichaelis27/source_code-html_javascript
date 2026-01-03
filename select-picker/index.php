<style>
.custom-select
 { position : relative;
   font-family : Arial, sans-serif;
   display : inline-block;
 }
.custom-select::after {
  content: "▼";
  position: absolute;
  right: 5px;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  font-size: 12px;
  color: #555;
}
.custom-select .options
 { list-style : none;
   margin : 0;
   padding : 0;
   border : 1px solid #ccc;
   max-height : 150px;
   overflow-y : auto;
   display : none;
   position : absolute;
   width : max-content;
   min-width : 100%;
   background : #fff;
   z-index : 10;
 }
.custom-select .options li
 { padding : 8px 22px 8px 8px;
   cursor : pointer;
 }
.custom-select .options li:hover
 { background : #f0f0f0;
 }
.custom-select .options li.active
 { background : #e0e0e0;
 }
</style>

<select id='ini'>
 <option value='1' selected>Jakarta</option>
 <option value='2'>Bandung</option>
 <option value='3'>Surabaya</option>
 <option value='4'>Medan</option>
 <option value='5'>Makassar</option>
</select>

<select id='ini2'>
</select>

<!--
<div class="custom-select">
  <input type="text" id="searchSelect" placeholder="Pilih kota..." autocomplete="off">
  <input type="hidden" class="selectedValue">
  <ul class="options">
    <li data-value="1">Jakarta</li>
    <li data-value="2">Bandung</li>
    <li data-value="3">Surabaya</li>
    <li data-value="4">Medan</li>
    <li data-value="5">Makassar</li>
  </ul>
</div>
-->
<br>
<button type='button' onclick="alert(document.getElementById('ini').value);">Ini</button>
<button type='button' onclick="alert(document.getElementById('ini2').value);">Ini 2</button>

<script>

 setSelectChosen(document.querySelector('#ini'), false);
 setSelectChosen(document.querySelector('#ini2'), false, 'get.php', {});

 function setSelectChosen(select, allow_new = false, url = null, parameter = {})
  { if(!select) return;

    const newSelect = select.cloneNode(true);
    newSelect.style.display = 'none';

    const custom_select = document.createElement('div');
    custom_select.className = 'custom-select';

    const input = document.createElement('input');
    input.type = 'text';
    input.style.paddingRight = '18px';
    input.placeholder = 'Pilih...';
    input.autocomplete = 'new-password';
    if(select.value !== "")
     { input.value = select.options[select.selectedIndex].text;
     }
    custom_select.appendChild(input);

    const options = document.createElement('ul');
    options.className = 'options';

    Array.from(select.options).forEach(opt => {
     const li = document.createElement('li');
     li.dataset.value = opt.value;
     li.textContent = opt.text;
     options.appendChild(li);
    });

    custom_select.appendChild(options);

    custom_select.appendChild(newSelect);
    select.replaceWith(custom_select);

    input.addEventListener('focus', () => {
     input.select();
     resetOptions();
     options.style.display = 'block';
     ensureOptionVisible(options);
    });

    if(url == null)
     { input.addEventListener('input', () => {
        const filter = input.value.toLowerCase();
        const items = options.querySelectorAll('li');
        items.forEach(item => {
         const text = item.textContent.toLowerCase();
         item.style.display = text.includes(filter) ? '' : 'none';
        });
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
            { options.innerHTML = '';
              options.style.display = 'none';
              return;
            }
           options.innerHTML = '<li>Loading...</li>';
           options.style.display = 'block';

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
            options.innerHTML = '';
            if(!Array.isArray(data) || !data.length)
             { const li = document.createElement('li');
               li.textContent = 'Tidak ditemukan';
               li.style.pointerEvents = 'none';
               options.appendChild(li);
               return;
             }
            data.forEach(row => {
             const li = document.createElement('li');
             li.textContent = row.label;
             li.dataset.value = row.value;
             options.appendChild(li);
            });
            activeIndex = -1;
           })
           .catch(err => {
            options.innerHTML = `
             <li style="pointer-events:none;color:red" class='option-error'>
              Koneksi Gagal
             </li>
            `;
            options.style.display = 'block';
           });
         }, 300);

       });
     }

     input.addEventListener('blur', () => {
      setTimeout(() => {
       selectOption();
      }, 150); // delay kecil agar klik tetap kebaca
    });

    document.addEventListener('mousedown', e => {
     if(!e.target.closest('.custom-select'))
      { selectOption();
      }
    });

    function selectOption()
     { const text = input.value.trim().toLowerCase();
       const items = options.querySelectorAll('li');
       let matchedItem = null;
       items.forEach(item => {
        if(item.textContent.toLowerCase() === text)
         { matchedItem = item;
         }
       });
       if(matchedItem)
        { setSelectValue(newSelect, matchedItem.dataset.value);
        }
       else
        { if(allow_new)
           { setSelectValue(newSelect, input.value);
           }
          else
           { input.value = '';
             newSelect.value = '';
           }
        }
       resetOptions();
       options.style.display = 'none';
     }

    function resetOptions() {
     if(options.querySelectorAll('.option-error').length > 0)
      { options.querySelector('.option-error').remove();
      }
     const items = options.querySelectorAll('li');
     items.forEach(li => {
      li.style.display = '';
      li.classList.remove('active');
     });
     activeIndex = -1;
    }

    options.addEventListener('click', e => {
     if(e.target.tagName === 'LI')
      { input.value = e.target.textContent;
        setSelectValue(newSelect, e.target.dataset.value, e.target.textContent);
        options.style.display = 'none';
      }
    });

    document.addEventListener('click', e => {
     if(!e.target.closest('.custom-select'))
      { options.style.display = 'none';
      }
    });

    function ensureOptionVisible(element)
     { const rect = element.getBoundingClientRect();
       const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
       if(rect.bottom > viewportHeight)
        { const scrollAmount = rect.bottom - viewportHeight + 16; // jarak aman
          window.scrollBy({
           top : scrollAmount,
           behavior : 'smooth'
          });
        }
     }

    let activeIndex = -1;

    input.addEventListener('keydown', e => {
     const items = Array.from(options.querySelectorAll('li')).filter(li => li.style.display !== 'none');
     if(!items.length) return;
     if(e.key === 'ArrowDown')
      { e.preventDefault();
        activeIndex = (activeIndex + 1) % items.length;
        setActive(items);
      }
     if(e.key === 'ArrowUp')
      { e.preventDefault();
        activeIndex = (activeIndex - 1 + items.length) % items.length;
        setActive(items);
      }
     if(e.key === 'Enter')
      { e.preventDefault();
        if(activeIndex > -1)
         { items[activeIndex].click();
         }
      }
    });

    function setActive(items)
     { items.forEach(li => li.classList.remove('active'));
       const activeItem = items[activeIndex];
       activeItem.classList.add('active');
       // scroll otomatis ke item aktif
       activeItem.scrollIntoView({
         block: 'nearest'
       });
     }

    function setSelectValue(select, value, text = value)
     { let option = select.querySelector(`option[value="${value}"]`);
       if(!option)
        { option = new Option(text, value);
          select.add(option);
        }
       select.value = value;
     }


  }

</script>
