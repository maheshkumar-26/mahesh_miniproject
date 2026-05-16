/**
 * Employee Compensation Insights - Main JS
 */
document.addEventListener('DOMContentLoaded', function () {

  // ── Sidebar Toggle ──
  const sidebarToggle = document.getElementById('sidebarToggle');
  const body = document.body;

  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function () {
      body.classList.toggle('sidebar-open');
    });
  }

  // Overlay click closes sidebar on mobile
  let overlay = document.querySelector('.sidebar-overlay');
  if (!overlay) {
    overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);
  }
  overlay.addEventListener('click', function () {
    body.classList.remove('sidebar-open');
  });

  // ── Auto-hide Flash Messages ──
  const flashAlerts = document.querySelectorAll('.alert-dismissible.auto-hide');
  flashAlerts.forEach(function (alert) {
    setTimeout(function () {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
      if (bsAlert) bsAlert.close();
    }, 4000);
  });

  // ── Confirm Delete ──
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      const msg = el.getAttribute('data-confirm') || 'Are you sure you want to delete this?';
      if (!confirm(msg)) {
        e.preventDefault();
        return false;
      }
    });
  });

  // ── Bootstrap Tooltips ──
  const tooltipEls = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  tooltipEls.forEach(function (el) {
    new bootstrap.Tooltip(el);
  });

  // ── Salary Breakdown Animated Bars ──
  const bars = document.querySelectorAll('.salary-bar-fill[data-width]');
  if (bars.length) {
    setTimeout(function () {
      bars.forEach(function (bar) {
        bar.style.width = bar.getAttribute('data-width') + '%';
      });
    }, 200);
  }

  // ── Star Rating ──
  document.querySelectorAll('.star-rating').forEach(function (ratingEl) {
    const stars = ratingEl.querySelectorAll('.star');
    const input = ratingEl.querySelector('input[type="hidden"]');
    let currentVal = parseInt(input ? input.value : 0) || 0;

    function setStars(val) {
      stars.forEach(function (s, i) {
        s.classList.toggle('active', i < val);
      });
    }

    setStars(currentVal);

    stars.forEach(function (star, idx) {
      star.addEventListener('mouseenter', function () { setStars(idx + 1); });
      star.addEventListener('mouseleave', function () { setStars(currentVal); });
      star.addEventListener('click', function () {
        currentVal = idx + 1;
        if (input) input.value = currentVal;
        setStars(currentVal);
      });
    });
  });

  // ── Live Payroll Calculation ──
  const payrollFields = [
    'basic_salary', 'hra', 'allowances', 'bonus', 'incentives', 'overtime_pay',
    'tax_deduction', 'pf_deduction', 'insurance_deduction'
  ];

  function calcPayroll() {
    const get = function (id) {
      const el = document.getElementById(id);
      return el ? parseFloat(el.value) || 0 : 0;
    };
    const gross = get('basic_salary') + get('hra') + get('allowances') +
                  get('bonus') + get('incentives') + get('overtime_pay');
    const deductions = get('tax_deduction') + get('pf_deduction') + get('insurance_deduction');
    const net = gross - deductions;

    const grossEl = document.getElementById('calc_gross');
    const deductEl = document.getElementById('calc_deductions');
    const netEl = document.getElementById('calc_net');

    if (grossEl)  grossEl.textContent  = '₹' + gross.toLocaleString('en-IN', {minimumFractionDigits: 2});
    if (deductEl) deductEl.textContent = '₹' + deductions.toLocaleString('en-IN', {minimumFractionDigits: 2});
    if (netEl)    netEl.textContent    = '₹' + net.toLocaleString('en-IN', {minimumFractionDigits: 2});
  }

  payrollFields.forEach(function (id) {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', calcPayroll);
  });
  calcPayroll();

  // ── Print Payslip ──
  window.printPayslip = function () {
    window.print();
  };

  // ── Smooth Scroll ──
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth' });
      }
    });
  });

  // ── Form Validation ──
  const forms = document.querySelectorAll('.needs-validation');
  forms.forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
      }
      form.classList.add('was-validated');
    });
  });

});

// ── Chart Helpers ──
window.initBarChart = function (canvasId, labels, datasets, options) {
  const ctx = document.getElementById(canvasId);
  if (!ctx) return null;
  return new Chart(ctx, {
    type: 'bar',
    data: { labels: labels, datasets: datasets },
    options: Object.assign({
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'top', labels: { font: { size: 12 }, usePointStyle: true } },
        tooltip: { mode: 'index', intersect: false }
      },
      scales: {
        x: { grid: { display: false }, ticks: { font: { size: 12 } } },
        y: {
          grid: { color: '#f1f5f9' },
          ticks: {
            font: { size: 12 },
            callback: function (v) { return '₹' + (v / 1000).toFixed(0) + 'k'; }
          }
        }
      }
    }, options || {})
  });
};

window.initLineChart = function (canvasId, labels, datasets, options) {
  const ctx = document.getElementById(canvasId);
  if (!ctx) return null;
  return new Chart(ctx, {
    type: 'line',
    data: { labels: labels, datasets: datasets },
    options: Object.assign({
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'top', labels: { font: { size: 12 }, usePointStyle: true } },
        tooltip: { mode: 'index', intersect: false }
      },
      scales: {
        x: { grid: { display: false }, ticks: { font: { size: 12 } } },
        y: {
          grid: { color: '#f1f5f9' },
          ticks: {
            font: { size: 12 },
            callback: function (v) { return '₹' + (v / 1000).toFixed(0) + 'k'; }
          }
        }
      },
      elements: { line: { tension: 0.4 }, point: { radius: 4, hoverRadius: 6 } }
    }, options || {})
  });
};

window.initDoughnutChart = function (canvasId, labels, data, colors, options) {
  const ctx = document.getElementById(canvasId);
  if (!ctx) return null;
  return new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: labels,
      datasets: [{ data: data, backgroundColor: colors, borderWidth: 2, borderColor: '#fff' }]
    },
    options: Object.assign({
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'bottom', labels: { font: { size: 12 }, usePointStyle: true, padding: 16 } },
        tooltip: {
          callbacks: {
            label: function (ctx) {
              return ' ' + ctx.label + ': ₹' + ctx.parsed.toLocaleString('en-IN');
            }
          }
        }
      },
      cutout: '65%'
    }, options || {})
  });
};
