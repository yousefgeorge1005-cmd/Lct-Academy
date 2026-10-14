<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/translations.php';
$current_page = 'halls';

$halls    = load_halls();
$branches = load_branches();
$branchFilter = $_GET['branch'] ?? 'all';

$lang = current_lang();
$T = translations($lang);
$page_title = $T['halls']['title'];
$page_desc  = $T['halls']['sub'];

require __DIR__ . '/includes/header.php';
?>
<div class="page-hero-strip">
  <div class="container">
    <span class="eyebrow"><?= h($T['nav']['halls']) ?></span>
    <h1 class="section-title" style="margin-top:10px"><?= h($T['halls']['title']) ?></h1>
    <p class="section-sub"><?= h($T['halls']['sub']) ?></p>
  </div>
</div>

<!-- Branch filter tabs -->
<div class="container" style="margin-top:28px">
  <div class="filter-tabs" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:28px">
    <a href="halls.php" class="btn <?= $branchFilter === 'all' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
      <?= h($T['halls']['allBranches']) ?>
    </a>
    <?php foreach ($branches as $br): ?>
      <a href="halls.php?branch=<?= urlencode($br['id']) ?>"
         class="btn <?= $branchFilter === $br['id'] ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
        <span data-lucide="map-pin" style="width:13px;height:13px"></span>
        <?= h($br['name'][$lang]) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- Branch info card (when a branch is selected) -->
  <?php if ($branchFilter !== 'all'):
    $activeBranch = find_branch($branches, $branchFilter);
    if ($activeBranch): ?>
      <div class="card" style="padding:18px 22px;margin-bottom:26px;display:flex;gap:24px;flex-wrap:wrap;align-items:flex-start">
        <div style="display:flex;align-items:center;gap:10px;min-width:220px">
          <span data-lucide="building" style="width:20px;height:20px;color:var(--gold)"></span>
          <div>
            <div style="font-weight:700;font-size:15px"><?= h($activeBranch['name'][$lang]) ?></div>
            <div class="cell-muted" style="font-size:12.5px"><?= h($activeBranch['address'][$lang]) ?></div>
          </div>
        </div>
        <div style="display:flex;gap:20px;flex-wrap:wrap">
          <div style="font-size:13px"><span data-lucide="phone" style="width:13px;height:13px;vertical-align:-2px"></span> <span dir="ltr"><?= h($activeBranch['phone']) ?></span></div>
          <?php if (!empty($activeBranch['email'])): ?><div style="font-size:13px"><span data-lucide="mail" style="width:13px;height:13px;vertical-align:-2px"></span> <?= h($activeBranch['email']) ?></div><?php endif; ?>
          <div style="font-size:13px"><span data-lucide="clock" style="width:13px;height:13px;vertical-align:-2px"></span> <?= h($activeBranch['hours'][$lang]) ?></div>
        </div>
      </div>
  <?php endif; endif; ?>

  <div class="halls-grid">
    <?php
    $filteredHalls = ($branchFilter === 'all')
        ? $halls
        : array_values(array_filter($halls, fn($h) => ($h['branchId'] ?? '') === $branchFilter));

    if (empty($filteredHalls)):
    ?>
      <div class="card" style="grid-column:1/-1;padding:40px;text-align:center">
        <div class="empty-state">
          <span data-lucide="building-2" style="width:44px;height:44px"></span>
          <h3><?= h($T['halls']['noSlots']) ?></h3>
        </div>
      </div>
    <?php else: foreach ($filteredHalls as $hall):
      $br = find_branch($branches, $hall['branchId'] ?? '');
    ?>
      <div class="card hall-public-card">
        <div class="hall-public-card-head">
          <span class="hall-icon"><span data-lucide="building-2" style="width:22px;height:22px"></span></span>
          <div>
            <h3 style="font-size:18px"><?= h($hall['name'][$lang]) ?></h3>
            <div class="cell-muted" style="font-size:12.5px;margin-top:2px">
              <?= h($hall['type'][$lang]) ?> · <?= h($T['halls']['capacity']) ?>: <?= h($hall['capacity']) ?>
            </div>
            <?php if ($br): ?>
            <div style="font-size:12px;margin-top:4px;color:var(--gold);font-weight:600">
              <span data-lucide="map-pin" style="width:12px;height:12px;vertical-align:-1px"></span>
              <?= h($br['name'][$lang]) ?>
            </div>
            <?php endif; ?>
            <?php $am = $hall['amenities'] ?? []; if (!empty($am['smartboard']) || !empty($am['projector']) || !empty($am['ac'])): ?>
            <div style="display:flex;gap:6px;margin-top:6px;flex-wrap:wrap">
              <?php if (!empty($am['smartboard'])): ?><span class="badge badge-neutral" style="font-size:10.5px"><span data-lucide="presentation" style="width:10px;height:10px;vertical-align:-1px"></span> <?= h($T['halls']['amenitySmartboard']) ?></span><?php endif; ?>
              <?php if (!empty($am['projector'])): ?><span class="badge badge-neutral" style="font-size:10.5px"><span data-lucide="projector" style="width:10px;height:10px;vertical-align:-1px"></span> <?= h($T['halls']['amenityProjector']) ?></span><?php endif; ?>
              <?php if (!empty($am['ac'])): ?><span class="badge badge-neutral" style="font-size:10.5px"><span data-lucide="snowflake" style="width:10px;height:10px;vertical-align:-1px"></span> <?= h($T['halls']['amenityAc']) ?></span><?php endif; ?>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <div style="margin-top:14px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
          <?php if (!empty($hall['pricePerHour'])): ?>
          <div style="font-size:13.5px;font-weight:700;color:var(--text)">
            <span data-lucide="tag" style="width:14px;height:14px;vertical-align:-2px;color:var(--gold)"></span>
            <?= h($hall['pricePerHour']) ?> <?= h($T['halls']['egpPerHour']) ?>
          </div>
          <?php endif; ?>
          <a href="<?= BASE_URL ?>/book-hall.php?hall=<?= urlencode($hall['id']) ?>" class="btn btn-primary btn-sm">
            <span data-lucide="calendar-plus" style="width:15px;height:15px"></span>
            <?= h($T['halls']['bookNow']) ?>
          </a>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
