// Razorpay Standard Checkout for the course enrolment form.
// Flow: create order on our server → open Razorpay → verify signature on our server → thank-you page.
(function () {
  var form = document.getElementById('enrol-form');
  if (!form) return;
  var msg = form.querySelector('.msg'), button = form.querySelector('button[type=submit]');
  var label = button.textContent;

  function show(text, kind) { msg.className = 'msg' + (kind ? ' ' + kind : ''); msg.textContent = text; }
  function busy(on, text) { button.disabled = on; button.textContent = on ? (text || 'Please wait…') : label; }

  function post(url, data) {
    return fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) })
      .then(function (r) {
        return r.json().catch(function () { return {}; }).then(function (d) {
          if (!r.ok) throw new Error(d.error || 'Something went wrong. Please try again.');
          return d;
        });
      });
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (!form.reportValidity()) return;
    if (typeof window.Razorpay !== 'function') {
      show('The payment window could not load. Please check your internet connection and refresh the page.', 'error');
      return;
    }
    var data = Object.fromEntries(new FormData(form));
    show(''); busy(true, 'Starting payment…');

    post('/api/create-order', data).then(function (order) {
      var rzp = new window.Razorpay({
        key: order.key_id,
        order_id: order.order_id,
        amount: order.amount,
        currency: order.currency,
        name: 'WidePrep',
        description: order.course,
        image: location.origin + '/android-chrome-192x192.png',
        prefill: order.prefill,
        theme: { color: '#0B4F6C' },
        handler: function (resp) {
          busy(true, 'Confirming payment…');
          show('Payment received — confirming, please don’t close this page…');
          post('/api/verify-payment', {
            razorpay_order_id: resp.razorpay_order_id,
            razorpay_payment_id: resp.razorpay_payment_id,
            razorpay_signature: resp.razorpay_signature
          }).then(function (v) {
            window.location.href = v.redirect;
          }).catch(function (err) {
            busy(false);
            show(err.message + ' (Payment ID: ' + resp.razorpay_payment_id + ')', 'error');
          });
        },
        modal: {
          ondismiss: function () { busy(false); show('Payment cancelled. You can try again whenever you’re ready.', 'error'); }
        }
      });
      rzp.on('payment.failed', function (resp) {
        var d = (resp && resp.error && resp.error.description) || 'Payment failed.';
        show(d + ' Please try again or use another payment method. If money was deducted for a failed attempt, it is normally returned to your account automatically.', 'error');
      });
      rzp.open();
    }).catch(function (err) {
      busy(false); show(err.message, 'error');
    });
  });
})();
