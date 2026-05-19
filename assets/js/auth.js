document.addEventListener('DOMContentLoaded', function () {

  const tabs = document.querySelectorAll('.tab-btn');
  const loginSec = document.getElementById('login-section');
  const regSec = document.getElementById('register-section');

  tabs.forEach(function(btn) {
    btn.addEventListener('click', function () {
      tabs.forEach(function(t) { t.classList.remove('active'); });
      btn.classList.add('active');
      if (btn.dataset.tab === 'login') {
        loginSec.classList.add('active');
        regSec.classList.remove('active');
      } else {
        regSec.classList.add('active');
        loginSec.classList.remove('active');
      }
    });
  });

  const roleCards = document.querySelectorAll('.role-card');
  const roleInput = document.getElementById('reg-role');

  roleCards.forEach(function(card) {
    card.addEventListener('click', function () {
      roleCards.forEach(function(c) {
        c.classList.remove('selected-admin', 'selected-teacher', 'selected-student');
      });
      const val = card.dataset.role;
      roleInput.value = val;
      card.classList.add('selected-' + val);

      const levelGroup = document.getElementById('level-group');
      if (val === 'student') {
        levelGroup.style.display = 'block';
      } else {
        levelGroup.style.display = 'none';
      }
    });
  });

  const levelCards = document.querySelectorAll('.level-card');
  const levelInput = document.getElementById('reg-level');

  levelCards.forEach(function(card) {
    card.addEventListener('click', function () {
      levelCards.forEach(function(c) {
        c.classList.remove('selected-low', 'selected-high');
      });
      const val = card.dataset.level;
      levelInput.value = val;
      card.classList.add('selected-' + val);
    });
  });

  document.querySelectorAll('.toggle-pw').forEach(function(btn) {
    btn.addEventListener('click', function () {
      const input = document.getElementById(btn.dataset.target);
      if (!input) return;
      if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = '🙈';
      } else {
        input.type = 'password';
        btn.textContent = '👁️';
      }
    });
  });

  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(function(a) {
    setTimeout(function() {
      a.style.transition = 'opacity .5s';
      a.style.opacity = '0';
      setTimeout(function() { a.remove(); }, 500);
    }, 4000);
  });

  const activeTab = document.getElementById('active-tab-hint');
  if (activeTab && activeTab.value === 'register') {
    document.querySelector('[data-tab="register"]').click();
  }
});