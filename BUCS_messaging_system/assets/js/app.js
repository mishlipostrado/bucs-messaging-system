// assets/js/app.js - BUCS Messaging System

// ── Modal ────────────────────────────────────────────────────────
function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
    // Focus first input
    const first = modal.querySelector('input:not([type=hidden]), select, textarea');
    if (first) setTimeout(() => first.focus(), 80);
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('open');
    document.body.style.overflow = '';
    // Reset form inside
    const form = modal.querySelector('form');
    if (form) form.reset();
}

// Close modal on overlay click
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('open');
        document.body.style.overflow = '';
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.open').forEach(function (m) {
            m.classList.remove('open');
            document.body.style.overflow = '';
        });
    }
});

// ── Alert auto-dismiss ───────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        // Slide in
        alert.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-8px)';
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                alert.style.opacity = '1';
                alert.style.transform = 'translateY(0)';
            });
        });
        // Auto-dismiss after 3.5s
        setTimeout(function () {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-8px)';
            setTimeout(function () {
                if (alert.parentNode) alert.parentNode.removeChild(alert);
            }, 400);
        }, 3500);
    });
});

// ── Table row highlight on click ─────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.data-table tbody tr').forEach(function (row) {
        row.addEventListener('click', function (e) {
            // Don't trigger if clicking a button
            if (e.target.closest('button')) return;
            document.querySelectorAll('.data-table tbody tr.selected')
                .forEach(function (r) { r.classList.remove('selected'); });
            row.classList.add('selected');
        });
    });
});

// ── Search / filter table ────────────────────────────────────────
function filterTable(inputId, tableSelector) {
    var input = document.getElementById(inputId);
    if (!input) return;
    input.addEventListener('input', function () {
        var query = this.value.toLowerCase().trim();
        var rows  = document.querySelectorAll(tableSelector + ' tbody tr');
        rows.forEach(function (row) {
            var text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    });
}

// ── Confirm delete helper (returns a promise for async use) ──────
function confirmDeletePromise(label) {
    return new Promise(function (resolve) {
        resolve(confirm('Delete ' + (label || 'this record') + '? This cannot be undone.'));
    });
}

// ── Active nav link ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    var current = window.location.pathname.split('/').pop();
    document.querySelectorAll('.nav-item').forEach(function (link) {
        var href = link.getAttribute('href').split('/').pop();
        if (href === current) {
            link.classList.add('active');
        } else if (current === '' && href === 'index.php') {
            link.classList.add('active');
        }
    });
});

// ── Password show/hide toggle ────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[type=password]').forEach(function (input) {
        var wrapper = document.createElement('div');
        wrapper.style.cssText = 'position:relative;display:flex;align-items:center;';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.innerHTML = '<i class="fa fa-eye"></i>';
        btn.title = 'Show/hide password';
        btn.style.cssText = [
            'position:absolute', 'right:10px', 'background:none',
            'border:none', 'cursor:pointer', 'color:var(--text-muted)',
            'font-size:13px', 'padding:0', 'line-height:1'
        ].join(';');
        btn.addEventListener('click', function () {
            var isText = input.type === 'text';
            input.type = isText ? 'password' : 'text';
            btn.innerHTML = isText
                ? '<i class="fa fa-eye"></i>'
                : '<i class="fa fa-eye-slash"></i>';
        });
        wrapper.appendChild(btn);
        // Make room for the icon
        input.style.paddingRight = '34px';
    });
});

// ── Form validation feedback ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var valid = true;
            form.querySelectorAll('[required]').forEach(function (field) {
                field.style.borderColor = '';
                if (!field.value.trim()) {
                    field.style.borderColor = 'var(--error)';
                    field.focus();
                    valid = false;
                }
            });
            if (!valid) e.preventDefault();
        });
        // Clear error border on input
        form.querySelectorAll('[required]').forEach(function (field) {
            field.addEventListener('input', function () {
                this.style.borderColor = '';
            });
        });
    });
});

// ── Sidebar toggle for mobile ────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    // Inject hamburger button if viewport is narrow
    if (window.innerWidth <= 768) {
        var btn = document.createElement('button');
        btn.id = 'sidebar-toggle';
        btn.innerHTML = '<i class="fa fa-bars"></i>';
        btn.style.cssText = [
            'position:fixed', 'top:16px', 'left:16px', 'z-index:300',
            'background:var(--navy)', 'color:#fff', 'border:none',
            'border-radius:8px', 'width:38px', 'height:38px',
            'font-size:16px', 'cursor:pointer', 'display:grid',
            'place-items:center', 'box-shadow:0 2px 8px rgba(0,0,0,.3)'
        ].join(';');
        document.body.appendChild(btn);

        var sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.style.transform = 'translateX(-100%)';
            sidebar.style.transition = 'transform .25s ease';
            btn.addEventListener('click', function () {
                var open = sidebar.style.transform === 'translateX(0px)';
                sidebar.style.transform = open ? 'translateX(-100%)' : 'translateX(0px)';
            });
            // Close on nav click (mobile)
            sidebar.querySelectorAll('.nav-item').forEach(function (link) {
                link.addEventListener('click', function () {
                    sidebar.style.transform = 'translateX(-100%)';
                });
            });
        }
    }
});