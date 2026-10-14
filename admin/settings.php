<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/translations.php';
require_admin();
$lang = current_lang(); $theme = current_theme(); $T = translations($lang); $dir = $T['dir'];
$st = $T['admin']['settings'];
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'change_pin') {
        $myRole  = current_admin_role();
        $me      = find_admin_user($myRole);
        $current = $_POST['current_pin'] ?? '';
        $new     = $_POST['new_pin'] ?? '';
        $confirm = $_POST['confirm_pin'] ?? '';
        if (!$me || empty($me['password_hash']) || !password_verify($current, $me['password_hash'])) {
            $notice = 'error:' . $st['pinWrong'];
        } elseif ($new === '' || $new !== $confirm) {
            $notice = 'error:' . $st['pinMismatch'];
        } else {
            update_admin_password($myRole, password_hash($new, PASSWORD_DEFAULT));
            $notice = 'success:' . $st['pinChanged'];
        }
    }

    if ($action === 'save_branch') {
        $branchId = trim($_POST['branch_id'] ?? '');
        $data = [
            'name'    => ['ar' => trim($_POST['name_ar'] ?? ''), 'en' => trim($_POST['name_en'] ?? '')],
            'address' => ['ar' => trim($_POST['address_ar'] ?? ''), 'en' => trim($_POST['address_en'] ?? '')],
            'phone'   => trim($_POST['phone'] ?? ''),
            'email'   => trim($_POST['email'] ?? ''),
            'hours'   => ['ar' => trim($_POST['hours_ar'] ?? ''), 'en' => trim($_POST['hours_en'] ?? '')],
        ];
        update_json_file(SETTINGS_FILE, function ($all) use ($branchId, $data) {
            if (!isset($all['branches'])) $all['branches'] = [];
            if ($branchId !== '') {
                foreach ($all['branches'] as &$b) { if ($b['id'] === $branchId) { $b = array_merge($b, $data); } }
                unset($b);
                return $all;
            }
            $data['id'] = gen_uid('branch');
            $all['branches'][] = $data;
            return $all;
        });
        header('Location: settings.php?saved=1'); exit;
    }

    if ($action === 'delete_branch') {
        $id = $_POST['id'] ?? '';
        update_json_file(SETTINGS_FILE, function ($all) use ($id) {
            $all['branches'] = array_values(array_filter($all['branches'] ?? [], fn($b) => $b['id'] !== $id));
            return $all;
        });
        header('Location: settings.php?deleted=1'); exit;
    }
}

$branches = load_branches();
$editBranch = isset($_GET['edit_branch']) ? find_branch($branches, $_GET['edit_branch']) : null;

$active_tab = 'settings';
require __DIR__ . '/../includes/admin_shell_start.php';
?>
<div class="admin-content-head"><h2><?= h($st['title']) ?></h2></div>

<?php if ($notice): [$ntype, $nmsg] = explode(':', $notice, 2); ?>
  <div class="toast-banner <?= $ntype ?>"><span data-lucide="<?= $ntype === 'success' ? 'check-circle-2' : 'alert-triangle' ?>" style="width:16px;height:16px"></span><?= h($nmsg) ?></div>
<?php endif; ?>
<?php if (isset($_GET['saved'])): ?><div class="toast-banner success"><span data-lucide="check-circle-2" style="width:16px;height:16px"></span><?= h($T['common']['saved']) ?></div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="toast-banner success"><span data-lucide="check-circle-2" style="width:16px;height:16px"></span><?= $lang === 'ar' ? 'تم الحذف' : 'Deleted' ?></div><?php endif; ?>

<!-- Branches management -->
<div class="card settings-section">
  <h3><span data-lucide="map-pin" style="width:17px;height:17px"></span><?= h($st['branchesTitle']) ?></h3>

  <?php foreach ($branches as $branch): ?>
    <div class="card" style="background:var(--surface-2);padding:16px 18px;margin-bottom:12px;display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px">
      <div>
        <div style="font-weight:700;font-size:14.5px"><?= h($branch['name'][$lang]) ?></div>
        <div class="cell-muted" style="font-size:12.5px;margin-top:3px"><?= h($branch['address'][$lang]) ?></div>
        <div class="cell-muted" style="font-size:12.5px;margin-top:2px" dir="ltr"><?= h($branch['phone']) ?> <?php if (!empty($branch['email'])): ?>· <?= h($branch['email']) ?><?php endif; ?></div>
        <div class="cell-muted" style="font-size:12.5px;margin-top:2px"><span data-lucide="clock" style="width:11px;height:11px;vertical-align:-1px"></span> <?= h($branch['hours'][$lang] ?? '') ?></div>
      </div>
      <div class="row-actions">
        <a href="settings.php?edit_branch=<?= urlencode($branch['id']) ?>#branch-form" class="icon-btn" aria-label="edit"><span data-lucide="pencil" style="width:15px;height:15px"></span></a>
        <form method="post" data-confirm="<?= h($st['deleteBranchConfirm']) ?>" style="display:inline">
          <input type="hidden" name="action" value="delete_branch"><input type="hidden" name="id" value="<?= h($branch['id']) ?>">
          <button type="submit" class="icon-btn" aria-label="delete"><span data-lucide="trash-2" style="width:15px;height:15px"></span></button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>

  <div id="branch-form" style="margin-top:18px;padding-top:18px;border-top:1px solid var(--border)">
    <h4 style="font-size:14px;margin-bottom:14px;color:var(--text-soft)"><?= $editBranch ? h($st['editBranch']) : h($st['addBranch']) ?></h4>
    <form method="post">
      <input type="hidden" name="action" value="save_branch">
      <input type="hidden" name="branch_id" value="<?= h($editBranch['id'] ?? '') ?>">
      <div class="form-row-2">
        <div class="form-group"><label class="form-label"><?= h($st['branchNameAr']) ?></label><input class="form-input" name="name_ar" value="<?= h($editBranch['name']['ar'] ?? '') ?>"></div>
        <div class="form-group"><label class="form-label"><?= h($st['branchNameEn']) ?></label><input class="form-input" name="name_en" value="<?= h($editBranch['name']['en'] ?? '') ?>" dir="ltr"></div>
      </div>
      <div class="form-row-2">
        <div class="form-group"><label class="form-label"><?= h($st['branchAddressAr']) ?></label><input class="form-input" name="address_ar" value="<?= h($editBranch['address']['ar'] ?? '') ?>"></div>
        <div class="form-group"><label class="form-label"><?= h($st['branchAddressEn']) ?></label><input class="form-input" name="address_en" value="<?= h($editBranch['address']['en'] ?? '') ?>" dir="ltr"></div>
      </div>
      <div class="form-row-2">
        <div class="form-group"><label class="form-label"><?= h($st['branchPhone']) ?></label><input class="form-input" name="phone" value="<?= h($editBranch['phone'] ?? '') ?>" dir="ltr"></div>
        <div class="form-group"><label class="form-label"><?= h($st['branchEmail']) ?></label><input class="form-input" name="email" value="<?= h($editBranch['email'] ?? '') ?>" dir="ltr"></div>
      </div>
      <div class="form-row-2">
        <div class="form-group"><label class="form-label"><?= h($st['branchHoursAr']) ?></label><input class="form-input" name="hours_ar" value="<?= h($editBranch['hours']['ar'] ?? '') ?>"></div>
        <div class="form-group"><label class="form-label"><?= h($st['branchHoursEn']) ?></label><input class="form-input" name="hours_en" value="<?= h($editBranch['hours']['en'] ?? '') ?>" dir="ltr"></div>
      </div>
      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary"><span data-lucide="save" style="width:16px;height:16px"></span><?= h($T['common']['save']) ?></button>
        <?php if ($editBranch): ?><a href="settings.php" class="btn btn-ghost"><?= h($T['common']['cancel']) ?></a><?php endif; ?>
      </div>
    </form>
  </div>
</div>

<!-- PIN change -->
<div class="card settings-section">
  <h3><span data-lucide="key-round" style="width:17px;height:17px"></span><?= h($st['changePin']) ?></h3>
  <p class="desc"><?= h($st['pinRoleNote']) ?> <strong><?= current_admin_role() === 'manager' ? h($st['roleManager']) : h($st['roleAdmin']) ?></strong></p>
  <form method="post">
    <input type="hidden" name="action" value="change_pin">
    <div class="form-group"><label class="form-label"><?= h($st['currentPin']) ?></label><input class="form-input" type="password" name="current_pin" dir="ltr"></div>
    <div class="form-row-2">
      <div class="form-group"><label class="form-label"><?= h($st['newPin']) ?></label><input class="form-input" type="password" name="new_pin" dir="ltr"></div>
      <div class="form-group"><label class="form-label"><?= h($st['confirmPin']) ?></label><input class="form-input" type="password" name="confirm_pin" dir="ltr"></div>
    </div>
    <button type="submit" class="btn btn-primary"><span data-lucide="save" style="width:16px;height:16px"></span><?= h($st['savePin']) ?></button>
  </form>
</div>

<!-- Export -->
<div class="card settings-section">
  <h3><span data-lucide="file-spreadsheet" style="width:17px;height:17px"></span><?= h($st['exportTitle']) ?></h3>
  <p class="desc"><?= h($st['exportDesc']) ?></p>
  <a href="export_csv.php" class="btn btn-gold"><span data-lucide="download" style="width:16px;height:16px"></span><?= h($st['exportBtn']) ?></a>
</div>

<?php if (is_manager()): ?>
<div class="card settings-section">
  <h3><span data-lucide="user-x" style="width:17px;height:17px"></span><?= h($st['blocklistTitle']) ?></h3>
  <p class="desc"><?= h($st['blocklistDesc']) ?></p>
  <a href="blocklist.php" class="btn btn-secondary"><span data-lucide="shield-ban" style="width:16px;height:16px"></span><?= h($st['blocklistBtn']) ?></a>
</div>
<?php endif; ?>

<div class="card settings-section danger-zone">
  <h3><span data-lucide="file-warning" style="width:17px;height:17px"></span><?= h($st['dangerTitle'] ?? ($lang === 'ar' ? 'منطقة حساسة' : 'Sensitive area')) ?></h3>
  <p class="desc" style="color:var(--danger)"><?= $lang === 'ar'
      ? 'صور بطاقات العملاء وبياناتهم محفوظة فعليًا كملفات على السيرفر (أو ترفع مباشرة لـ Cloudinary لو التوثيق شغال). تأكد إن ملف data/.htaccess موجود دايمًا، واعمل نسخة احتياطية من مجلد data/ بانتظام.'
      : 'Customer ID photos and data are stored as real files on the server (or uploaded directly to Cloudinary if configured). Make sure data/.htaccess always stays in place, and back up the data/ folder regularly.' ?></p>
</div>
<?php require __DIR__ . '/../includes/admin_shell_end.php'; ?>
