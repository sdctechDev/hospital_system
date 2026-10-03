(function () {
    const doctorSel = document.getElementById('doctor_id');
    const dateInp   = document.getElementById('date');
    const slotsBox  = document.getElementById('slots');
    const feeBox    = document.getElementById('fee');
    const reasonInp = document.getElementById('reason');
    const bookBtn   = document.getElementById('book-btn');
    let selectedTime = '';

    function updateFee() {
        const opt = doctorSel.options[doctorSel.selectedIndex];
        const fee = opt && opt.dataset.fee ? Number(opt.dataset.fee) : null;
        feeBox.textContent = fee !== null ? 'Consultation fee: \u20A6' + fee.toLocaleString() : '';
    }

    async function loadSlots() {
        selectedTime = '';
        slotsBox.innerHTML = '';
        if (!doctorSel.value || !dateInp.value) return;

        slotsBox.textContent = 'Loading available times...';
        try {
            const url = window.APP.baseUrl + 'api/get_slots.php?doctor_id=' +
                encodeURIComponent(doctorSel.value) + '&date=' + encodeURIComponent(dateInp.value);
            const res  = await fetch(url);
            const data = await res.json();

            slotsBox.innerHTML = '';
            if (!data.success) {
                showMessage(data.message, false);
                return;
            }
            if (data.slots.length === 0) {
                slotsBox.innerHTML = '<span class="muted">' + data.message + '</span>';
                return;
            }
            data.slots.forEach(function (time) {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'slot';
                b.textContent = time;
                b.addEventListener('click', function () {
                    slotsBox.querySelectorAll('.slot').forEach(function (s) { s.classList.remove('selected'); });
                    b.classList.add('selected');
                    selectedTime = time;
                });
                slotsBox.appendChild(b);
            });
        } catch (err) {
            slotsBox.innerHTML = '';
            showMessage('Could not load time slots. Check your connection.', false);
        }
    }

    async function book() {
        if (!doctorSel.value) return showMessage('Please choose a doctor.', false);
        if (!dateInp.value)   return showMessage('Please choose a date.', false);
        if (!selectedTime)    return showMessage('Please pick a time slot.', false);

        const fd = new FormData();
        fd.append('doctor_id', doctorSel.value);
        fd.append('date', dateInp.value);
        fd.append('time', selectedTime);
        fd.append('reason', reasonInp.value);

        bookBtn.disabled = true;
        try {
            const res  = await fetch(window.APP.baseUrl + 'api/book_appointment.php', { method: 'POST', body: fd });
            const data = await res.json();
            showMessage(data.message, data.success);

            if (data.success) {
                setTimeout(function () {
                    location.href = window.APP.baseUrl + 'patient/pay.php?appointment_id=' + data.appointment_id;
                }, 800);
            } else {
                bookBtn.disabled = false;
                loadSlots(); // refresh in case the slot was taken
            }
        } catch (err) {
            showMessage('Network error. Please try again.', false);
            bookBtn.disabled = false;
        }
    }

    doctorSel.addEventListener('change', function () { updateFee(); loadSlots(); });
    dateInp.addEventListener('change', loadSlots);
    bookBtn.addEventListener('click', book);

    updateFee();
    loadSlots();
})();
