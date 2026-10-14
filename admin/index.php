<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/translations.php';
require_admin();

$lang = current_lang(); $theme = current_theme(); $T = translations($lang); $dir = $T['dir'];
$active_tab = 'overview';
require __DIR__ . '/../includes/admin_shell_start.php';

$customers     = list_all_customers();
$totalBookings = count($allBookings);
$pending       = count(array_filter($allBookings, fn($b) => $b['status'] === 'pending'));
$confirmed     = count(array_filter($allBookings, fn($b) => $b['status'] === 'confirmed'));
$activeCourses = count(array_filter($courses, fn($c) => $c['status'] === 'active'));
$recent        = array_slice($allBookings, 0, 6);

$analytics     = calculate_analytics_summary();
$reminders     = get_upcoming_reminders();

$courseById = []; foreach ($courses as $c) $courseById[$c['id']] = $c;
$hallById   = []; foreach ($halls as $h) $hallById[$h['id']] = $h;

$stats = [
  ['icon' => 'wallet', 'num' => number_format($analytics['totalRevenue']) . ' EGP', 'label' => ($lang === 'ar' ? 'إجمالي الإيرادات' : 'Total Revenue'), 'highlight' => true],
  ['icon' => 'clipboard-list', 'num' => $totalBookings, 'label' => $T['admin']['overview']['totalBookings']],
  ['icon' => 'hourglass', 'num' => $pending, 'label' => $T['admin']['overview']['pending']],
  ['icon' => 'check-circle-2', 'num' => $confirmed, 'label' => $T['admin']['overview']['confirmed']],
  ['icon' => 'users', 'num' => count($customers), 'label' => $T['admin']['overview']['totalCustomers']],
];

$topCourses = $analytics['topCourses'];
?>
<div class="admin-content-head" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
  <h2><?= h($T['admin']['overview']['title']) ?></h2>
  <div style="display:flex; gap:8px;">
    <a href="export_excel.php?type=bookings" class="btn btn-outline btn-sm">📊 <?= $lang === 'ar' ? 'تصدير إكسل' : 'Export Excel' ?></a>
    <a href="export_pdf.php?type=bookings" target="_blank" class="btn btn-primary btn-sm">🖨️ <?= $lang === 'ar' ? 'تقرير PDF' : 'PDF Report' ?></a>
  </div>
</div>

<!-- Primary Key Stats -->
<div class="stat-grid">
  <?php foreach ($stats as $s): ?>
    <div class="card stat-card <?= !empty($s['highlight']) ? 'stat-card-highlight' : '' ?>">
      <span class="stat-icon"><span data-lucide="<?= h($s['icon']) ?>" style="width:18px;height:18px"></span></span>
      <div class="stat-num"><?= h($s['num']) ?></div>
      <div class="stat-label"><?= h($s['label']) ?></div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Financial Revenue Breakdown & Reminders Widget -->
<div class="dashboard-grid-2col" style="display:grid; grid-template-columns: 2fr 1fr; gap:20px; margin-top:20px;">

  <!-- Visual Charts / Analytics Card -->
  <div class="card" style="padding: 20px;">
    <h3 style="font-size:16px; font-weight:700; margin-bottom:15px; display:flex; align-items:center; gap:8px;">
      <span data-lucide="trending-up" style="width:18px;height:18px; color:var(--primary);"></span>
      <?= $lang === 'ar' ? 'تحليل الإيرادات والحجوزات' : 'Revenue & Bookings Analytics' ?>
    </h3>
    
    <div style="display:flex; gap:15px; margin-bottom:15px; flex-wrap:wrap;">
      <div style="background:var(--bg-subtle, #f8fafc); border-radius:8px; padding:12px; flex:1; min-width:140px;">
        <span style="font-size:12px; color:var(--text-muted);"><?= $lang === 'ar' ? 'إيرادات الكورسات' : 'Courses Revenue' ?></span>
        <div style="font-size:18px; font-weight:bold; color:var(--primary);"><?= number_format($analytics['courseRevenue']) ?> EGP</div>
      </div>
      <div style="background:var(--bg-subtle, #f8fafc); border-radius:8px; padding:12px; flex:1; min-width:140px;">
        <span style="font-size:12px; color:var(--text-muted);"><?= $lang === 'ar' ? 'إيرادات القاعات' : 'Halls Revenue' ?></span>
        <div style="font-size:18px; font-weight:bold; color:#059669;"><?= number_format($analytics['hallRevenue']) ?> EGP</div>
      </div>
      <div style="background:var(--bg-subtle, #f8fafc); border-radius:8px; padding:12px; flex:1; min-width:140px;">
        <span style="font-size:12px; color:var(--text-muted);"><?= $lang === 'ar' ? 'قيد المراجعة' : 'Pending Revenue' ?></span>
        <div style="font-size:18px; font-weight:bold; color:#d97706;"><?= number_format($analytics['pendingRevenue']) ?> EGP</div>
      </div>
    </div>

    <!-- Chart Container -->
    <div style="position: relative; height: 220px; width: 100%;">
      <canvas id="revenueChart"></canvas>
    </div>
  </div>

  <!-- Reminders & Today's Schedule Card -->
  <div class="card" style="padding: 20px;">
    <h3 style="font-size:16px; font-weight:700; margin-bottom:15px; display:flex; align-items:center; gap:8px;">
      <span data-lucide="bell" style="width:18px;height:18px; color:#d97706;"></span>
      <?= $lang === 'ar' ? 'تنبيهات ومواعيد اليوم' : "Today's Schedule & Reminders" ?>
    </h3>

    <?php if (empty($reminders['hallToday']) && empty($reminders['pendingAction']) && empty($reminders['paymentReminders'])): ?>
      <div style="text-align:center; padding:30px 10px; color:var(--text-muted);">
        <span data-lucide="check-circle-2" style="width:36px;height:36px; stroke-width:1.5; color:#10b981; margin-bottom:8px;"></span>
        <p style="font-size:13px;"><?= $lang === 'ar' ? 'لا توجد تنبيهات عاجلة أو أقساط مستحقة.' : 'No urgent alerts or payment due dates.' ?></p>
      </div>
    <?php else: ?>
      <div style="display:flex; flex-direction:column; gap:10px;">
        <?php foreach ($reminders['paymentReminders'] as $pRem): ?>
          <div style="border-right:3px solid #ef4444; background:#fef2f2; padding:10px; border-radius:6px; font-size:13px;">
            <div style="font-weight:bold; color:#991b1b; display:flex; justify-content:space-between; align-items:center;">
              <span>💳 <?= $lang === 'ar' ? 'استحقاق مالي متبقي' : 'Payment Due' ?></span>
              <span style="font-size:12px; font-weight:bold; color:#ef4444;"><?= number_format($pRem['remainingAmount']) ?> EGP</span>
            </div>
            <div style="color:#7f1d1d; margin-top:3px;">
              👤 <?= h($pRem['fullName']) ?> (<?= h($pRem['phone']) ?>)
            </div>
            <div style="font-size:11.5px; color:#b91c1c; margin-top:2px;">
              📅 <?= $lang === 'ar' ? 'ميعاد السداد المحدد: ' . h($pRem['paymentDueDate']) : 'Due Date: ' . h($pRem['paymentDueDate']) ?>
            </div>
            <a href="bookings.php?view=<?= urlencode($pRem['id']) ?>" style="font-size:12px; color:#991b1b; text-decoration:underline; font-weight:bold; display:inline-block; margin-top:4px;">
              <?= $lang === 'ar' ? 'تحصيل وتسجيل الدفع ←' : 'Collect & Record Payment →' ?>
            </a>
          </div>
        <?php endforeach; ?>

        <?php foreach ($reminders['hallToday'] as $hBooking): ?>
          <div style="border-right:3px solid #0284c7; background:var(--bg-subtle, #f0f9ff); padding:10px; border-radius:6px; font-size:13px;">
            <div style="font-weight:bold; color:#0369a1;">
              🏛️ <?= h($hBooking['hallName'][$lang] ?? 'قاعة') ?> — <?= h($hBooking['slotStart']) ?> - <?= h($hBooking['slotEnd']) ?>
            </div>
            <div style="color:var(--text-muted); margin-top:3px;">
              👤 <?= h($hBooking['fullName']) ?> (<?= h($hBooking['phone']) ?>)
            </div>
          </div>
        <?php endforeach; ?>

        <?php if ($pendingCount > 0): ?>
          <div style="border-right:3px solid #f59e0b; background:#fffbe6; padding:10px; border-radius:6px; font-size:13px;">
            <div style="font-weight:bold; color:#b45309;">
              ⏳ <?= $lang === 'ar' ? "تنبيه: يوجد $pendingCount حجز قيد المراجعة" : "Alert: $pendingCount pending bookings awaiting action" ?>
            </div>
            <a href="bookings.php" style="font-size:12px; color:#d97706; text-decoration:underline; display:inline-block; margin-top:4px;">
              <?= $lang === 'ar' ? 'مراجعة الحجوزات الآن ←' : 'Review Bookings Now →' ?>
            </a>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Top Requested Courses & Recent Bookings Grid -->
<div class="dashboard-grid-2col" style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-top:20px;">

  <!-- Top Requested Courses -->
  <div class="card" style="padding:20px;">
    <h3 style="font-size:16px; font-weight:700; margin-bottom:15px; display:flex; align-items:center; gap:8px;">
      <span data-lucide="flame" style="width:18px;height:18px; color:#ef4444;"></span>
      <?= $lang === 'ar' ? 'الكورسات الأكثر طلباً' : 'Top Requested Courses' ?>
    </h3>
    <table class="data-table" style="font-size:13px;">
      <thead>
        <tr>
          <th>#</th>
          <th><?= $lang === 'ar' ? 'اسم الكورس' : 'Course Name' ?></th>
          <th><?= $lang === 'ar' ? 'الحجوزات' : 'Bookings' ?></th>
          <th><?= $lang === 'ar' ? 'الإيرادات' : 'Revenue' ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($topCourses as $idx => $tc): ?>
          <tr>
            <td><strong>#<?= $idx + 1 ?></strong></td>
            <td><?= h($tc['name'][$lang] ?? '') ?></td>
            <td><span class="badge badge-confirmed"><?= $tc['bookings'] ?></span></td>
            <td><strong><?= number_format($tc['totalRevenue']) ?> EGP</strong></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Recent Bookings Table -->
  <div class="card" style="padding:20px;">
    <div class="table-toolbar" style="margin-bottom:15px; margin-top:0;">
      <h3 style="font-size:16px; font-weight:700; margin:0; display:flex; align-items:center; gap:8px;">
        <span data-lucide="clock" style="width:18px;height:18px; color:var(--primary);"></span>
        <?= h($T['admin']['overview']['recent']) ?>
      </h3>
      <a href="bookings.php" class="btn btn-ghost btn-sm"><?= h($T['admin']['overview']['viewAll']) ?></a>
    </div>

    <?php if (empty($recent)): ?>
      <div class="empty-state" style="padding:30px 0;"><span data-lucide="clipboard-list" style="width:36px;height:36px"></span><h3><?= h($T['admin']['overview']['noRecent']) ?></h3></div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="data-table" style="font-size:13px;">
          <thead>
            <tr>
              <th><?= h($T['admin']['bookings']['table']['name'] ?? 'الاسم') ?></th>
              <th><?= $lang === 'ar' ? 'النوع' : 'Type' ?></th>
              <th><?= $lang === 'ar' ? 'الحالة' : 'Status' ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recent as $b): $isHall = ($b['type'] ?? 'course') === 'hall'; ?>
              <tr>
                <td class="cell-strong"><?= h($b['fullName']) ?></td>
                <td class="cell-muted"><?= $isHall ? h($T['admin']['bookings']['typeHall']) : h($T['admin']['bookings']['typeCourse']) ?></td>
                <td><span class="badge badge-<?= h($b['status']) ?>"><?= h($T['status'][$b['status']] ?? '') ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>

<!-- Include Chart.js script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const ctx = document.getElementById('revenueChart');
  if (!ctx) return;

  const monthlyData = <?= json_encode($analytics['monthlyStats']) ?>;
  const labels = monthlyData.map(d => d.month);
  const revenues = monthlyData.map(d => d.revenue);

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: '<?= $lang === 'ar' ? "الإيرادات (جنيه)" : "Revenue (EGP)" ?>',
        data: revenues,
        borderColor: '#0284c7',
        backgroundColor: 'rgba(2, 132, 199, 0.1)',
        fill: true,
        tension: 0.3,
        borderWidth: 2,
        pointBackgroundColor: '#0284c7'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: { beginAtZero: true }
      }
    }
  });
});
</script>

<?php require __DIR__ . '/../includes/admin_shell_end.php'; ?>
