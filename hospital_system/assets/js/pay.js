(function () {
    const form   = document.getElementById('pay-form');
    const method = document.getElementById('payment_method');
    const groups = {
        card: document.getElementById('card-fields'),
        bank_transfer: document.getElementById('bank-fields')
    };
    const btn = form.querySelector('button[type=submit]');
    const label = btn.textContent;

    // Show only the fields for the chosen method (disabled inputs are not sent)
    function toggle() {
        Object.keys(groups).forEach(function (key) {
            const on = key === method.value;
            groups[key].style.display = on ? 'block' : 'none';
            groups[key].querySelectorAll('input').forEach(function (i) { i.disabled = !on; });
        });
    }
    method.addEventListener('change', toggle);
    toggle();

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        btn.disabled = true;
        btn.textContent = 'Processing...';

        try {
            const res  = await fetch(window.APP.baseUrl + 'api/process_payment.php', {
                method: 'POST',
                body: new FormData(form)
            });
            const data = await res.json();

            let text = data.message;
            if (data.transaction_ref) text += ' (Ref: ' + data.transaction_ref + ')';
            showMessage(text, data.success);

            if (data.success) {
                setTimeout(function () {
                    location.href = window.APP.baseUrl + 'patient/receipt.php?payment_id=' + data.payment_id;
                }, 1200);
            } else {
                btn.disabled = false;
                btn.textContent = label;
            }
        } catch (err) {
            showMessage('Network error. Please try again.', false);
            btn.disabled = false;
            btn.textContent = label;
        }
    });
})();
