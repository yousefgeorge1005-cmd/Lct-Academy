<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/translations.php';
require_admin();
$lang = current_lang(); $theme = current_theme(); $T = translations($lang); $dir = $T['dir'];
$f = $T['admin']['halls'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_hall') {
        $hallId = trim($_POST['hall_id'] ?? '');
        $data = [
            'name'         => ['ar' => trim($_POST['name_ar'] ?? ''), 'en' => trim($_POST['name_en'] ?? '')],
            'type'         => ['ar' => trim($_POST['type_ar'] ?? ''), 'en' => trim($_POST['type_en'] ?? '')],
            'capacity'     => (int)($_POST['capacity'] ?? 0),
            'pricePerHour' => (int)($_POST['price_per_hour'] ?? 0),
            'branchId'     => trim($_POST['branch_id'] ?? ''),
            'amenities'    => [
                'smartboard' => !empty($_POST['amenity_smartboard']),
                'projector'  => !empty($_POST['amenity_projector']),
                'ac'         => !empty($_POST['amenity_ac']),
            ],
        ];
        update_json_file(HALLS_FILE, function ($all) use ($hallId, $data) {
            if ($hallId !== '') {
                foreach ($all as &$h) { if ($h['id'] === $hallId) { $h = array_merge($h, $data); } }
                unset($h);
                return $all;
            }
            $data['id'] = gen_uid('hall');
            $data['slots'] = [];
            $all[] = $data;
            return $all;
        });
        header('Location: halls.php?saved=1'); exit;
    }

    if ($action === 'delete_hall') {
        $id = $_POST['id'] ?? '';
        update_json_file(HALLS_FILE, function ($all) use ($id) {
            return array_values(array_filter($all, fn($h) => $h['id'] !== $id));
        });
        header('Location: halls.php?deleted=1'); exit;
    }

    // Manual block: admin blocks a hall for maintenance/private use (no customer booking attached)
    if ($action === 'save_slot') {
        $hallId = $_POST['hall_id'] ?? '';
        $slotId = trim($_POST['slot_id'] ?? '');
        $slotData = [
            'date' => trim($_POST['date'] ?? ''), 'start' => trim($_POST['start'] ?? ''), 'end' => trim($_POST['end'] ?? ''),
            'status' => 'booked',
            'note' => ['ar' => trim($_POST['note'] ?? ''), 'en' => trim($_POST['note'] ?? '')],
        ];
        update_json_file(HALLS_FILE, function ($all) use ($hallId, $slotId, $slotData) {
            foreach ($all as &$h) {
                if ($h['id'] !== $hallId) continue;
                if ($slotId !== '') {
                    foreach ($h['slots'] as &$s) { if ($s['id'] === $slotId) { $s = array_merge($s, $slotData); } }
                    unset($s);
                } else {
                    $slotData['id'] = gen_uid('slot');
                    $h['slots'][] = $slotData;
                }
            }
            unset($h);
            return $all;
        });
        header('Location: halls.php?saved=1'); exit;
    }

    if ($action === 'delete_slot') {
        $hallId = $_POST['hall_id'] ?? ''; $slotId = $_POST['slot_id'] ?? '';
        update_json_file(HALLS_FILE, function ($all) use ($hallId, $slotId) {
            foreach ($all as &$h) {
                if ($h['id'] === $hallId) { $h['slots'] = array_values(array_filter($h['slots'], fn($s) => $s['id'] !== $slotId)); }
            }
            unset($h);
            return $all;
        });
        header('Location: halls.php?deleted=1'); exit;
    }
}

$halls    = load_halls();
$branches = load_branches();
$branchById = []; foreach ($branches as $b) $branchById[$b['id']] = $b;

$editHall = isset($_GET['edit']) ? find_hall($halls, $_GET['edit']) : null;
$editSlotHallId = $_GET['hall'] ?? '';
$editSlot = isset($_GET['edit_slot']) ? find_slot(find_hall($halls, $editSlotHallId), $_GET['edit_slot']) : null;

$active_tab = 'halls';
require __DIR__ . '/../includes/admin_shell_start.php';
$statusLabels = ['available' => $f['available'], 'pending' => ($lang === 'ar' ? 'قيد المراجعة' : 'Pending'), 'booked' => $f['booked']];
$statusBadge  = ['available' => 'badge-confirmed', 'pending' => 'badge-pending', 'booked' => 'badge-rejected'];
?>
<div class="admin-content-head"><h2><?= h($f['title']) ?></h2></div>
<?php if (isset($_GET['saved'])): ?><div class="toast-banner success"><span data-lucide="check-circle-2" style="width:16px;height:16px"></span><?= h($T['common']['saved']) ?></div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="toast-banner success"><span data-lucide="check-circle-2" style="width:16px;height:16px"></span><?= $lang === 'ar' ? 'تم الحذف' : 'Deleted' ?></div><?php endif; ?>

<!-- Add / Edit hall -->
<div class="card" style="padding:24px;margin-bottom:30px">
  <h3 style="font-size:16px;margin-bottom:16px"><?= $editHall ? h($f['editHall']) : h($f['addHall']) ?></h3>
  <form method="post">
    <input type="hidden" name="action" value="save_hall">
    <input type="hidden" name="hall_id" value="<?= h($editHall['id'] ?? '') ?>">
    <div class="form-row-2">
      <div class="form-group"><label class="form-label"><?= $lang === 'ar' ? 'اسم القاعة (عربي)' : 'Hall name (Arabic)' ?></label><input class="form-input" name="name_ar" value="<?= h($editHall['name']['ar'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label"><?= $lang === 'ar' ? 'اسم القاعة (إنجليزي)' : 'Hall name (English)' ?></label><input class="form-input" name="name_en" value="<?= h($editHall['name']['en'] ?? '') ?>" dir="ltr"></div>
    </div>
    <div class="form-row-2">
      <div class="form-group"><label class="form-label"><?= $lang === 'ar' ? 'النوع (عربي)' : 'Type (Arabic)' ?></label><input class="form-input" name="type_ar" value="<?= h($editHall['type']['ar'] ?? '') ?>"></div>
      <div class="form-group"><label class="form-label"><?= $lang === 'ar' ? 'النوع (إنجليزي)' : 'Type (English)' ?></label><input class="form-input" name="type_en" value="<?= h($editHall['type']['en'] ?? '') ?>" dir="ltr"></div>
    </div>
    <div class="form-row-2">
      <div class="form-group"><label class="form-label"><?= h($f['capacity']) ?></label><input class="form-input" type="number" name="capacity" value="<?= h($editHall['capacity'] ?? 16) ?>" dir="ltr"></div>
      <div class="form-group"><label class="form-label"><?= h($f['pricePerHour']) ?></label><input class="form-input" type="number" name="price_per_hour" value="<?= h($editHall['pricePerHour'] ?? 0) ?>" dir="ltr"></div>
    </div>
    <div class="form-group">
      <label class="form-label"><?= h($f['selectBranch']) ?></label>
      <select class="form-select" name="branch_id">
        <option value=""><?= h($T['common']['all']) ?></option>
        <?php foreach ($branches as $br): ?>
          <option value="<?= h($br['id']) ?>" <?= ($editHall['branchId'] ?? '') === $br['id'] ? 'selected' : '' ?>><?= h($br['name'][$lang]) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label"><?= h($f['amenitiesLabel']) ?></label>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <label class="agree-row" style="flex:1;min-width:150px">
          <input type="checkbox" name="amenity_smartboard" value="1" <?= !empty($editHall['amenities']['smartboard']) ? 'checked' : '' ?>>
          <span><span data-lucide="presentation" style="width:14px;height:14px;vertical-align:-2px"></span> <?= h($f['amenitySmartboard']) ?></span>
        </label>
        <label class="agree-row" style="flex:1;min-width:150px">
          <input type="checkbox" name="amenity_projector" value="1" <?= !empty($editHall['amenities']['projector']) ? 'checked' : '' ?>>
          <span><span data-lucide="projector" style="width:14px;height:14px;vertical-align:-2px"></span> <?= h($f['amenityProjector']) ?></span>
        </label>
        <label class="agree-row" style="flex:1;min-width:150px">
          <input type="checkbox" name="amenity_ac" value="1" <?= !empty($editHall['amenities']['ac']) ? 'checked' : '' ?>>
          <span><span data-lucide="snowflake" style="width:14px;height:14px;vertical-align:-2px"></span> <?= h($f['amenityAc']) ?></span>
        </label>
      </div>
    </div>
    <div style="display:flex;gap:10px">
      <button type="submit" class="btn btn-primary"><span data-lucide="save" style="width:16px;height:16px"></span><?= h($T['common']['save']) ?></button>
      <?php if ($editHall): ?><a href="halls.php" class="btn btn-ghost"><?= h($T['common']['cancel']) ?></a><?php endif; ?>
    </div>
  </form>
</div>

<?php foreach ($halls as $hall):
  $br = $branchById[$hall['branchId'] ?? ''] ?? null;
  // Only real customer bookings (have a bookingId) are shown read-only here;
  // full actions (confirm/reject/cancel/edit time) live on the Bookings page.
  $realSlots   = array_values(array_filter($hall['slots'], fn($s) => !empty($s['bookingId'])));
  $blockedSlots = array_values(array_filter($hall['slots'], fn($s) => empty($s['bookingId'])));
?>
  <div class="card hall-card">
    <div class="hall-card-head">
      <div class="hall-card-title">
        <span class="hall-icon"><span data-lucide="building-2" style="width:20px;height:20px"></span></span>
        <div>
          <h3 style="font-size:17px"><?= h($hall['name'][$lang]) ?></h3>
          <div class="cell-muted" style="font-size:12.5px;margin-top:2px">
            <?= h($hall['type'][$lang]) ?> · <?= h($f['capacity']) ?>: <?= h($hall['capacity']) ?>
            <?php if (!empty($hall['pricePerHour'])): ?> · <?= h($hall['pricePerHour']) ?> <?= h($T['halls']['egpPerHour']) ?><?php endif; ?>
          </div>
          <?php if ($br): ?><div style="font-size:12px;margin-top:3px;color:var(--gold);font-weight:600"><span data-lucide="map-pin" style="width:11px;height:11px;vertical-align:-1px"></span> <?= h($br['name'][$lang]) ?></div><?php endif; ?>
          <?php $am = $hall['amenities'] ?? []; if (!empty($am['smartboard']) || !empty($am['projector']) || !empty($am['ac'])): ?>
          <div style="display:flex;gap:8px;margin-top:6px;flex-wrap:wrap">
            <?php if (!empty($am['smartboard'])): ?><span class="badge badge-neutral" style="font-size:11px"><span data-lucide="presentation" style="width:11px;height:11px;vertical-align:-1px"></span> <?= h($f['amenitySmartboard']) ?></span><?php endif; ?>
            <?php if (!empty($am['projector'])): ?><span class="badge badge-neutral" style="font-size:11px"><span data-lucide="projector" style="width:11px;height:11px;vertical-align:-1px"></span> <?= h($f['amenityProjector']) ?></span><?php endif; ?>
            <?php if (!empty($am['ac'])): ?><span class="badge badge-neutral" style="font-size:11px"><span data-lucide="snowflake" style="width:11px;height:11px;vertical-align:-1px"></span> <?= h($f['amenityAc']) ?></span><?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="row-actions">
        <a href="halls.php?edit=<?= urlencode($hall['id']) ?>" class="icon-btn" aria-label="edit"><span data-lucide="pencil" style="width:15px;height:15px"></span></a>
        <form method="post" data-confirm="<?= h($f['deleteHallConfirm']) ?>" style="display:inline">
          <input type="hidden" name="action" value="delete_hall"><input type="hidden" name="id" value="<?= h($hall['id']) ?>">
          <button type="submit" class="icon-btn" aria-label="delete"><span data-lucide="trash-2" style="width:15px;height:15px"></span></button>
        </form>
      </div>
    </div>

    <!-- Real customer bookings for this hall — read-only here, manage from Bookings page -->
    <?php if (!empty($realSlots)): ?>
      <div style="font-size:12.5px;font-weight:700;color:var(--text-soft);margin-top:16px;margin-bottom:8px">
        <?= $lang === 'ar' ? 'حجوزات العملاء' : 'Customer bookings' ?>
      </div>
      <div class="slot-list">
        <?php $sorted = $realSlots; usort($sorted, fn($a, $b) => strcmp($a['date'], $b['date'])); ?>
        <?php foreach ($sorted as $slot): ?>
          <div class="slot-row">
            <div class="slot-info">
              <span class="slot-date"><?= h(format_date_h($slot['date'], $lang)) ?></span>
              <span class="slot-time" dir="ltr"><?= h($slot['start']) ?> – <?= h($slot['end']) ?></span>
              <span class="badge <?= $statusBadge[$slot['status']] ?? 'badge-neutral' ?>"><?= h($statusLabels[$slot['status']] ?? $slot['status']) ?></span>
            </div>
            <a href="bookings.php?view=<?= urlencode($slot['bookingId']) ?>" class="btn btn-secondary btn-sm">
              <span data-lucide="eye" style="width:13px;height:13px"></span><?= $lang === 'ar' ? 'إدارة' : 'Manage' ?>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Admin-blocked times (maintenance etc.) -->
    <div style="font-size:12.5px;font-weight:700;color:var(--text-soft);margin-top:18px;margin-bottom:8px">
      <?= $lang === 'ar' ? 'أوقات محجوبة يدويًا (صيانة، إلخ)' : 'Manually blocked times (maintenance, etc.)' ?>
    </div>
    <?php if (empty($blockedSlots)): ?>
      <p class="cell-muted" style="font-size:13px"><?= h($f['noSlots']) ?></p>
    <?php else: ?>
      <div class="slot-list">
        <?php $sortedB = $blockedSlots; usort($sortedB, fn($a, $b) => strcmp($a['date'], $b['date'])); ?>
        <?php foreach ($sortedB as $slot): ?>
          <div class="slot-row">
            <div class="slot-info">
              <span class="slot-date"><?= h(format_date_h($slot['date'], $lang)) ?></span>
              <span class="slot-time" dir="ltr"><?= h($slot['start']) ?> – <?= h($slot['end']) ?></span>
              <?php if (!empty($slot['note'][$lang])): ?><span class="cell-muted"><?= h($slot['note'][$lang]) ?></span><?php endif; ?>
            </div>
            <div class="row-actions">
              <a href="halls.php?hall=<?= urlencode($hall['id']) ?>&edit_slot=<?= urlencode($slot['id']) ?>" class="icon-btn" aria-label="edit"><span data-lucide="pencil" style="width:14px;height:14px"></span></a>
              <form method="post" data-confirm="<?= h($f['deleteSlotConfirm']) ?>" style="display:inline">
                <input type="hidden" name="action" value="delete_slot"><input type="hidden" name="hall_id" value="<?= h($hall['id']) ?>"><input type="hidden" name="slot_id" value="<?= h($slot['id']) ?>">
                <button type="submit" class="icon-btn" aria-label="delete"><span data-lucide="trash-2" style="width:14px;height:14px"></span></button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php $isEditingThisHall = $editSlot && $editSlotHallId === $hall['id'] && empty($editSlot['bookingId']); ?>
    <form method="post" style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border)">
      <input type="hidden" name="action" value="save_slot">
      <input type="hidden" name="hall_id" value="<?= h($hall['id']) ?>">
      <input type="hidden" name="slot_id" value="<?= $isEditingThisHall ? h($editSlot['id']) : '' ?>">
      <div style="font-size:13px;font-weight:700;margin-bottom:10px;color:var(--text-soft)"><?= $isEditingThisHall ? h($f['editSlot']) : h($f['addSlot']) ?></div>
      <div class="form-row-2">
        <div class="form-group"><label class="form-label"><?= $lang === 'ar' ? 'التاريخ' : 'Date' ?></label><input class="form-input" type="date" name="date" value="<?= h($isEditingThisHall ? $editSlot['date'] : '') ?>" dir="ltr"></div>
        <div></div>
      </div>
      <div class="form-row-2">
        <div class="form-group"><label class="form-label"><?= $lang === 'ar' ? 'من الساعة' : 'Start time' ?></label><input class="form-input" type="time" name="start" value="<?= h($isEditingThisHall ? $editSlot['start'] : '17:00') ?>" dir="ltr"></div>
        <div class="form-group"><label class="form-label"><?= $lang === 'ar' ? 'إلى الساعة' : 'End time' ?></label><input class="form-input" type="time" name="end" value="<?= h($isEditingThisHall ? $editSlot['end'] : '19:00') ?>" dir="ltr"></div>
      </div>
      <div class="form-group"><label class="form-label"><?= $lang === 'ar' ? 'سبب الحجب (مثال: صيانة)' : 'Block reason (e.g. maintenance)' ?></label><input class="form-input" name="note" value="<?= h($isEditingThisHall ? ($editSlot['note'][$lang] ?? '') : '') ?>"></div>
      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-secondary btn-sm"><span data-lucide="plus" style="width:14px;height:14px"></span><?= $isEditingThisHall ? h($T['common']['save']) : h($f['addSlot']) ?></button>
        <?php if ($isEditingThisHall): ?><a href="halls.php" class="btn btn-ghost btn-sm"><?= h($T['common']['cancel']) ?></a><?php endif; ?>
      </div>
    </form>
  </div>
<?php endforeach; ?>
<?php require __DIR__ . '/../includes/admin_shell_end.php'; ?>
