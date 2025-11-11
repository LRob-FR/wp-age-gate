(function(){
  function setCookie(name, value, days) {
    var expires = "";
    if (days) {
      var date = new Date();
      date.setTime(date.getTime() + (days*24*60*60*1000));
      expires = "; expires=" + date.toUTCString();
    }
    var secure = (location.protocol === 'https:') ? '; Secure' : '';
    document.cookie = name + "=" + (value || "")  + expires + "; path=/; SameSite=Lax" + secure;
  }

  function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
      var c = ca[i];
      while (c.charAt(0)==' ') c = c.substring(1,c.length);
      if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
  }

  function showGate() {
    var gate = document.getElementById('lrob-age-gate');
    if (!gate) return;
    var opts = window.LROB_AGEGATE_OPTS || {};

    var title = gate.querySelector('#lrob-age-title');
    var desc = gate.querySelector('#lrob-age-desc');
    var legal = gate.querySelector('.lrob-age-gate__legal');
    var btnOk = gate.querySelector('.lrob-age-gate__btn--accept');
    var btnNo = gate.querySelector('.lrob-age-gate__btn--decline');

    title.textContent = opts.title || 'Age Verification';
    desc.innerHTML = opts.message || '';
    legal.innerHTML = opts.legal || '';
    btnOk.textContent = opts.accept || 'I Confirm';
    btnNo.textContent = opts.decline || 'I Decline';

    gate.style.display = 'flex';
    document.documentElement.classList.add('lrob-age-gate-open');
    var dialog = gate.querySelector('.lrob-age-gate__dialog');
    if (dialog) dialog.focus();

    btnOk.addEventListener('click', function(){
      setCookie('lrob_age_verified', '1:' + (opts.token || ''), opts.cookieDays || 30);
      gate.style.display = 'none';
      document.documentElement.classList.remove('lrob-age-gate-open');
    });
    btnNo.addEventListener('click', function(){
      var url = opts.declineUrl || 'about:blank';
      window.location.href = url;
    });

    // focus trap
    gate.addEventListener('keydown', function(e){
      if (e.key === 'Tab') {
        var focusable = gate.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        focusable = Array.prototype.slice.call(focusable);
        var first = focusable[0];
        var last = focusable[focusable.length - 1];
        if (e.shiftKey) {
          if (document.activeElement === first) {
            last.focus();
            e.preventDefault();
          }
        } else {
          if (document.activeElement === last) {
            first.focus();
            e.preventDefault();
          }
        }
      }
      if (e.key === 'Escape') {
        e.preventDefault();
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function(){
    var cookie = getCookie('lrob_age_verified');
    if (cookie) {
      // Old format: just "1"
      if (cookie === '1') return;

      // New format: "1:token"
      var parts = cookie.split(':');
      if (parts.length === 2 && parts[0] === '1') {
        var cookieToken = parts[1];
        var currentToken = window.LROB_AGEGATE_OPTS && window.LROB_AGEGATE_OPTS.token || '';
        if (cookieToken === currentToken) {
          return; // Valid cookie with matching token
        }
      }
    }
    showGate();
  });
})();
