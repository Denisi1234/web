(function ($) {
	"use strict";
	
	// Handle all traveler selectors on page
  document.querySelectorAll('.selection-container').forEach(wrapper => {
    const input = wrapper.querySelector('.traveler-input');
    const dropdown = wrapper.querySelector('.traveler-dropdown');
    const hasRooms = dropdown.dataset.hasRooms === "true";

    input.addEventListener('click', () => {
      document.querySelectorAll('.traveler-dropdown').forEach(d => {
        if (d !== dropdown) d.style.display = 'none';
      });
      dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
      if (dropdown.innerHTML.trim() === '') {
        renderDropdown(dropdown, hasRooms);
      }
    });

    document.addEventListener('click', e => {
	  const isInside = wrapper.contains(e.target);
	  if (!isInside) {
		dropdown.style.display = 'none';
	  }
	});
	
	// Prevent dropdown from closing when clicking inside
	dropdown.addEventListener('click', (e) => {
	  e.stopPropagation();
	});

    function renderDropdown(container, withRooms) {
      let rooms = [
        { adults: 1, children: 0 }
      ];

      function updateUI() {
        container.innerHTML = '';
        rooms.forEach((room, index) => {
          const roomDiv = document.createElement('div');
          roomDiv.className = 'room';

          roomDiv.innerHTML = `
            ${withRooms ? `<strong>Room ${index + 1}</strong>` : ''}
            <div class="clouse">
              <label>Adults</label>
              <div class="counter">
                <button class="dec"><i class="bi bi-dash"></i></button>
                <span class="adult-count">${room.adults}</span>
                <button class="inc"><i class="bi bi-plus"></i></button>
              </div>
            </div>
            <div class="clouse">
              <label>Children</label>
              <div class="counter">
                <button class="dec"><i class="bi bi-dash"></i></button>
                <span class="child-count">${room.children}</span>
                <button class="inc"><i class="bi bi-plus"></i></button>
              </div>
            </div>
          `;

          if (withRooms && rooms.length > 1) {
            const removeBtn = document.createElement('button');
            removeBtn.className = 'btn btn-sm fw-medium btn-light-danger remove-room-btn w-100 mb-3';
            removeBtn.textContent = 'Remove Room';
            removeBtn.onclick = () => {
              rooms.splice(index, 1);
              updateUI();
            };
            roomDiv.appendChild(removeBtn);
          }

          container.appendChild(roomDiv);
        });

        if (withRooms) {
          const addRoom = document.createElement('button');
          addRoom.className = 'btn btn-md btn-light-primary add-room-btn';
          addRoom.textContent = 'Add Room';
          addRoom.onclick = () => {
            rooms.push({ adults: 1, children: 0 });
            updateUI();
          };
          container.appendChild(addRoom);
        }

        const applyBtn = document.createElement('button');
        applyBtn.className = 'btn btn-md btn-primary apply-btn float-end';
        applyBtn.textContent = 'Apply';
        applyBtn.onclick = () => {
          let summary = '';
          if (withRooms) {
            summary = `${rooms.length} Room${rooms.length > 1 ? 's' : ''}, `;
          }
          let totalAdults = rooms.reduce((sum, r) => sum + r.adults, 0);
          let totalChildren = rooms.reduce((sum, r) => sum + r.children, 0);
          summary += `${totalAdults} Adult${totalAdults > 1 ? 's' : ''}`;
          if (totalChildren > 0) {
            summary += `, ${totalChildren} Child${totalChildren > 1 ? 'ren' : ''}`;
          }
          input.value = summary;
          container.style.display = 'none';
        };
        container.appendChild(applyBtn);

        // Event bindings
        container.querySelectorAll('.room').forEach((roomDiv, i) => {
          const adultSpan = roomDiv.querySelector('.adult-count');
          const childSpan = roomDiv.querySelector('.child-count');
          const adultInc = roomDiv.querySelectorAll('.inc')[0];
          const adultDec = roomDiv.querySelectorAll('.dec')[0];
          const childInc = roomDiv.querySelectorAll('.inc')[1];
          const childDec = roomDiv.querySelectorAll('.dec')[1];

          adultInc.onclick = () => {
            rooms[i].adults++;
            updateUI();
          };
          adultDec.onclick = () => {
            rooms[i].adults = Math.max(1, rooms[i].adults - 1);
            updateUI();
          };
          childInc.onclick = () => {
            rooms[i].children++;
            updateUI();
          };
          childDec.onclick = () => {
            rooms[i].children = Math.max(0, rooms[i].children - 1);
            updateUI();
          };
        });
      }

      updateUI();
    }
  });
	
	
})(this.jQuery);