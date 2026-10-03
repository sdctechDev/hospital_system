// Show a success or error message inside <div id="js-message"></div>
function showMessage(text, ok) {
    const box = document.getElementById('js-message');
    if (!box) return;
    box.innerHTML = '';
    const div = document.createElement('div');
    div.className = 'alert ' + (ok ? 'alert-success' : 'alert-error');
    div.textContent = text;
    box.appendChild(div);
    box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
