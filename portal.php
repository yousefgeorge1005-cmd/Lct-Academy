<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/translations.php';
$current_page = 'portal';

$query = trim($_GET['phone'] ?? '');
$searched = $query !== '';
$customer = null;
if ($searched) {
    // Accept either a phone number or a booking reference (e.g. "LCT-123456") in the same box.
    $customer = load_customer($query);
    if (!$customer) {
        $ownerPhone = find_phone_by_ref($query);
        if ($ownerPhone) $customer = load_customer($ownerPhone);
    }
}
$courses = load_courses();
$halls = load_halls();
$courseById = [];
foreach ($courses as $c) $courseById[$c['id']] = $c;
$hallById = [];
$lang = current_lang();
$T = translations($lang);
$page_title = $T['portal']['title'];
$page_desc  = $T['portal']['sub'];

require __DIR__ . '/includes/header.php';
?>
<div class="container section">
<?php if (!$searched): ?>
  <div class="card portal-lookup">
    <div class="icon-circle"><span data-lucide="user-circle" style="width:28px;height:28px"></span></div>
    <h1 style="font-size:24px"><?= h($T['portal']['title']) ?></h1>
    <p style="color:var(--text-soft);margin-top:10px;font-size:14.5px"><?= h($T['portal']['sub']) ?></p>
    <form method="get" class="portal-lookup-row">
      <input class="form-input" type="text" name="phone" placeholder="<?= h($T['portal']['phonePh']) ?>" dir="ltr" maxlength="20" autocapitalize="characters">
      <button type="submit" class="btn btn-primary"><span data-lucide="search" style="width:16px;height:16px"></span></button>
    </form>
  </div>
<?php else: ?>
  <?php
    $bookings = $customer['bookings'] ?? [];
    usort($bookings, function ($a, $b) { return strcmp($b['createdAt'] ?? '', $a['createdAt'] ?? ''); });
    $initials = '?';
    if ($customer && !empty($customer['fullName'])) {
        $parts = preg_split('/\s+/', trim($customer['fullName']));
        $initials = mb_strtoupper(mb_substr($parts[0], 0, 1) . (isset($parts[1]) ? mb_substr($parts[1], 0, 1) : ''));
    }
  ?>
  <div class="portal-header">
    <div class="portal-user-row">
      <div class="portal-avatar"><?= h($initials) ?></div>
      <div>
        <div style="font-size:13px;color:var(--text-soft)"><?= h($T['portal']['welcomeBack']) ?></div>
        <h2 style="font-size:21px"><?= h($customer['fullName'] ?? ($customer['phone'] ?? '')) ?></h2>
      </div>
    </div>
    <a href="<?= BASE_URL ?>/portal.php" class="btn btn-secondary btn-sm"><span data-lucide="refresh-cw" style="width:14px;height:14px"></span><?= $lang === 'ar' ? 'بحث تاني' : 'New search' ?></a>
  </div>

  <?php if (!$customer || empty($bookings)): ?>
    <div class="empty-state">
      <span data-lucide="search" style="width:44px;height:44px"></span>
      <h3><?= h($T['portal']['notFoundTitle']) ?></h3>
      <p><?= h($T['portal']['notFoundDesc']) ?></p>
    </div>
  <?php else: ?>
    <h3 style="font-size:15px;color:var(--text-soft);margin-bottom:16px;font-weight:700"><?= h($T['portal']['myBookings']) ?> (<?= count($bookings) ?>)</h3>
    <?php
    $branches = load_branches(); $branchById = []; foreach ($branches as $brc) $branchById[$brc['id']] = $brc;
    foreach ($bookings as $b):
      $isHall = ($b['type'] ?? 'course') === 'hall';
      $course = !$isHall ? ($courseById[$b['courseId']] ?? null) : null;
      $hall = $isHall ? ($hallById[$b['hallId']] ?? null) : null;
      $bBranch = $branchById[$b['branchId'] ?? ''] ?? null;
    ?>
      <div class="card booking-card">
        <div class="booking-card-top">
          <div>
            <span class="course-code"><?= $isHall ? h($T['portal']['hallBookingLabel']) : h($course['code'] ?? '') ?></span>
            <h4><?= $isHall ? h($hall['name'][$lang] ?? $b['hallId']) : h($course['name'][$lang] ?? $b['courseId']) ?></h4>
          </div>
          <span class="badge badge-<?= h($b['status']) ?>"><?= h($T['status'][$b['status']] ?? $b['status']) ?></span>
        </div>
        <div class="booking-card-meta">
          <span><span data-lucide="calendar" style="width:13px;height:13px"></span><?= h($T['portal']['bookedOn']) ?>: <?= h(format_date_h($b['createdAt'], $lang)) ?></span>
          <?php if ($isHall): ?><span><span data-lucide="clock" style="width:13px;height:13px"></span><?= h(format_date_h($b['slotDate'], $lang)) ?>, <?= h($b['slotStart']) ?>–<?= h($b['slotEnd']) ?></span><?php endif; ?>
          <?php if ($bBranch): ?><span><span data-lucide="map-pin" style="width:13px;height:13px"></span><?= h($bBranch['name'][$lang]) ?></span><?php endif; ?>
          <span><span data-lucide="ticket" style="width:13px;height:13px"></span><?= h($b['ref']) ?></span>
        </div>
        <div class="status-note <?= h($b['status']) ?>">
          <span data-lucide="<?= ['pending'=>'hourglass','confirmed'=>'check-circle-2','rejected'=>'x-circle','completed'=>'badge-check','cancelled'=>'ban'][$b['status']] ?? 'hourglass' ?>" style="width:15px;height:15px"></span>
          <span><?= h($T['portal']['statusNote'][$b['status']] ?? '') ?></span>
        </div>
      </div>
    <?php endforeach; ?>
    <a href="<?= BASE_URL ?>/courses.php" class="btn btn-secondary" style="margin-top:10px"><span data-lucide="plus" style="width:15px;height:15px"></span><?= h($T['portal']['newBooking']) ?></a>
  <?php endif; ?>
<?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
