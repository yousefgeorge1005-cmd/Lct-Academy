<?php
/**
 * Shared admin shell. Include AFTER require_admin() and after $lang/$T/$dir/
 * $theme and $active_tab are set. Prints <html><head>...sidebar...<main>.
 * Pair with admin_shell_end.php to close the tags.
 */
$courses = load_courses();
$halls = load_halls();
$allBookings = all_bookings();
$pendingCount = count(array_filter($allBookings, function ($b) { return $b['status'] === 'pending'; }));

$shellReminders = get_upcoming_reminders();
$totalAlerts = count($shellReminders['hallToday']) + count($shellReminders['pendingAction']) + count($shellReminders['paymentReminders']);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>" data-theme="<?= $theme ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($T['admin']['tabs'][$active_tab] ?? '') ?> — <?= h($T['brand']['name']) ?></title>
<link rel="icon" href="../assets/logo.png">
<link rel="stylesheet" href="../assets/style.css">
<script src="https://unpkg.com/lucide@0.383.0/dist/umd/lucide.js" defer></script>
<script src="../assets/script.js" defer></script>
</head>
<body>
<div class="admin-shell">
  <aside class="admin-sidebar">
    <div class="admin-sidebar-brand">
      <span class="logo-badge" style="width:34px;height:34px"><img src="../assets/logo.png" alt="LCT"></span>
      <span><?= h($T['brand']['name']) ?></span>
    </div>
    <a href="index.php" class="admin-nav-item <?= $active_tab === 'overview' ? 'active' : '' ?>"><span data-lucide="layout-dashboard" style="width:17px;height:17px"></span> <?= h($T['admin']['tabs']['overview']) ?></a>
    <a href="site_editor.php" class="admin-nav-item <?= ($active_tab === 'site_editor' && empty($_GET['view'])) ? 'active' : '' ?>"><span data-lucide="edit-3" style="width:17px;height:17px"></span> <?= h($T['admin']['tabs']['site_editor']) ?></a>
    <a href="site_editor.php?view=canvas" class="admin-nav-item <?= ($active_tab === 'site_editor' && ($_GET['view'] ?? '') === 'canvas') ? 'active' : '' ?>" style="color:var(--gold);font-weight:700;"><span data-lucide="mouse-pointer-click" style="width:17px;height:17px;color:var(--gold)"></span> <?= $lang === 'ar' ? '🎯 اضغط وعدّل الموقع' : '🎯 Point & Edit Site' ?></a>
    <a href="courses.php" class="admin-nav-item <?= $active_tab === 'courses' ? 'active' : '' ?>"><span data-lucide="book-open" style="width:17px;height:17px"></span> <?= h($T['admin']['tabs']['courses']) ?></a>
    <a href="halls.php" class="admin-nav-item <?= $active_tab === 'halls' ? 'active' : '' ?>"><span data-lucide="building-2" style="width:17px;height:17px"></span> <?= h($T['admin']['tabs']['halls']) ?></a>
    <a href="schedule.php" class="admin-nav-item <?= $active_tab === 'schedule' ? 'active' : '' ?>"><span data-lucide="calendar-range" style="width:17px;height:17px"></span> <?= h($T['admin']['tabs']['schedule']) ?></a>
    <a href="bookings.php" class="admin-nav-item <?= $active_tab === 'bookings' ? 'active' : '' ?>"><span data-lucide="clipboard-list" style="width:17px;height:17px"></span> <?= h($T['admin']['tabs']['bookings']) ?><?php if ($pendingCount > 0): ?><span class="count"><?= $pendingCount ?></span><?php endif; ?></a>
    <a href="customers.php" class="admin-nav-item <?= $active_tab === 'customers' ? 'active' : '' ?>"><span data-lucide="users" style="width:17px;height:17px"></span> <?= h($T['admin']['tabs']['customers']) ?></a>
    <?php if (is_manager()): ?>
    <a href="blocklist.php" class="admin-nav-item <?= $active_tab === 'blocklist' ? 'active' : '' ?>"><span data-lucide="shield-ban" style="width:17px;height:17px"></span> <?= h($T['admin']['tabs']['blocklist']) ?></a>
    <?php endif; ?>
    <a href="settings.php" class="admin-nav-item <?= $active_tab === 'settings' ? 'active' : '' ?>"><span data-lucide="settings" style="width:17px;height:17px"></span> <?= h($T['admin']['tabs']['settings']) ?></a>
    <div class="admin-sidebar-foot">
      <div class="toggles" style="position:relative;">
        <button type="button" class="icon-btn" id="notifBtn" title="reminders" style="position:relative;">
          <span data-lucide="bell" style="width:15px;height:15px"></span>
          <?php if ($totalAlerts > 0): ?>
            <span style="position:absolute; top:-2px; right:-2px; background:#ef4444; color:#fff; font-size:10px; font-weight:bold; border-radius:10px; padding:1px 4px; min-width:14px; text-align:center; border:1px solid var(--bg-surface);"><?= $totalAlerts ?></span>
          <?php endif; ?>
        </button>

        <!-- Notification Popover Menu -->
        <div id="notifDropdown" class="notif-dropdown" style="display:none; position:absolute; bottom:40px; <?= $lang === 'ar' ? 'right:0;' : 'left:0;' ?> width:280px; background:var(--bg-surface, #ffffff); border:1px solid var(--border-color, #e2e8f0); border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.15); z-index:9999; padding:12px; font-size:12px;">
          <div style="font-weight:bold; padding-bottom:8px; border-bottom:1px solid var(--border-color, #e2e8f0); margin-bottom:8px; display:flex; justify-content:space-between;">
            <span>🔔 <?= $lang === 'ar' ? 'التنبيهات والتذكيرات' : 'Notifications & Reminders' ?></span>
            <span style="color:var(--text-muted); font-weight:normal;"><?= $totalAlerts ?></span>
          </div>

          <div style="max-height:240px; overflow-y:auto; display:flex; flex-direction:column; gap:8px;">
            <?php foreach ($shellReminders['paymentReminders'] as $pr): ?>
              <div style="background:#fef2f2; color:#991b1b; padding:8px; border-radius:6px; border-right:3px solid #ef4444;">
                <strong>💳 <?= $lang === 'ar' ? 'تذكير مالي مستحق' : 'Payment Due Reminder' ?></strong>
                <div>👤 <?= h($pr['fullName']) ?> — <?= number_format($pr['remainingAmount']) ?> EGP</div>
                <div style="font-size:11px; color:#b91c1c; margin-top:2px;">📅 <?= $lang === 'ar' ? 'الميعاد: ' . h($pr['paymentDueDate']) : 'Due: ' . h($pr['paymentDueDate']) ?></div>
                <a href="bookings.php?view=<?= urlencode($pr['id']) ?>" style="display:block; margin-top:3px; color:#991b1b; text-decoration:underline; font-weight:bold;"><?= $lang === 'ar' ? 'تحصيل المتبقي ←' : 'Collect Payment →' ?></a>
              </div>
            <?php endforeach; ?>

            <?php if ($pendingCount > 0): ?>
              <div style="background:#fffbe6; color:#b45309; padding:8px; border-radius:6px; border-right:3px solid #f59e0b;">
                <strong>⏳ <?= $lang === 'ar' ? "$pendingCount حجز ينتظر التأكيد" : "$pendingCount pending bookings" ?></strong>
                <a href="bookings.php" style="display:block; margin-top:3px; color:#d97706; text-decoration:underline;"><?= $lang === 'ar' ? 'عرض الحجوزات' : 'View Bookings' ?></a>
              </div>
            <?php endif; ?>

            <?php foreach ($shellReminders['hallToday'] as $ht): ?>
              <div style="background:#f0f9ff; color:#0369a1; padding:8px; border-radius:6px; border-right:3px solid #0284c7;">
                <strong>🏛️ <?= h($ht['hallName'][$lang] ?? 'قاعة') ?> (اليوم)</strong>
                <div><?= h($ht['slotStart']) ?> - <?= h($ht['slotEnd']) ?></div>
                <div style="color:var(--text-muted); font-size:11px;"><?= h($ht['fullName']) ?></div>
              </div>
            <?php endforeach; ?>

            <?php if ($totalAlerts === 0): ?>
              <div style="text-align:center; padding:15px 0; color:var(--text-muted);">
                <?= $lang === 'ar' ? ' لا توجد تنبيهات جديدة' : 'No new notifications' ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <button type="button" class="icon-btn" data-action="toggle-lang" title="lang"><span data-lucide="globe" style="width:15px;height:15px"></span></button>
        <button type="button" class="icon-btn" data-action="toggle-theme" title="theme"><span data-lucide="<?= $theme === 'light' ? 'moon' : 'sun' ?>" style="width:16px;height:16px"></span></button>
        <a href="../index.php" class="icon-btn" title="view site" style="text-decoration:none"><span data-lucide="eye" style="width:16px;height:16px"></span></a>
      </div>
      <a href="logout.php" class="admin-nav-item"><span data-lucide="log-out" style="width:17px;height:17px"></span> <?= h($T['admin']['logout']) ?></a>
    </div>
  </aside>
  <main class="admin-content">

<script>
document.addEventListener('DOMContentLoaded', function() {
  const btn = document.getElementById('notifBtn');
  const dropdown = document.getElementById('notifDropdown');
  if (btn && dropdown) {
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    });
    document.addEventListener('click', function() {
      dropdown.style.display = 'none';
    });
    dropdown.addEventListener('click', function(e) { e.stopPropagation(); });
  }
});
</script>
