// Mobile menu + enquiry form.
(function () {
  var btn = document.querySelector('.menu-btn'), menu = document.getElementById('menu');
  if (btn && menu) {
    btn.addEventListener('click', function () {
      var open = menu.classList.toggle('open');
      btn.setAttribute('aria-expanded', open);
    });
    menu.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () { menu.classList.remove('open'); btn.setAttribute('aria-expanded', false); });
    });
  }

  var form = document.getElementById('enquiry-form');
  if (!form) return;
  var msg = form.querySelector('.msg'), submit = form.querySelector('button[type=submit]');
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    msg.className = 'msg'; msg.textContent = 'Sending…'; submit.disabled = true;
    fetch('/api/enquiry', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(Object.fromEntries(new FormData(form)))
    }).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
      .then(function (res) {
        if (!res.ok) throw new Error(res.d.error || 'Something went wrong.');
        form.reset(); msg.className = 'msg ok';
        msg.textContent = 'Thank you! We’ve received your message and will reply within one working day.';
      })
      .catch(function (err) { msg.className = 'msg error'; msg.textContent = err.message; })
      .finally(function () { submit.disabled = false; });
  });
})();
