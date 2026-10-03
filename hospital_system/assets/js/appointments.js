document.addEventListener('click', async function (e) {
    const btn = e.target.closest('[data-cancel-id]');
    if (!btn) return;
    if (!confirm('Cancel this appointment?')) return;

    btn.disabled = true;
    const fd = new FormData();
    fd.append('appointment_id', btn.dataset.cancelId);

    try {
        const res  = await fetch(window.APP.baseUrl + 'api/cancel_appointment.php', { method: 'POST', body: fd });
        const data = await res.json();
        showMessage(data.message, data.success);
        if (data.success) {
            setTimeout(function () { location.reload(); }, 800);
        } else {
            btn.disabled = false;
        }
    } catch (err) {
        showMessage('Network error. Please try again.', false);
        btn.disabled = false;
    }
});
