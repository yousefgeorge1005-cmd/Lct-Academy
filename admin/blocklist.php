<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/translations.php';
require_manager();
$lang = current_lang(); $theme = current_theme(); $T = translations($lang); $dir = $T['dir'];
$bl = $T['admin']['blocklist'];
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'block') {
        $phone  = trim($_POST['phone'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        if (!validate_phone_eg($phone)) {
            $notice = 'error:' . $bl['invalidPhone'];
        } elseif (is_phone_blocked($phone)) {
            $notice = 'error:' . $bl['alreadyBlocked'];
        } else {
            block_phone_number($phone, $reason);
            header('Location: blocklist.php?blocked=1'); exit;
        }
    }

    if ($action === 'unblock') {
        unblock_phone_number($_POST['phone'] ?? '');
        header('Location: blocklist.php?unblocked=1'); exit;
    }
}

$blocked = load_blocked_numbers();
usort($blocked, fn($a, $b) => strcmp($b['blockedAt'] ?? '', $a['blockedAt'] ?? ''));

$active_tab = 'blocklist';
require __DIR__ . '/../includes/admin_shell_start.php';
?>
<div class="admin-content-head"><h2><?= h($bl['title']) ?></h2></div>
<p class="desc" style="margin-bottom:20px"><?= h($bl['intro']) ?></p>

<?php if (isset($_GET['blocked'])): ?><div class="toast-banner success"><span data-lucide="check-circle-2" style="width:16px;height:16px"></span><?= h($bl['blockedMsg']) ?></div><?php endif; ?>
<?php if (isset($_GET['unblocked'])): ?><div class="toast-banner success"><span data-lucide="check-circle-2" style="width:16px;height:16px"></span><?= h($bl['unblockedMsg']) ?></div><?php endif; ?>
<?php if ($notice): [$ntype, $nmsg] = explode(':', $notice, 2); ?>
  <div class="toast-banner <?= $ntype ?>"><span data-lucide="<?= $ntype === 'success' ? 'check-circle-2' : 'alert-triangle' ?>" style="width:16px;height:16px"></span><?= h($nmsg) ?></div>
<?php endif; ?>

<!-- Add to blocklist -->
<div class="card" style="padding:24px;margin-bottom:30px">
  <h3 style="font-size:16px;margin-bottom:16px"><?= h($bl['addTitle']) ?></h3>
  <form method="post">
    <input type="hidden" name="action" value="block">
    <div class="form-row-2">
      <div class="form-group">
        <label class="form-label"><?= h($bl['phoneLabel']) ?></label>
        <input class="form-input" name="phone" placeholder="01xxxxxxxxx" dir="ltr" maxlength="11" inputmode="numeric" required>
      </div>
      <div class="form-group">
        <label class="form-label"><?= h($bl['reasonLabel']) ?> <span class="opt-tag">(<?= h($T['booking']['optional']) ?>)</span></label>
        <input class="form-input" name="reason" placeholder="<?= h($bl['reasonPh']) ?>">
      </div>
    </div>
    <button type="submit" class="btn btn-danger-outline"><span data-lucide="shield-ban" style="width:16px;height:16px"></span><?= h($bl['blockBtn']) ?></button>
  </form>
</div>

<!-- List -->
<?php if (empty($blocked)): ?>
  <div class="card"><div class="empty-state"><span data-lucide="shield-check" style="width:44px;height:44px"></span><h3><?= h($bl['noneTitle']) ?></h3><p><?= h($bl['noneDesc']) ?></p></div></div>
<?php else: ?>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr>
        <th><?= h($bl['phoneLabel']) ?></th>
        <th><?= h($bl['reasonLabel']) ?></th>
        <th><?= h($bl['blockedOn']) ?></th>
        <th><?= h($T['admin']['bookings']['table']['actions']) ?></th>
      </tr></thead>
      <tbody>
        <?php foreach ($blocked as $b): ?>
          <tr>
            <td class="cell-strong" dir="ltr"><?= h($b['phone']) ?></td>
            <td class="cell-muted"><?= h($b['reason'] ?: '—') ?></td>
            <td class="cell-muted"><?= h(format_date_h($b['blockedAt'] ?? '', $lang)) ?></td>
            <td>
              <form method="post" data-confirm="<?= h($bl['unblockConfirm']) ?>" style="display:inline">
                <input type="hidden" name="action" value="unblock">
                <input type="hidden" name="phone" value="<?= h($b['phone']) ?>">
                <button type="submit" class="btn btn-secondary btn-sm"><span data-lucide="shield-check" style="width:13px;height:13px"></span><?= h($bl['unblockBtn']) ?></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/admin_shell_end.php'; ?>
