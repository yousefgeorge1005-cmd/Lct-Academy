<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/translations.php';
$current_page = '';
$ref = $_GET['ref'] ?? '';
$phone = $_GET['phone'] ?? '';
require __DIR__ . '/includes/header.php';
?>
<div class="container section">
  <div class="success-wrap">
    <div class="stamp"><span data-lucide="shield-check"></span></div>
    <h2><?= h($T['booking']['successTitle']) ?></h2>
    <p style="color:var(--text-soft);margin-top:10px;font-size:15px"><?= h($T['booking']['successDesc']) ?></p>
    <?php if ($ref): ?><div class="ref-box"><span data-lucide="ticket" style="width:18px;height:18px"></span><?= h($ref) ?></div><?php endif; ?>
    <div style="display:flex;gap:12px;justify-content:center;margin-top:30px;flex-wrap:wrap">
      <a href="<?= BASE_URL ?>/portal.php?phone=<?= urlencode($phone) ?>" class="btn btn-primary"><?= h($T['booking']['goToMyPage']) ?></a>
      <a href="<?= BASE_URL ?>/index.php" class="btn btn-secondary"><?= h($T['booking']['backHome']) ?></a>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
