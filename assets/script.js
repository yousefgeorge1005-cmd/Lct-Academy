document.addEventListener('DOMContentLoaded', function () {
  if (window.lucide) lucide.createIcons();

  /* ---------- theme toggle (instant, no reload) ---------- */
  document.querySelectorAll('[data-action="toggle-theme"]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var html = document.documentElement;
      var next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      html.setAttribute('data-theme', next);
      document.cookie = 'lct_theme=' + next + ';path=/;max-age=' + (60 * 60 * 24 * 365);
      var icon = btn.querySelector('[data-lucide]');
      if (icon) { icon.setAttribute('data-lucide', next === 'dark' ? 'sun' : 'moon'); lucide.createIcons(); }
    });
  });

  /* ---------- language toggle (needs a reload: content is server-rendered) ---------- */
  document.querySelectorAll('[data-action="toggle-lang"]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var current = document.documentElement.getAttribute('lang') === 'ar' ? 'ar' : 'en';
      var next = current === 'ar' ? 'en' : 'ar';
      document.cookie = 'lct_lang=' + next + ';path=/;max-age=' + (60 * 60 * 24 * 365);
      window.location.reload();
    });
  });

  /* ---------- mobile menu ---------- */
  var menuBtn = document.querySelector('[data-action="toggle-mobile-menu"]');
  var drawer = document.querySelector('.mobile-drawer');
  if (menuBtn && drawer) {
    menuBtn.addEventListener('click', function () { drawer.classList.toggle('open'); });
  }

  /* ---------- delete confirmations ---------- */
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!window.confirm(form.getAttribute('data-confirm'))) e.preventDefault();
    });
  });

  /* ---------- 3D tilt: hero emblem follows the cursor ---------- */
  var heroEmblem = document.querySelector('.hero-emblem');
  var heroSection = document.querySelector('.hero');
  if (heroEmblem && heroSection) {
    heroSection.addEventListener('mousemove', function (e) {
      var rect = heroEmblem.getBoundingClientRect();
      var cx = rect.left + rect.width / 2, cy = rect.top + rect.height / 2;
      var dx = (e.clientX - cx) / (rect.width / 2), dy = (e.clientY - cy) / (rect.height / 2);
      var rotY = Math.max(-1, Math.min(1, dx)) * 16, rotX = Math.max(-1, Math.min(1, dy)) * -16;
      heroEmblem.style.transform = 'rotateX(' + rotX + 'deg) rotateY(' + rotY + 'deg)';
    });
    heroSection.addEventListener('mouseleave', function () { heroEmblem.style.transform = 'rotateX(0) rotateY(0)'; });
  }

  /* ---------- 3D tilt: course cards on hover ---------- */
  document.querySelectorAll('.course-card').forEach(function (card) {
    card.addEventListener('mousemove', function (e) {
      var rect = card.getBoundingClientRect();
      var dx = (e.clientX - rect.left - rect.width / 2) / (rect.width / 2);
      var dy = (e.clientY - rect.top - rect.height / 2) / (rect.height / 2);
      card.style.transform = 'perspective(800px) rotateX(' + (-dy * 6) + 'deg) rotateY(' + (dx * 6) + 'deg) translateY(-4px)';
    });
    card.addEventListener('mouseleave', function () { card.style.transform = ''; });
  });

  /* ---------- booking form: step wizard + image preview + client validation ---------- */
  var wizard = document.querySelector('[data-wizard]');
  if (wizard) {
    var steps = Array.prototype.slice.call(wizard.querySelectorAll('[data-step]'));
    var stepItems = Array.prototype.slice.call(wizard.querySelectorAll('.step-item'));
    var stepLines = Array.prototype.slice.call(wizard.querySelectorAll('.step-line'));
    var current = 1;

    function showStep(n) {
      current = n;
      steps.forEach(function (s) { s.style.display = (parseInt(s.getAttribute('data-step'), 10) === n) ? '' : 'none'; });
      stepItems.forEach(function (item, i) {
        item.classList.remove('active', 'done');
        if (i + 1 < n) item.classList.add('done');
        else if (i + 1 === n) item.classList.add('active');
      });
      stepLines.forEach(function (line, i) { line.classList.toggle('done', i + 1 < n); });
      wizard.querySelectorAll('[data-nav-back]').forEach(function (b) { b.style.visibility = n === 1 ? 'hidden' : 'visible'; });
      wizard.querySelectorAll('[data-nav-next]').forEach(function (b) { b.style.display = n === steps.length ? 'none' : ''; });
      wizard.querySelectorAll('[data-nav-submit]').forEach(function (b) { b.style.display = n === steps.length ? '' : 'none'; });
      window.scrollTo({ top: wizard.offsetTop - 20, behavior: 'smooth' });
      syncReview();
    }

    function fieldError(input, msg) {
      clearFieldError(input);
      input.classList.add('err');
      var e = document.createElement('div');
      e.className = 'field-err generated-err';
      e.innerHTML = '<span data-lucide="alert-triangle" style="width:13px;height:13px"></span>' + msg;
      input.insertAdjacentElement('afterend', e);
      if (window.lucide) lucide.createIcons();
    }
    function clearFieldError(input) {
      input.classList.remove('err');
      var next = input.nextElementSibling;
      if (next && next.classList.contains('generated-err')) next.remove();
    }

    function validateStep1() {
      var ok = true;
      var name = wizard.querySelector('[name="full_name"]');
      var phone = wizard.querySelector('[name="phone"]');
      var email = wizard.querySelector('[name="email"]');
      var nid = wizard.querySelector('[name="national_id"]');
      [name, phone, email, nid].forEach(clearFieldError);
      if (!name.value.trim()) { fieldError(name, wizard.dataset.errRequired); ok = false; }
      if (!phone.value.trim()) { fieldError(phone, wizard.dataset.errRequired); ok = false; }
      else if (!/^01[0125][0-9]{8}$/.test(phone.value.trim())) { fieldError(phone, wizard.dataset.errPhone); ok = false; }
      if (email.value.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) { fieldError(email, wizard.dataset.errEmail); ok = false; }
      if (!nid.value.trim()) { fieldError(nid, wizard.dataset.errRequired); ok = false; }
      else if (!/^\d{14}$/.test(nid.value.trim())) { fieldError(nid, wizard.dataset.errNid); ok = false; }

      // Hall booking: date + start/end time (only present on book-hall.php)
      var bdate = wizard.querySelector('[name="booking_date"]');
      var bstart = wizard.querySelector('[name="start_time"]');
      var bend = wizard.querySelector('[name="end_time"]');
      if (bdate && bstart && bend) {
        [bdate, bstart, bend].forEach(clearFieldError);
        if (!bdate.value) { fieldError(bdate, wizard.dataset.errRequired); ok = false; }
        if (!bstart.value) { fieldError(bstart, wizard.dataset.errRequired); ok = false; }
        if (!bend.value) { fieldError(bend, wizard.dataset.errRequired); ok = false; }
        if (bstart.value && bend.value && bend.value <= bstart.value) { fieldError(bend, wizard.dataset.errTime); ok = false; }
      }

      // Course booking: branch select (only present on book.php)
      var branch = wizard.querySelector('select[name="branch_id"]');
      if (branch) {
        clearFieldError(branch);
        if (!branch.value) { fieldError(branch, wizard.dataset.errRequired); ok = false; }
      }
      return ok;
    }
    function validateStep2() {
      var payInput = wizard.querySelector('[name="payment_photo"]');
      var dzPay    = wizard.querySelector('[data-dz-payment]') || wizard.querySelector('.dropzone-payment');
      var idInput  = wizard.querySelector('[name="id_photo"]');
      var dzId     = wizard.querySelector('[data-dz-id]') || wizard.querySelector('.dropzone-id') || wizard.querySelector('.dropzone');

      var ok = true;
      if (payInput && (!payInput.files || payInput.files.length === 0)) {
        if (dzPay) dzPay.classList.add('err');
        ok = false;
      } else if (dzPay) {
        dzPay.classList.remove('err');
      }

      if (idInput && (!idInput.files || idInput.files.length === 0)) {
        if (dzId) dzId.classList.add('err');
        ok = false;
      } else if (dzId) {
        dzId.classList.remove('err');
      }
      return ok;
    }

    wizard.querySelectorAll('[data-nav-next]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (current === 1 && !validateStep1()) return;
        if (current === 2 && !validateStep2()) return;
        showStep(Math.min(steps.length, current + 1));
      });
    });
    wizard.querySelectorAll('[data-nav-back]').forEach(function (btn) {
      btn.addEventListener('click', function () { showStep(Math.max(1, current - 1)); });
    });

    var submitBtn = wizard.querySelector('[data-nav-submit]');
    if (submitBtn) {
      submitBtn.addEventListener('click', function () {
        var agree = wizard.querySelector('[name="agree"]');
        if (agree && !agree.checked) {
          window.alert(wizard.dataset.errAgree || 'برجاء الموافقة على الإقرار أولاً');
          return;
        }
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spin-dot"></span> ' + (wizard.dataset.submitting || 'جاري الإرسال...');
        wizard.closest('form').submit();
      });
    }

    // Payment receipt photo preview & handlers
    var payInput = wizard.querySelector('[name="payment_photo"]');
    var dzPay    = wizard.querySelector('[data-dz-payment]') || wizard.querySelector('.dropzone-payment');
    var payPrev  = wizard.querySelector('[data-payment-preview]');
    if (payInput) {
      payInput.addEventListener('change', function () {
        var file = payInput.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function () {
          if (payPrev) {
            payPrev.style.display = '';
            var img = payPrev.querySelector('img');
            if (img) img.src = reader.result;
          }
          if (dzPay) dzPay.style.display = 'none';
          var reviewImg = wizard.querySelector('[data-review-payment-photo]');
          if (reviewImg) { reviewImg.src = reader.result; reviewImg.style.display = ''; }
        };
        reader.readAsDataURL(file);
      });
      if (dzPay) {
        dzPay.addEventListener('click', function () { payInput.click(); });
        dzPay.addEventListener('dragover', function (e) { e.preventDefault(); dzPay.classList.add('drag'); });
        dzPay.addEventListener('dragleave', function () { dzPay.classList.remove('drag'); });
        dzPay.addEventListener('drop', function (e) {
          e.preventDefault(); dzPay.classList.remove('drag');
          if (e.dataTransfer.files[0]) { payInput.files = e.dataTransfer.files; payInput.dispatchEvent(new Event('change')); }
        });
      }
      var changePayBtn = wizard.querySelector('[data-change-payment-photo]');
      if (changePayBtn) changePayBtn.addEventListener('click', function () { payInput.click(); });
    }

    // ID photo preview & handlers
    var fileInput = wizard.querySelector('[name="id_photo"]');
    var dropzone  = wizard.querySelector('[data-dz-id]') || wizard.querySelector('.dropzone-id') || wizard.querySelector('.dropzone');
    var previewWrap = wizard.querySelector('[data-id-preview]');
    if (fileInput) {
      fileInput.addEventListener('change', function () {
        var file = fileInput.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function () {
          if (previewWrap) {
            previewWrap.style.display = '';
            var img = previewWrap.querySelector('img');
            if (img) img.src = reader.result;
          }
          if (dropzone) dropzone.style.display = 'none';
          var reviewImg = wizard.querySelector('[data-review-id-photo]');
          if (reviewImg) { reviewImg.src = reader.result; reviewImg.style.display = ''; }
        };
        reader.readAsDataURL(file);
      });
      if (dropzone) {
        dropzone.addEventListener('click', function () { fileInput.click(); });
        dropzone.addEventListener('dragover', function (e) { e.preventDefault(); dropzone.classList.add('drag'); });
        dropzone.addEventListener('dragleave', function () { dropzone.classList.remove('drag'); });
        dropzone.addEventListener('drop', function (e) {
          e.preventDefault(); dropzone.classList.remove('drag');
          if (e.dataTransfer.files[0]) { fileInput.files = e.dataTransfer.files; fileInput.dispatchEvent(new Event('change')); }
        });
      }
      var changeBtn = wizard.querySelector('[data-change-photo]');
      if (changeBtn) changeBtn.addEventListener('click', function () { fileInput.click(); });
    }

    function syncReview() {
      wizard.querySelectorAll('[data-review-field]').forEach(function (el) {
        var name = el.getAttribute('data-review-field');
        var input = wizard.querySelector('[name="' + name + '"]');
        if (input) el.textContent = input.value || '';
      });
      var fullNameInput = wizard.querySelector('[name="full_name"]');
      var pledgeNameEl  = wizard.querySelector('[data-pledge-name]');
      if (pledgeNameEl) {
        var val = fullNameInput ? fullNameInput.value.trim() : '';
        pledgeNameEl.textContent = val || '______';
      }
    }

    var fullNameInput = wizard.querySelector('[name="full_name"]');
    if (fullNameInput) {
      fullNameInput.addEventListener('input', function() {
        var pledgeNameEl = wizard.querySelector('[data-pledge-name]');
        if (pledgeNameEl) {
          pledgeNameEl.textContent = fullNameInput.value.trim() || '______';
        }
      });
    }

    showStep(1);
  }
});
