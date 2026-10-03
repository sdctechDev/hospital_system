// Handles buttons like:
// <button data-status-action data-id="5" data-status="completed">Complete</button>
function showMessage(text, ok) {
    const box = document.getElementById('js-message');
    if (!box) return;
    box.innerHTML = '';
    const div = document.createElement('div');
    div.className = 'alert ' + (ok ? 'alert-success' : 'alert-error');
    div.textContent = text;
    box.appendChild(div);
}

document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-status-action]');
    if (!btn) return;

    if (btn.dataset.status === 'cancelled' && !confirm('Cancel this appointment?')) return;

    btn.disabled = true;
    const fd = new FormData();
    fd.append('appointment_id', btn.dataset.id);
    fd.append('status', btn.dataset.status);

    try {
        const res = await fetch(window.APP.baseUrl + 'api/update_appointment_status.php', {
            method: 'POST',
            body: fd
        });
        const data = await res.json();
        showMessage(data.message, data.success);
        if (data.success) {
            setTimeout(() => location.reload(), 800);
        } else {
            btn.disabled = false;
        }
    } catch (err) {
        showMessage('Network error. Please try again.', false);
        btn.disabled = false;
    }
});
