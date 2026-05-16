// assets/js/app.js – BUCS Messaging System

// ── Modal helpers ────────────────────────────────────────────
function openModal(id) {
    var m = document.getElementById(id);
    if (!m) return;
    m.classList.add('open');
    document.body.style.overflow = 'hidden';
    var first = m.querySelector('input:not([type=hidden]),select,textarea');
    if (first) setTimeout(function(){ first.focus(); }, 80);
}
function closeModal(id) {
    var m = document.getElementById(id);
    if (!m) return;
    m.classList.remove('open');
    document.body.style.overflow = '';
    var f = m.querySelector('form');
    if (f) f.reset();
}

// Close on backdrop click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('open');
        document.body.style.overflow = '';
    }
});

// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.open').forEach(function(m) {
            m.classList.remove('open');
            document.body.style.overflow = '';
        });
    }
});

// ── User dropdown toggle ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    var navUser = document.querySelector('.nav-user');
    if (navUser) {
        navUser.addEventListener('click', function(e) {
            e.stopPropagation();
            navUser.classList.toggle('open');
        });
        document.addEventListener('click', function() {
            navUser.classList.remove('open');
        });
    }
});

// ── Alert auto-dismiss ────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.alert').forEach(function(el) {
        el.style.transition = 'opacity .4s ease, transform .4s ease';
        setTimeout(function() {
            el.style.opacity = '0';
            el.style.transform = 'translateY(-6px)';
            setTimeout(function() { el.remove(); }, 400);
        }, 4000);
    });
});

// ── Password toggle ───────────────────────────────────────────
function togglePwd(inputId, eyeId) {
    var input = document.getElementById(inputId);
    var eye   = document.getElementById(eyeId);
    if (!input || !eye) return;
    if (input.type === 'password') {
        input.type = 'text';
        eye.className = 'fa fa-eye-slash';
    } else {
        input.type = 'password';
        eye.className = 'fa fa-eye';
    }
}

// ── Required field highlight on submit ───────────────────────
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            var ok = true;
            form.querySelectorAll('[required]').forEach(function(f) {
                f.classList.remove('error');
                if (!f.value.trim()) { f.classList.add('error'); ok = false; }
            });
            if (!ok) e.preventDefault();
        });
        form.querySelectorAll('[required]').forEach(function(f) {
            f.addEventListener('input', function() { f.classList.remove('error'); });
        });
    });
});
