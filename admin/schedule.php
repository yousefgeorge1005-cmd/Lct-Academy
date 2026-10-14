<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/translations.php';
require_admin();
$lang = current_lang(); $theme = current_theme(); $T = translations($lang); $dir = $T['dir'];
$sc = $T['admin']['schedule'];

$halls    = load_halls();
$branches = load_branches();
$branchById = []; foreach ($branches as $b) $branchById[$b['id']] = $b;
$branchFilter = $_GET['branch'] ?? 'all';

// ---- Resolve the week being viewed (Saturday → Friday, matching the local work week) ----
$refDate = $_GET['ref'] ?? date('Y-m-d');
$refTs   = strtotime($refDate) ?: time();
$dow     = (int)date('w', $refTs); // 0=Sun..6=Sat
$daysSinceSaturday = ($dow + 1) % 7;
$weekStartTs = strtotime("-$daysSinceSaturday days", $refTs);

$dayNamesAr = ['السبت', 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'];
$dayNamesEn = ['Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
$weekDays = [];
for ($i = 0; $i < 7; $i++) {
    $ts = strtotime("+$i days", $weekStartTs);
    $weekDays[] = [
        'date'  => date('Y-m-d', $ts),
        'label' => $lang === 'ar' ? $dayNamesAr[$i] : $dayNamesEn[$i],
        'num'   => date('j', $ts),
        'isToday' => date('Y-m-d', $ts) === date('Y-m-d'),
    ];
}
$prevWeekRef = date('Y-m-d', strtotime('-7 days', $weekStartTs));
$nextWeekRef = date('Y-m-d', strtotime('+7 days', $weekStartTs));

// ---- Timeline range: 8:00 to 22:00 ----
$dayStartMin = 8 * 60;
$dayEndMin   = 22 * 60;
$rangeMin    = $dayEndMin - $dayStartMin;

function _sched_minutes($t) { [$h, $m] = explode(':', $t); return (int)$h * 60 + (int)$m; }

// Index all bookings by id, so slot blocks can show who booked them without extra file reads per slot.
$bookingsById = [];
foreach (all_bookings() as $b) $bookingsById[$b['id']] = $b;

$filteredHalls = ($branchFilter === 'all') ? $halls : array_values(array_filter($halls, fn($h) => ($h['branchId'] ?? '') === $branchFilter));

$active_tab = 'schedule';
require __DIR__ . '/../includes/admin_shell_start.php';
?>
<div class="admin-content-head"><h2><?= h($sc['title']) ?></h2></div>
<p class="desc" style="margin-bottom:18px"><?= h($sc['intro']) ?></p>

<!-- Week nav + branch filter -->
<div class="table-toolbar" style="align-items:center">
  <div style="display:flex;align-items:center;gap:8px">
    <a href="schedule.php?ref=<?= $prevWeekRef ?>&branch=<?= h($branchFilter) ?>" class="btn btn-secondary btn-sm" aria-label="prev">
      <span data-lucide="<?= $dir === 'rtl' ? 'chevron-right' : 'chevron-left' ?>" style="width:15px;height:15px"></span>
    </a>
    <div style="font-weight:700;font-size:14.5px;min-width:170px;text-align:center">
      <?= h(format_date_h($weekDays[0]['date'], $lang)) ?> — <?= h(format_date_h($weekDays[6]['date'], $lang)) ?>
    </div>
    <a href="schedule.php?ref=<?= $nextWeekRef ?>&branch=<?= h($branchFilter) ?>" class="btn btn-secondary btn-sm" aria-label="next">
      <span data-lucide="<?= $dir === 'rtl' ? 'chevron-left' : 'chevron-right' ?>" style="width:15px;height:15px"></span>
    </a>
    <a href="schedule.php?branch=<?= h($branchFilter) ?>" class="btn btn-ghost btn-sm"><?= h($sc['today']) ?></a>
  </div>
  <div class="table-filters">
    <select class="form-select" style="width:auto" onchange="location.href='schedule.php?ref=<?= h($refDate) ?>&branch='+this.value">
      <option value="all" <?= $branchFilter === 'all' ? 'selected' : '' ?>><?= h($sc['allBranches']) ?></option>
      <?php foreach ($branches as $br): ?>
        <option value="<?= h($br['id']) ?>" <?= $branchFilter === $br['id'] ? 'selected' : '' ?>><?= h($br['name'][$lang]) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
</div>

<!-- Legend -->
<div style="display:flex;gap:18px;margin:14px 2px 20px;flex-wrap:wrap;font-size:12.5px">
  <span style="display:flex;align-items:center;gap:6px"><span style="width:12px;height:12px;border-radius:3px;background:var(--warn);display:inline-block"></span><?= h($sc['legendPending']) ?></span>
  <span style="display:flex;align-items:center;gap:6px"><span style="width:12px;height:12px;border-radius:3px;background:var(--success);display:inline-block"></span><?= h($sc['legendConfirmed']) ?></span>
</div>

<?php if (empty($filteredHalls)): ?>
  <div class="card"><div class="empty-state"><span data-lucide="building-2" style="width:44px;height:44px"></span><h3><?= h($sc['noHalls']) ?></h3></div></div>
<?php else: foreach ($filteredHalls as $hall):
  $br = $branchById[$hall['branchId'] ?? ''] ?? null;
?>
  <div class="card" style="padding:20px 22px;margin-bottom:22px">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:16px">
      <div>
        <h3 style="font-size:16px"><?= h($hall['name'][$lang]) ?></h3>
        <?php if ($br): ?><div class="cell-muted" style="font-size:12px;margin-top:2px"><span data-lucide="map-pin" style="width:11px;height:11px;vertical-align:-1px"></span> <?= h($br['name'][$lang]) ?></div><?php endif; ?>
      </div>
    </div>

    <!-- Hour ruler -->
    <div style="display:flex;margin-bottom:4px">
      <div style="width:70px;flex:none"></div>
      <div style="flex:1;position:relative;height:16px">
        <?php for ($h = 8; $h <= 22; $h += 2): $pct = (($h * 60 - $dayStartMin) / $rangeMin) * 100; ?>
          <span style="position:absolute;<?= $dir === 'rtl' ? 'right' : 'left' ?>:<?= $pct ?>%;font-size:10.5px;color:var(--text-soft);transform:translateX(<?= $dir === 'rtl' ? '50%' : '-50%' ?>)"><?= $h ?>:00</span>
        <?php endfor; ?>
      </div>
    </div>

    <?php foreach ($weekDays as $day):
      $daySlots = array_values(array_filter($hall['slots'] ?? [], fn($s) => $s['date'] === $day['date'] && in_array($s['status'], ['pending', 'booked'])));
    ?>
      <div style="display:flex;align-items:center;margin-bottom:6px">
        <div style="width:70px;flex:none;font-size:12px;<?= $day['isToday'] ? 'color:var(--gold);font-weight:700' : 'color:var(--text-soft)' ?>">
          <?= h($day['label']) ?> <?= h($day['num']) ?>
        </div>
        <div style="flex:1;position:relative;height:26px;background:var(--surface-2);border-radius:6px;overflow:hidden">
          <?php foreach ($daySlots as $slot):
            $sMin = max($dayStartMin, _sched_minutes($slot['start']));
            $eMin = min($dayEndMin, _sched_minutes($slot['end']));
            if ($eMin <= $sMin) continue;
            $left  = (($sMin - $dayStartMin) / $rangeMin) * 100;
            $width = (($eMin - $sMin) / $rangeMin) * 100;
            $color = $slot['status'] === 'booked' ? 'var(--success)' : 'var(--warn)';
            $owner = $bookingsById[$slot['bookingId'] ?? ''] ?? null;
            $label = $owner ? $owner['fullName'] : ($slot['note'][$lang] ?? '');
            $timeLabel = h($slot['start']) . '–' . h($slot['end']) . ($label ? ' · ' . h($label) : '');
          ?>
            <a href="<?= !empty($slot['bookingId']) ? 'bookings.php?view=' . urlencode($slot['bookingId']) : '#' ?>"
               title="<?= $timeLabel ?>"
               style="position:absolute;top:2px;bottom:2px;<?= $dir === 'rtl' ? 'right' : 'left' ?>:<?= $left ?>%;width:<?= max($width, 2) ?>%;background:<?= $color ?>;border-radius:4px;display:flex;align-items:center;padding-inline:6px;overflow:hidden;white-space:nowrap;text-decoration:none">
              <span style="font-size:10px;color:#fff;font-weight:600;overflow:hidden;text-overflow:ellipsis"><?= h($label ?: $slot['start']) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endforeach; endif; ?>
<?php require __DIR__ . '/../includes/admin_shell_end.php'; ?>
