<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/translations.php';
require_admin();
$lang = current_lang(); $theme = current_theme(); $T = translations($lang); $dir = $T['dir'];
$ct = $T['admin']['customers'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_customer') {
    require_manager();
    delete_customer($_POST['phone'] ?? '');
    header('Location: customers.php?deleted=1');
    exit;
}

$courses = load_courses(); $courseById = []; foreach ($courses as $c) $courseById[$c['id']] = $c;
$halls = load_halls(); $hallById = []; foreach ($halls as $h) $hallById[$h['id']] = $h;

$customers = list_all_customers();
usort($customers, function ($a, $b) { return count($b['bookings'] ?? []) <=> count($a['bookings'] ?? []); });
$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $q = mb_strtolower($search);
    $customers = array_filter($customers, function ($c) use ($q, $search) {
        return mb_strpos(mb_strtolower($c['fullName'] ?? ''), $q) !== false || strpos($c['phone'], $search) !== false;
    });
}
$profilePhone = $_GET['phone'] ?? '';
$profile = $profilePhone ? load_customer($profilePhone) : null;

$active_tab = 'customers';
require __DIR__ . '/../includes/admin_shell_start.php';
?>
<div class="admin-content-head" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
  <h2><?= h($ct['title']) ?></h2>
  <div style="display:flex; gap:8px;">
    <a href="export_excel.php?type=customers" class="btn btn-outline btn-sm">📊 <?= $lang === 'ar' ? 'تصدير إكسل' : 'Export Excel' ?></a>
    <a href="export_csv.php?type=customers" class="btn btn-outline btn-sm">📄 CSV</a>
    <a href="export_pdf.php?type=customers" target="_blank" class="btn btn-primary btn-sm">🖨️ PDF</a>
  </div>
</div>
<?php if (isset($_GET['deleted'])): ?><div class="toast-banner success"><span data-lucide="check-circle-2" style="width:16px;height:16px"></span><?= h($ct['deletedMsg']) ?></div><?php endif; ?>

<?php if ($profile): $bookings = $profile['bookings'] ?? []; usort($bookings, fn($a,$b) => strcmp($b['createdAt']??'', $a['createdAt']??'')); ?>
  <div class="card" style="padding:26px;margin-bottom:30px">
    <div class="customer-profile-head">
      <?php
        $parts = preg_split('/\s+/', trim($profile['fullName'] ?? '?'));
        $initials = mb_strtoupper(mb_substr($parts[0], 0, 1) . (isset($parts[1]) ? mb_substr($parts[1], 0, 1) : ''));
      ?>
      <div class="portal-avatar" style="width:70px;height:70px;font-size:24px;margin:0 auto 14px"><?= h($initials) ?></div>
      <h3><?= h($profile['fullName']) ?></h3>
      <p class="cell-muted" dir="ltr"><?= h($profile['phone']) ?><?= !empty($profile['email']) ? ' · ' . h($profile['email']) : '' ?></p>
    </div>
    <div style="margin-top:20px">
      <?php foreach ($bookings as $b): $isHall = ($b['type'] ?? 'course') === 'hall'; ?>
        <div class="card booking-card">
          <div class="booking-card-top">
            <div><span class="course-code"><?= $isHall ? h($T['admin']['bookings']['typeHall']) : h($courseById[$b['courseId']]['code'] ?? '') ?></span><h4><?= $isHall ? h($hallById[$b['hallId']]['name'][$lang] ?? '') : h($courseById[$b['courseId']]['name'][$lang] ?? '') ?></h4></div>
            <span class="badge badge-<?= h($b['status']) ?>"><?= h($T['status'][$b['status']] ?? '') ?></span>
          </div>
          <div class="booking-card-meta"><span><span data-lucide="calendar" style="width:13px;height:13px"></span><?= h(format_date_h($b['createdAt'], $lang)) ?></span><span><span data-lucide="ticket" style="width:13px;height:13px"></span><?= h($b['ref']) ?></span></div>
        </div>
      <?php endforeach; ?>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap">
      <a href="customers.php" class="btn btn-ghost btn-sm"><?= h($T['common']['close']) ?></a>
      <?php if (is_manager()): ?>
        <form method="post" data-confirm="<?= h(sprintf($ct['deleteConfirm'], $profile['fullName'] ?? $profile['phone'])) ?>" style="display:inline">
          <input type="hidden" name="action" value="delete_customer">
          <input type="hidden" name="phone" value="<?= h($profile['phone']) ?>">
          <button type="submit" class="btn btn-danger-outline btn-sm"><span data-lucide="trash-2" style="width:13px;height:13px"></span><?= h($ct['deleteCustomer']) ?></button>
        </form>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<form method="get" class="search-box" style="max-width:320px;margin-bottom:20px">
  <span data-lucide="search" style="width:16px;height:16px"></span>
  <input type="text" name="q" value="<?= h($search) ?>" placeholder="<?= h($ct['searchPh']) ?>">
</form>

<?php if (empty($customers)): ?>
  <div class="card"><div class="empty-state"><span data-lucide="users" style="width:44px;height:44px"></span><h3><?= h($ct['noCustomers']) ?></h3></div></div>
<?php else: ?>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th><?= h($ct['table']['name']) ?></th><th><?= h($ct['table']['phone']) ?></th><th><?= h($ct['table']['email']) ?></th><th><?= h($ct['table']['bookings']) ?></th><th><?= h($ct['table']['lastBooking']) ?></th><th><?= h($ct['table']['actions']) ?></th></tr></thead>
      <tbody>
        <?php foreach ($customers as $c):
          $bks = $c['bookings'] ?? []; usort($bks, fn($a,$b) => strcmp($b['createdAt']??'', $a['createdAt']??''));
          $last = $bks[0] ?? null;
        ?>
          <tr>
            <td class="cell-strong"><?= h($c['fullName']) ?></td>
            <td class="cell-muted" dir="ltr"><?= h($c['phone']) ?></td>
            <td class="cell-muted" dir="ltr"><?= h($c['email'] ?: '—') ?></td>
            <td><?= count($bks) ?></td>
            <td class="cell-muted"><?= $last ? h(format_date_h($last['createdAt'], $lang)) : '—' ?></td>
            <td><a href="customers.php?phone=<?= urlencode($c['phone']) ?>" class="btn btn-secondary btn-sm"><span data-lucide="eye" style="width:13px;height:13px"></span><?= h($ct['viewProfile']) ?></a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/admin_shell_end.php'; ?>
