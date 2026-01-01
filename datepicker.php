<style>
 .datepicker
  { position : relative;
    width : 220px;
    font-family : Arial, sans-serif;
  }
 .datepicker .calendar
  { position : absolute;
    top : 110%;
    left : 0;
    width : max-content;
    background : #fff;
    border : 1px solid #ddd;
    box-shadow : 0 4px 8px rgba(0,0,0,.1);
    display : none;
    z-index : 10;
  }
 .datepicker .calendar-header
  { display : flex;
    align-items : center;
    justify-content : space-between;
    gap : 6px;
    padding : 6px;
    background : #f5f5f5;
  }
 .datepicker .calendar-header select
  { flex : 1;
    padding : 4px;
    background : white;
    border-radius : 3px;
    border : 1px silver solid;
  }
 .datepicker .year-nav
  { display : flex;
    align-items : center;
    gap : 6px;
  }
 .datepicker .year-nav button
  { padding : 2px 6px;
    cursor : pointer;
    border : 1px solid #ccc;
    background : #fff;
  }
 .datepicker .year-nav button:hover
  { background : #eee;
  }
 .datepicker .yearLabel
  { min-width : 45px;
    text-align : center;
    font-weight : bold;
  }
 .datepicker .calendar-days, .calendar-dates
  { display : grid;
    grid-template-columns : repeat(7, 1fr);
    text-align : center;
  }
 .datepicker .calendar-days
  { gap : 5px;
  }
 .datepicker .calendar-days div
  { font-weight : bold;
    padding : 5px 0;
    background : #fafafa;
  }
 .datepicker .calendar-dates div
  { padding : 9.5px 0px;
    cursor : pointer;
    border-radius : 50%;
  }
 .datepicker .calendar-dates div:hover
  { background : #eee;
  }
 .datepicker .calendar-dates .selected, .calendar-dates .selected:hover
  { background : #007bff;
    color : #fff;
  }
 .datepicker .calendar-dates .today
  { border : 1px solid #007bff;
    box-sizing : border-box;
    font-weight : bold;
  }
 .datepicker .box-input-calender
  { position : relative;
  }
 .datepicker .clear-calender
  { position : absolute;
    top : 50%;
    transform : translateY(-50%);
    right : 18px;
    cursor : pointer;
    user-select : none;
    color : #5e5959;
    font-weight : bold;
  }
</style>

<input type="text" id="dateInput" placeholder="Pilih tanggal" readonly>

<script>

 setDatePicker('dateInput');

 function setDatePicker(inputId)
  { const input = document.getElementById(inputId);
    if (!input) return;
    // buat wrapper
    const wrapper = document.createElement('div');
    wrapper.className = 'datepicker';
    //clone input
    const newInput = input.cloneNode(true);
    newInput.setAttribute('readonly', true);
    newInput.style['padding-right'] = '20px';
    //box input
    const box_input = document.createElement('div');
    box_input.className = 'box-input-calender';
    box_input.appendChild(newInput);
    //clear input
    const clear_input = document.createElement('div');
    clear_input.className = 'clear-calender';
    clear_input.innerHTML = '&times;';
    box_input.appendChild(clear_input);
    //calendar element
    const calendar = document.createElement('div');
    calendar.className = 'calendar';
    calendar.innerHTML = `
     <div class='calendar-header'>
      <select class='monthSelect'></select>
      <div class='year-nav'>
       <button type='button' class='prevYear'>◀</button>
       <span class='yearLabel'></span>
       <button type='button' class='nextYear'>▶</button>
      </div>
     </div>
     <div class='calendar-days'>
      <div>Min</div><div>Sen</div><div>Sel</div><div>Rab</div><div>Kam</div><div>Jum</div><div>Sab</div>
     </div>
     <div class='calendar-dates'></div>
    `;
    // replace input
    input.parentNode.replaceChild(wrapper, input);
    wrapper.appendChild(box_input);
    wrapper.appendChild(calendar);
    terapkanDatePicker(inputId);
  }


function terapkanDatePicker(id)
 { const input = document.getElementById(id);
   const calendar = input.parentNode.parentNode.querySelector('div.calendar') //document.getElementById('calendar');
   const datesContainer = input.parentNode.parentNode.querySelector('div.calendar-dates');
   const monthSelect = input.parentNode.parentNode.querySelector('.monthSelect');
   const yearLabel = input.parentNode.parentNode.querySelector('.yearLabel');
   const prevYear = input.parentNode.parentNode.querySelector('.prevYear');
   const nextYear = input.parentNode.parentNode.querySelector('.nextYear');
   const clearInput = input.parentNode.querySelector('.clear-calender');
   let currentDate = new Date();
   let selectedDate = null;

   //SET CLEAR INPUT
   if(input.value == '')
    { clearInput.style.display = 'none';
    }
   else
    { clearInput.style.display = 'block';
    }
   clearInput.onclick = () => {
    input.value = '';
    clearInput.style.display = 'none';
   }

   //BUKA DATEPICKER
   input.onclick = () => {
    calendar.style.display = 'block';
    const parsed = parseInputDate(input.value);
    if(parsed)
     { selectedDate = parsed;
       currentDate = new Date(parsed);
     }
    initHeader();
    renderCalendar();
    ensureCalendarVisible(calendar);
   };

   //KLIK DI LUAR
   document.addEventListener('click', e => {
    if(!e.target.closest('.datepicker'))
     { calendar.style.display = 'none';
     }
   });

   //INIT HEADER
   function initHeader()
    { const months = ['Januari','Februari','Maret','April','Mei','Juni', 'Juli','Agustus','September','Oktober','November','Desember'];
      monthSelect.innerHTML = '';
      months.forEach((m, i) => {
       monthSelect.add(new Option(m, i));
      });
      monthSelect.value = currentDate.getMonth();
      yearLabel.textContent = currentDate.getFullYear();
    }

   //EVENT HEADER
   monthSelect.onchange = () => {
    currentDate.setMonth(parseInt(monthSelect.value));
    renderCalendar();
   };
   prevYear.onclick = () => {
    currentDate.setFullYear(currentDate.getFullYear() - 1);
    yearLabel.textContent = currentDate.getFullYear();
    renderCalendar();
   };
   nextYear.onclick = () => {
    currentDate.setFullYear(currentDate.getFullYear() + 1);
    yearLabel.textContent = currentDate.getFullYear();
    renderCalendar();
   };

   //RENDER KALENDER
   function renderCalendar()
    { datesContainer.innerHTML = '';
      const year = currentDate.getFullYear();
      const month = currentDate.getMonth();
      const firstDay = new Date(year, month, 1).getDay();
      const daysInMonth = new Date(year, month + 1, 0).getDate();
      for(let i = 0; i < firstDay; i++)
       { datesContainer.appendChild(document.createElement('div'));
       }
      for(let d = 1; d <= daysInMonth; d++)
       { const day = document.createElement('div');
         day.textContent = d;
         //TANDAI HARI INI
         const today = new Date();
         if(d === today.getDate() && month === today.getMonth() && year === today.getFullYear())
          { day.classList.add('today');
          }
         //TANDAI TANGGAL TERPILIH
         if(selectedDate && selectedDate.getDate() === d && selectedDate.getMonth() === month && selectedDate.getFullYear() === year)
          { day.classList.add('selected');
          }
         day.onclick = () => {
          selectedDate = new Date(year, month, d);
          currentDate = new Date(selectedDate);
          input.value = formatDate(selectedDate);
          calendar.style.display = 'none';
          clearInput.style.display = 'block';
         };
         datesContainer.appendChild(day);
       }
    }

   //FORMAT & PARSE TANGGAL
   function formatDate(date)
    { const d = String(date.getDate()).padStart(2, '0');
      const m = String(date.getMonth() + 1).padStart(2, '0');
      const y = date.getFullYear();
      return `${d}-${m}-${y}`;
    }

   function parseInputDate(value)
    { if(!value) return null;
      const parts = value.split('-');
      if(parts.length !== 3) return null;
      const day = parseInt(parts[0], 10);
      const month = parseInt(parts[1], 10) - 1;
      const year = parseInt(parts[2], 10);
      const date = new Date(year, month, day);
      if(date.getDate() !== day || date.getMonth() !== month || date.getFullYear() !== year) return null;
      return date;
    }

   function ensureCalendarVisible(calendarEl)
    { const rect = calendarEl.getBoundingClientRect();
      const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
      if(rect.bottom > viewportHeight)
       { const scrollAmount = rect.bottom - viewportHeight + 16; // jarak aman
         window.scrollBy({
          top : scrollAmount,
          behavior : 'smooth'
         });
       }
    }
  }
</script>