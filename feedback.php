<?php
require_once __DIR__ . '/../config/config.php';
requireEmployee();

$pageTitle = 'Satisfaction Index';
$emp = currentEmployee();
$empId = (int)$emp['id'];

$existing = db()->fetchOne('SELECT * FROM salary_feedback WHERE employee_id=?', 'i', [$empId]);
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $sal  = max(1, min(5, (int)($_POST['salary_satisfaction'] ?? 3)));
        $wlb  = max(1, min(5, (int)($_POST['work_life_balance'] ?? 3)));
        $ben  = max(1, min(5, (int)($_POST['benefits_rating'] ?? 3)));
        $comm = trim($_POST['comments'] ?? '');

        if ($existing) {
            db()->execute(
                'UPDATE salary_feedback SET salary_satisfaction=?,work_life_balance=?,benefits_rating=?,comments=? WHERE employee_id=?',
                'iiisi', [$sal, $wlb, $ben, $comm, $empId]
            );
        } else {
            db()->execute(
                'INSERT INTO salary_feedback (employee_id,salary_satisfaction,work_life_balance,benefits_rating,comments) VALUES (?,?,?,?,?)',
                'iiiis', [$empId, $sal, $wlb, $ben, $comm]
            );
        }
        $success = 'Feedback submitted successfully. Thank you!';
        $existing = db()->fetchOne('SELECT * FROM salary_feedback WHERE employee_id=?', 'i', [$empId]);
    }
}

function starDisplay(int $val): string {
    $out = '';
    for ($i = 1; $i <= 5; $i++) {
        $out .= $i <= $val ? '★' : '☆';
    }
    return $out;
}

include __DIR__ . '/../includes/header.php';
?>
<div class="main-wrapper">
  <?php include __DIR__ . '/../includes/employee_sidebar.php'; ?>
  <?php include __DIR__ . '/../includes/topbar.php'; ?>
  <div class="page-content">
    <div class="page-header">
      <div><h1>Satisfaction Index</h1><p>Share your feedback on compensation and work-life balance</p></div>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success alert-dismissible fade show auto-hide"><?= sanitize($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="row g-4">
      <!-- Feedback Form -->
      <div class="col-lg-7">
        <div class="card">
          <div class="card-header"><i class="bi bi-star-fill me-2"></i><?= $existing ? 'Update Your Feedback' : 'Submit Feedback' ?></div>
          <div class="card-body">
            <form method="POST">
              <?= csrfField() ?>

              <div class="mb-4">
                <label class="form-label fw-700">Salary Satisfaction</label>
                <div class="star-rating" id="salRating">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="star <?= ($existing && $existing['salary_satisfaction'] >= $i) ? 'active' : '' ?>">★</span>
                  <?php endfor; ?>
                  <input type="hidden" name="salary_satisfaction" value="<?= $existing['salary_satisfaction'] ?? 3 ?>">
                </div>
                <div class="form-text">How satisfied are you with your current salary?</div>
              </div>

              <div class="mb-4">
                <label class="form-label fw-700">Work-Life Balance</label>
                <div class="star-rating" id="wlbRating">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="star <?= ($existing && $existing['work_life_balance'] >= $i) ? 'active' : '' ?>">★</span>
                  <?php endfor; ?>
                  <input type="hidden" name="work_life_balance" value="<?= $existing['work_life_balance'] ?? 3 ?>">
                </div>
                <div class="form-text">How would you rate your work-life balance?</div>
              </div>

              <div class="mb-4">
                <label class="form-label fw-700">Benefits Rating</label>
                <div class="star-rating" id="benRating">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="star <?= ($existing && $existing['benefits_rating'] >= $i) ? 'active' : '' ?>">★</span>
                  <?php endfor; ?>
                  <input type="hidden" name="benefits_rating" value="<?= $existing['benefits_rating'] ?? 3 ?>">
                </div>
                <div class="form-text">How satisfied are you with the benefits package?</div>
              </div>

              <div class="mb-4">
                <label class="form-label fw-700">Additional Comments</label>
                <textarea class="form-control" name="comments" rows="4" placeholder="Share your thoughts..."><?= sanitize($existing['comments'] ?? '') ?></textarea>
              </div>

              <button type="submit" class="btn btn-primary">
                <i class="bi bi-send-fill me-2"></i><?= $existing ? 'Update Feedback' : 'Submit Feedback' ?>
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Current Ratings Display -->
      <div class="col-lg-5">
        <?php if ($existing): ?>
          <div class="card">
            <div class="card-header"><i class="bi bi-clipboard-check me-2"></i>Your Current Ratings</div>
            <div class="card-body">
              <?php $ratings = [
                ['Salary Satisfaction', $existing['salary_satisfaction']],
                ['Work-Life Balance',   $existing['work_life_balance']],
                ['Benefits Rating',     $existing['benefits_rating']],
              ]; foreach ($ratings as $r): ?>
                <div style="margin-bottom:20px;">
                  <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span style="font-weight:600;font-size:13px;"><?= $r[0] ?></span>
                    <span style="font-size:13px;color:#64748b;"><?= $r[1] ?>/5</span>
                  </div>
                  <div class="stars-display" style="font-size:22px;"><?= starDisplay($r[1]) ?></div>
                </div>
              <?php endforeach; ?>

              <?php
              $overall = round(($existing['salary_satisfaction'] + $existing['work_life_balance'] + $existing['benefits_rating']) / 3, 1);
              ?>
              <div class="divider"></div>
              <div style="text-align:center;padding:16px;background:#eff6ff;border-radius:10px;">
                <div style="font-size:13px;color:#64748b;margin-bottom:4px;">Overall Satisfaction</div>
                <div style="font-size:36px;font-weight:800;color:#2563eb;"><?= $overall ?><span style="font-size:18px;">/5</span></div>
                <div class="stars-display" style="font-size:24px;"><?= starDisplay((int)round($overall)) ?></div>
              </div>

              <?php if ($existing['comments']): ?>
                <div style="margin-top:16px;padding:12px;background:#f8fafc;border-radius:8px;font-size:13px;">
                  <strong>Your Comments:</strong><br>
                  <?= sanitize($existing['comments']) ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php else: ?>
          <div class="card">
            <div class="card-body empty-state">
              <i class="bi bi-star d-block"></i>
              <p>You haven't submitted feedback yet.<br>Share your thoughts using the form.</p>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
