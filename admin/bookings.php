<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/translations.php';
require_admin();
$lang = current_lang(); $theme = current_theme(); $T = translations($lang); $dir = $T['dir'];
$bt = $T['admin']['bookings'];

function get_booking_full($bookingId) {
    $phone = find_booking_owner($bookingId);
    if (!$phone) return [null, null];
    $customer = load_customer($phone);
    foreach (($customer['bookings'] ?? []) as $b) {
        if ($b['id'] === $bookingId) return [$b, $phone];
    }
    return [null, null];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = $_POST['booking_id'] ?? '';
    $action    = $_POST['action'] ?? '';
    [$booking, $phone] = get_booking_full($bookingId);

    /* ---- Cancel booking ---- */
    if ($action === 'cancel') {
        cancel_booking($bookingId);
        header('Location: bookings.php?updated=1'); exit;
    }

    /* ---- Permanently delete booking (manager only) ---- */
    if ($action === 'delete_booking') {
        require_manager();
        delete_booking_permanently($bookingId);
        header('Location: bookings.php?deleted=1'); exit;
    }

    /* ---- Block this customer's number (manager only) ---- */
    if ($action === 'block_number') {
        require_manager();
        if ($phone) block_phone_number($phone, $bt['blockedFromBooking']);
        header('Location: bookings.php?view=' . urlencode($bookingId) . '&blocked=1'); exit;
    }

    /* ---- Edit hall booking time ---- */
    if ($action === 'edit_time') {
        $newDate  = trim($_POST['new_date'] ?? '');
        $newStart = trim($_POST['new_start'] ?? '');
        $newEnd   = trim($_POST['new_end'] ?? '');
        if ($booking && $newDate && $newStart && $newEnd && $newEnd > $newStart) {
            // Don't let an edit land on top of a slot that's already confirmed for someone else.
            if (hall_slot_conflicts($booking['hallId'] ?? '', $newDate, $newStart, $newEnd, $booking['slotId'] ?? null)) {
                header('Location: bookings.php?view=' . urlencode($bookingId) . '&error=hall_conflict'); exit;
            }
            update_hall_booking_time($bookingId, $newDate, $newStart, $newEnd);
        }
        header('Location: bookings.php?view=' . urlencode($bookingId) . '&updated=1'); exit;
    }

    /* ---- Update payment & installment details ---- */
    if ($action === 'update_payment') {
        $amountPaid   = (float)($_POST['amount_paid'] ?? 0);
        $dueDate      = trim($_POST['payment_due_date'] ?? '');
        $paymentNotes = trim($_POST['payment_notes'] ?? '');
        if ($bookingId) {
            update_booking_payment($bookingId, $amountPaid, $dueDate, $paymentNotes);
        }
        header('Location: bookings.php?view=' . urlencode($bookingId) . '&payment_updated=1'); exit;
    }

    /* ---- Status change ---- */
    $newStatus = $_POST['new_status'] ?? '';
    $reason    = trim($_POST['reason'] ?? '');

    if ($booking && in_array($newStatus, ['confirmed', 'rejected', 'completed'], true)) {
        $isHall = ($booking['type'] ?? 'course') === 'hall';

        // Several customers can hold a PENDING request for the same overlapping hall
        // time at once. The moment one gets confirmed, that slot is locked — block
        // confirming a second one on top of it (the admin then rejects the rest, or
        // edits their time first, so those customers pick a different appointment).
        if ($isHall && $newStatus === 'confirmed'
            && hall_slot_conflicts($booking['hallId'] ?? '', $booking['slotDate'] ?? '', $booking['slotStart'] ?? '', $booking['slotEnd'] ?? '', $booking['slotId'] ?? null)) {
            header('Location: bookings.php?view=' . urlencode($bookingId) . '&error=hall_conflict'); exit;
        }

        // A course seat is only ever counted once it's CONFIRMED (not at submission),
        // so guard against confirming past capacity.
        if (!$isHall && $newStatus === 'confirmed') {
            $courseObj = find_course(load_courses(), $booking['courseId'] ?? '');
            if ($courseObj && course_seats_left($courseObj) <= 0) {
                header('Location: bookings.php?view=' . urlencode($bookingId) . '&error=course_full'); exit;
            }
        }

        update_customer_booking_status($phone, $bookingId, $newStatus, $reason);

        if (!$isHall) {
            // Seats increment only when a booking becomes confirmed. Rejecting a
            // pending request needs no seat change (pending never counted); if a
            // previously-confirmed booking needs undone, use Cancel instead, which
            // already refunds the seat.
            if ($newStatus === 'confirmed') {
                update_json_file(COURSES_FILE, function ($all) use ($booking) {
                    foreach ($all as &$c) { if ($c['id'] === $booking['courseId']) { $c['seatsBooked'] = (int)$c['seatsBooked'] + 1; } }
                    unset($c); return $all;
                });
            }
        } else {
            update_json_file(HALLS_FILE, function ($all) use ($booking, $newStatus) {
                foreach ($all as &$h) {
                    if ($h['id'] !== ($booking['hallId'] ?? '')) continue;
                    foreach ($h['slots'] as &$s) {
                        if ($s['id'] === ($booking['slotId'] ?? '')) {
                            $s['status'] = $newStatus === 'confirmed' ? 'booked' : 'available';
                            if ($newStatus === 'rejected') $s['note'] = ['ar' => '', 'en' => ''];
                        }
                    } unset($s);
                } unset($h); return $all;
            });
        }
    }
    header('Location: bookings.php?updated=1'); exit;
}

$courses = load_courses(); $courseById = [];
foreach ($courses as $c) $courseById[$c['id']] = $c;
$halls = load_halls(); $hallById = [];
foreach ($halls as $h) $hallById[$h['id']] = $h;
$branches = load_branches(); $branchById = [];
foreach ($branches as $b) $branchById[$b['id']] = $b;

$allBookings = all_bookings();
$statusFilter = $_GET['status'] ?? 'all';
$typeFilter   = $_GET['type'] ?? 'all';
$search       = trim($_GET['q'] ?? '');
$filtered = array_filter($allBookings, function ($b) use ($statusFilter, $typeFilter, $search) {
    if ($statusFilter !== 'all' && $b['status'] !== $statusFilter) return false;
    if ($typeFilter !== 'all' && ($b['type'] ?? 'course') !== $typeFilter) return false;
    if ($search !== '') {
        $q = mb_strtolower($search);
        if (mb_strpos(mb_strtolower($b['fullName']), $q) === false && strpos($b['phone'], $search) === false) return false;
    }
    return true;
});

$viewBooking = null; $viewPhone = null;
if (isset($_GET['view'])) { [$viewBooking, $viewPhone] = get_booking_full($_GET['view']); }

$active_tab = 'bookings';
require __DIR__ . '/../includes/admin_shell_start.php';
?>
<div class="admin-content-head" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
  <h2><?= h($bt['title']) ?></h2>
  <div style="display:flex; gap:8px;">
    <a href="export_excel.php?type=bookings" class="btn btn-outline btn-sm">📊 <?= $lang === 'ar' ? 'تصدير إكسل' : 'Export Excel' ?></a>
    <a href="export_csv.php?type=bookings" class="btn btn-outline btn-sm">📄 CSV</a>
    <a href="export_pdf.php?type=bookings" target="_blank" class="btn btn-primary btn-sm">🖨️ PDF</a>
  </div>
</div>
<?php if (isset($_GET['updated'])): ?><div class="toast-banner success"><span data-lucide="check-circle-2" style="width:16px;height:16px"></span><?= h($T['common']['saved']) ?></div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="toast-banner success"><span data-lucide="check-circle-2" style="width:16px;height:16px"></span><?= h($bt['deletedMsg']) ?></div><?php endif; ?>
<?php if (isset($_GET['blocked'])): ?><div class="toast-banner success"><span data-lucide="check-circle-2" style="width:16px;height:16px"></span><?= h($bt['blockedMsg']) ?></div><?php endif; ?>
<?php if (($_GET['error'] ?? '') === 'hall_conflict'): ?><div class="toast-banner error"><span data-lucide="alert-triangle" style="width:16px;height:16px"></span><?= h($bt['hallConflictError']) ?></div><?php endif; ?>
<?php if (($_GET['error'] ?? '') === 'course_full'): ?><div class="toast-banner error"><span data-lucide="alert-triangle" style="width:16px;height:16px"></span><?= h($bt['courseFullError']) ?></div><?php endif; ?>

<?php if ($viewBooking):
  $isHall  = ($viewBooking['type'] ?? 'course') === 'hall';
  $course  = $courseById[$viewBooking['courseId'] ?? ''] ?? null;
  $hall    = $hallById[$viewBooking['hallId'] ?? ''] ?? null;
  $branch  = $branchById[$viewBooking['branchId'] ?? ''] ?? null;
  $isCloudinary = !empty($viewBooking['idPhotoCloudinary']);
?>
  <div class="card" style="padding:26px;margin-bottom:30px">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:18px;flex-wrap:wrap;gap:10px">
      <div>
        <div class="cell-muted"><?= $lang === 'ar' ? 'رقم المرجع' : 'Reference' ?></div>
        <div style="font-family:var(--font-display);font-size:20px;font-weight:700"><?= h($viewBooking['ref']) ?></div>
      </div>
      <span class="badge badge-<?= h($viewBooking['status']) ?>"><?= h($T['status'][$viewBooking['status']] ?? '') ?></span>
    </div>

    <div class="review-block">
      <div class="review-row"><span><?= $isHall ? h($bt['typeHall']) : h($bt['typeCourse']) ?></span>
        <span><?= $isHall ? h($hall['name'][$lang] ?? '') : h($course['name'][$lang] ?? '') ?></span></div>
      <?php if ($branch): ?><div class="review-row"><span><?= h($bt['branch']) ?></span><span><?= h($branch['name'][$lang]) ?> — <?= h($branch['address'][$lang]) ?></span></div><?php endif; ?>
      <div class="review-row"><span><?= h($T['booking']['fullName']) ?></span><span><?= h($viewBooking['fullName'] ?? '') ?></span></div>
      <div class="review-row"><span><?= h($T['booking']['phone']) ?></span><span dir="ltr"><?= h($viewPhone) ?></span></div>
      <?php if (!empty($viewBooking['email'])): ?><div class="review-row"><span><?= h($T['booking']['email']) ?></span><span dir="ltr"><?= h($viewBooking['email']) ?></span></div><?php endif; ?>
      <?php if ($isHall): ?>
        <div class="review-row"><span><?= h($bt['slotDate']) ?></span><span><?= h(format_date_h($viewBooking['slotDate'] ?? '', $lang)) ?></span></div>
        <div class="review-row"><span><?= h($bt['slotTime']) ?></span><span dir="ltr"><?= h($viewBooking['slotStart'] ?? '') ?> – <?= h($viewBooking['slotEnd'] ?? '') ?></span></div>
      <?php endif; ?>
      <div class="review-row"><span><?= $lang === 'ar' ? 'تاريخ التقديم' : 'Submitted on' ?></span><span><?= h(format_datetime_h($viewBooking['createdAt'], $lang)) ?></span></div>
      <?php if (!empty($viewBooking['notes'])): ?><div class="review-row"><span><?= h($T['booking']['notes']) ?></span><span><?= h($viewBooking['notes']) ?></span></div><?php endif; ?>
      <?php if (!empty($viewBooking['rejectReason'])): ?><div class="review-row"><span><?= h($bt['rejectReason']) ?></span><span><?= h($viewBooking['rejectReason']) ?></span></div><?php endif; ?>
    </div>

    <?php if ($isHall && $viewBooking['status'] === 'pending'):
      $overlapCount = count_overlapping_hall_slots($viewBooking['hallId'] ?? '', $viewBooking['slotDate'] ?? '', $viewBooking['slotStart'] ?? '', $viewBooking['slotEnd'] ?? '', $viewBooking['slotId'] ?? null);
      if ($overlapCount > 0): ?>
        <div class="toast-banner" style="background:var(--warn-bg);color:var(--warn);margin-top:16px">
          <span data-lucide="users" style="width:16px;height:16px"></span>
          <?= h(sprintf($bt['overlapNotice'], $overlapCount)) ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <!-- Photos: Payment Receipt & ID Card -->
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-top:20px;">
      <!-- Payment Receipt Photo -->
      <div style="background:var(--surface-2); padding:12px; border-radius:10px; border:1px solid var(--border);">
        <div class="form-label" style="margin-bottom:8px; font-weight:700; color:#0284c7; display:flex; align-items:center; gap:6px;">
          <span data-lucide="receipt" style="width:16px;height:16px"></span>
          <?= $lang === 'ar' ? 'صورة إيصال التحويل (Vodafone Cash)' : 'Payment Receipt Photo' ?>
        </div>
        <?php if (!empty($viewBooking['paymentPhotoPath'])): ?>
          <?php $isPayCloud = !empty($viewBooking['paymentPhotoCloudinary']); ?>
          <div class="detail-id-photo" style="max-height:220px;">
            <img src="<?= $isPayCloud ? h($viewBooking['paymentPhotoPath']) : 'photo.php?booking=' . urlencode($viewBooking['id']) . '&type=payment' ?>" alt="Payment Receipt">
          </div>
          <a href="<?= $isPayCloud ? h($viewBooking['paymentPhotoPath']) : 'photo.php?booking=' . urlencode($viewBooking['id']) . '&type=payment' ?>" target="_blank" class="btn btn-secondary btn-sm" style="margin-top:8px; width:100%;">
            <span data-lucide="external-link" style="width:14px;height:14px"></span>
            <?= $lang === 'ar' ? 'فتح الصورة بالكامل' : 'Open full image' ?>
          </a>
        <?php else: ?>
          <div class="cell-muted" style="padding:20px; text-align:center; font-size:13px; background:var(--surface); border-radius:8px;">
            <?= $lang === 'ar' ? 'لم يتم رفع صورة إيصال' : 'No payment receipt uploaded' ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- ID Card Photo -->
      <div style="background:var(--surface-2); padding:12px; border-radius:10px; border:1px solid var(--border);">
        <div class="form-label" style="margin-bottom:8px; font-weight:700; display:flex; align-items:center; gap:6px;">
          <span data-lucide="id-card" style="width:16px;height:16px"></span>
          <?= h($bt['idCardLabel']) ?>
        </div>
        <?php if (!empty($viewBooking['idPhotoPath'])): ?>
          <div class="detail-id-photo" style="max-height:220px;">
            <img src="<?= $isCloudinary ? h($viewBooking['idPhotoPath']) : 'photo.php?booking=' . urlencode($viewBooking['id']) . '&type=id' ?>" alt="ID">
          </div>
          <a href="<?= $isCloudinary ? h($viewBooking['idPhotoPath']) : 'photo.php?booking=' . urlencode($viewBooking['id']) . '&type=id' ?>" target="_blank" class="btn btn-secondary btn-sm" style="margin-top:8px; width:100%;">
            <span data-lucide="external-link" style="width:14px;height:14px"></span>
            <?= $lang === 'ar' ? 'فتح الصورة بالكامل' : 'Open full image' ?>
          </a>
        <?php else: ?>
          <div class="cell-muted" style="padding:20px; text-align:center; font-size:13px; background:var(--surface); border-radius:8px;">
            <?= $lang === 'ar' ? 'لم يتم رفع صورة البطاقة' : 'No ID photo uploaded' ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <?php
      // Payment Breakdown Calculations
      $itemPrice = 0;
      if ($isHall) {
          $rate = (float)($hall['hourlyRate'] ?? 0);
          $hours = 2;
          if (!empty($viewBooking['slotStart']) && !empty($viewBooking['slotEnd'])) {
              $t1 = strtotime('2000-01-01 ' . $viewBooking['slotStart']);
              $t2 = strtotime('2000-01-01 ' . $viewBooking['slotEnd']);
              if ($t2 > $t1) $hours = round(($t2 - $t1) / 3600, 1);
          }
          $itemPrice = $rate * $hours;
      } else {
          $itemPrice = (float)($course['price'] ?? 0);
      }

      $amountPaid      = isset($viewBooking['amountPaid']) ? (float)$viewBooking['amountPaid'] : ($viewBooking['status'] === 'confirmed' ? $itemPrice : 0);
      $remainingAmount = max(0, $itemPrice - $amountPaid);
      $paymentDueDate  = $viewBooking['paymentDueDate'] ?? '';
      $paymentNotes    = $viewBooking['paymentNotes'] ?? '';
    ?>

    <!-- Financial & Installment Status Card -->
    <details open style="margin-top:20px; border:1px solid var(--border); border-radius:10px; padding:16px; background:var(--surface-2, #f8fafc);">
      <summary style="cursor:pointer; font-size:15px; font-weight:700; color:var(--primary); display:flex; align-items:center; justify-content:space-between;">
        <span>💳 <?= $lang === 'ar' ? 'موقف الدفع والمستحقات المتبقية' : 'Payment & Remaining Installments' ?></span>
        <?php if ($remainingAmount == 0): ?>
          <span class="badge badge-confirmed" style="background:#10b981; color:#fff;"><?= $lang === 'ar' ? 'مدفوع بالكامل' : 'Paid in Full' ?></span>
        <?php elseif ($amountPaid > 0): ?>
          <span class="badge" style="background:#f59e0b; color:#fff;"><?= $lang === 'ar' ? 'دفعة جزئية (متبقي ' . number_format($remainingAmount) . ' EGP)' : 'Partial (Left ' . number_format($remainingAmount) . ' EGP)' ?></span>
        <?php else: ?>
          <span class="badge badge-neutral"><?= $lang === 'ar' ? 'لم يدفع بعد' : 'Unpaid' ?></span>
        <?php endif; ?>
      </summary>

      <div style="margin-top:14px; padding-top:12px; border-top:1px solid var(--border);">
        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:12px; margin-bottom:15px; text-align:center;">
          <div style="background:var(--surface); padding:10px; border-radius:8px; border:1px solid var(--border);">
            <div style="font-size:11px; color:var(--text-muted);"><?= $lang === 'ar' ? 'الإجمالي الكلي' : 'Total Amount' ?></div>
            <div style="font-size:16px; font-weight:bold;"><?= number_format($itemPrice) ?> EGP</div>
          </div>
          <div style="background:var(--surface); padding:10px; border-radius:8px; border:1px solid var(--border);">
            <div style="font-size:11px; color:var(--text-muted);"><?= $lang === 'ar' ? 'المدفوع حالياً' : 'Paid So Far' ?></div>
            <div style="font-size:16px; font-weight:bold; color:#10b981;"><?= number_format($amountPaid) ?> EGP</div>
          </div>
          <div style="background:var(--surface); padding:10px; border-radius:8px; border:1px solid var(--border);">
            <div style="font-size:11px; color:var(--text-muted);"><?= $lang === 'ar' ? 'المتبقي المستحق' : 'Remaining Due' ?></div>
            <div style="font-size:16px; font-weight:bold; color:#ef4444;"><?= number_format($remainingAmount) ?> EGP</div>
          </div>
        </div>

        <form method="post" style="background:var(--surface); padding:14px; border-radius:8px; border:1px solid var(--border);">
          <input type="hidden" name="booking_id" value="<?= h($viewBooking['id']) ?>">
          <input type="hidden" name="action" value="update_payment">

          <div class="form-row-2">
            <div class="form-group" style="margin-bottom:10px;">
              <label class="form-label"><?= $lang === 'ar' ? 'المبلغ المدفوع (جنيه)' : 'Paid Amount (EGP)' ?></label>
              <input type="number" step="any" name="amount_paid" class="form-input" value="<?= h($amountPaid) ?>" required>
            </div>
            <div class="form-group" style="margin-bottom:10px;">
              <label class="form-label"><?= $lang === 'ar' ? 'تاريخ استحقاق المتبقي (ميعاد التذكير)' : 'Reminder / Due Date' ?></label>
              <input type="date" name="payment_due_date" class="form-input" value="<?= h($paymentDueDate) ?>">
            </div>
          </div>

          <div class="form-group" style="margin-bottom:12px;">
            <label class="form-label"><?= $lang === 'ar' ? 'ملاحظات الدفع والأقساط' : 'Payment Notes' ?></label>
            <input type="text" name="payment_notes" class="form-input" value="<?= h($paymentNotes) ?>" placeholder="<?= $lang === 'ar' ? 'مثال: تم دفع 500 مقدم والباقي عند استلام المادة' : 'e.g. 500 deposit paid' ?>">
          </div>

          <button type="submit" class="btn btn-primary btn-sm">
            <span data-lucide="save" style="width:14px;height:14px"></span>
            <?= $lang === 'ar' ? 'حفظ وتحديث موقف الدفع' : 'Update Payment Details' ?>
          </button>
        </form>
      </div>
    </details>

    <!-- Action buttons -->
    <div style="display:flex;gap:10px;margin-top:22px;flex-wrap:wrap;align-items:flex-start">
      <?php if ($viewBooking['status'] === 'pending'): ?>
        <form method="post" style="display:inline-flex;gap:10px;align-items:center">
          <input type="hidden" name="booking_id" value="<?= h($viewBooking['id']) ?>"><input type="hidden" name="new_status" value="confirmed">
          <button type="submit" class="btn btn-primary"><span data-lucide="check-circle-2" style="width:15px;height:15px"></span><?= h($bt['confirm']) ?></button>
        </form>
        <form method="post" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
          <input type="hidden" name="booking_id" value="<?= h($viewBooking['id']) ?>"><input type="hidden" name="new_status" value="rejected">
          <input class="form-input" name="reason" placeholder="<?= h($bt['rejectReason']) ?>" style="width:200px">
          <button type="submit" class="btn btn-danger-outline"><span data-lucide="x-circle" style="width:15px;height:15px"></span><?= h($bt['reject']) ?></button>
        </form>
      <?php elseif ($viewBooking['status'] === 'confirmed' && !$isHall): ?>
        <form method="post" style="display:inline">
          <input type="hidden" name="booking_id" value="<?= h($viewBooking['id']) ?>"><input type="hidden" name="new_status" value="completed">
          <button type="submit" class="btn btn-primary"><span data-lucide="badge-check" style="width:15px;height:15px"></span><?= h($bt['markCompleted']) ?></button>
        </form>
      <?php endif; ?>

      <!-- Cancel (available for pending/confirmed) -->
      <?php if (in_array($viewBooking['status'], ['pending', 'confirmed'])): ?>
        <form method="post" data-confirm="<?= h($bt['cancelConfirm']) ?>" style="display:inline">
          <input type="hidden" name="booking_id" value="<?= h($viewBooking['id']) ?>"><input type="hidden" name="action" value="cancel">
          <button type="submit" class="btn btn-danger-outline"><span data-lucide="ban" style="width:15px;height:15px"></span><?= h($bt['cancel']) ?></button>
        </form>
      <?php endif; ?>

      <!-- Manager-only: permanent delete + block this number -->
      <?php if (is_manager()): ?>
        <form method="post" data-confirm="<?= h($bt['deletePermanentConfirm']) ?>" style="display:inline">
          <input type="hidden" name="booking_id" value="<?= h($viewBooking['id']) ?>"><input type="hidden" name="action" value="delete_booking">
          <button type="submit" class="btn btn-danger-outline"><span data-lucide="trash-2" style="width:15px;height:15px"></span><?= h($bt['deleteBooking']) ?></button>
        </form>
        <?php if (!is_phone_blocked($viewPhone)): ?>
        <form method="post" data-confirm="<?= h(sprintf($bt['blockNumberConfirm'], $viewPhone)) ?>" style="display:inline">
          <input type="hidden" name="booking_id" value="<?= h($viewBooking['id']) ?>"><input type="hidden" name="action" value="block_number">
          <button type="submit" class="btn btn-danger-outline"><span data-lucide="shield-ban" style="width:15px;height:15px"></span><?= h($bt['blockThisNumber']) ?></button>
        </form>
        <?php else: ?>
          <span class="badge badge-neutral" style="align-self:center"><span data-lucide="shield-ban" style="width:12px;height:12px;vertical-align:-1px"></span> <?= h($bt['numberAlreadyBlocked']) ?></span>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <!-- Edit time (hall bookings only, when pending or confirmed) -->
    <?php if ($isHall && in_array($viewBooking['status'], ['pending', 'confirmed'])): ?>
      <details style="margin-top:20px">
        <summary style="cursor:pointer;font-size:13.5px;font-weight:700;color:var(--gold)">
          <span data-lucide="calendar-clock" style="width:14px;height:14px;vertical-align:-2px"></span>
          <?= h($bt['editTimeTitle']) ?>
        </summary>
        <form method="post" style="margin-top:14px;padding:16px;background:var(--surface-2);border-radius:10px">
          <input type="hidden" name="booking_id" value="<?= h($viewBooking['id']) ?>">
          <input type="hidden" name="action" value="edit_time">
          <div class="form-row-2" style="margin-bottom:12px">
            <div class="form-group">
              <label class="form-label"><?= h($bt['slotDate']) ?></label>
              <input class="form-input" type="date" name="new_date" value="<?= h($viewBooking['slotDate'] ?? '') ?>" dir="ltr" required>
            </div>
            <div></div>
          </div>
          <div class="form-row-2" style="margin-bottom:12px">
            <div class="form-group">
              <label class="form-label"><?= $lang === 'ar' ? 'من الساعة' : 'Start time' ?></label>
              <input class="form-input" type="time" name="new_start" value="<?= h($viewBooking['slotStart'] ?? '') ?>" dir="ltr" required>
            </div>
            <div class="form-group">
              <label class="form-label"><?= $lang === 'ar' ? 'إلى الساعة' : 'End time' ?></label>
              <input class="form-input" type="time" name="new_end" value="<?= h($viewBooking['slotEnd'] ?? '') ?>" dir="ltr" required>
            </div>
          </div>
          <button type="submit" class="btn btn-secondary btn-sm"><span data-lucide="save" style="width:14px;height:14px"></span><?= h($T['common']['save']) ?></button>
        </form>
      </details>
    <?php endif; ?>

    <a href="bookings.php" class="btn btn-ghost btn-sm" style="margin-top:14px"><?= h($T['common']['close']) ?></a>
  </div>
<?php endif; ?>

<!-- Filters -->
<form method="get" class="table-toolbar">
  <div class="search-box" style="max-width:280px">
    <span data-lucide="search" style="width:16px;height:16px"></span>
    <input type="text" name="q" value="<?= h($search) ?>" placeholder="<?= h($bt['searchPh']) ?>">
  </div>
  <div class="table-filters">
    <select class="form-select" name="status" style="width:auto" onchange="this.form.submit()">
      <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>><?= h($T['common']['all']) ?> — <?= h($bt['filterStatus'] ?? '') ?></option>
      <?php foreach (['pending','confirmed','rejected','completed','cancelled'] as $s): ?>
        <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= h($T['status'][$s] ?? $s) ?></option>
      <?php endforeach; ?>
    </select>
    <select class="form-select" name="type" style="width:auto" onchange="this.form.submit()">
      <option value="all" <?= $typeFilter === 'all' ? 'selected' : '' ?>><?= h($T['common']['all']) ?></option>
      <option value="course" <?= $typeFilter === 'course' ? 'selected' : '' ?>><?= h($bt['typeCourse']) ?></option>
      <option value="hall" <?= $typeFilter === 'hall' ? 'selected' : '' ?>><?= h($bt['typeHall']) ?></option>
    </select>
    <button type="submit" class="btn btn-secondary btn-sm"><?= h($T['common']['search']) ?></button>
  </div>
</form>

<?php if (empty($filtered)): ?>
  <div class="card"><div class="empty-state"><span data-lucide="clipboard-list" style="width:44px;height:44px"></span><h3><?= h($bt['noResults']) ?></h3></div></div>
<?php else: ?>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr>
        <th><?= h($bt['table']['name']) ?></th>
        <th><?= h($bt['table']['phone']) ?></th>
        <th><?= $lang === 'ar' ? 'النوع' : 'Type' ?></th>
        <th><?= h($bt['table']['course']) ?></th>
        <th><?= $lang === 'ar' ? 'الفرع' : 'Branch' ?></th>
        <th><?= h($bt['table']['date']) ?></th>
        <th><?= h($bt['table']['status']) ?></th>
        <th><?= h($bt['table']['actions']) ?></th>
      </tr></thead>
      <tbody>
        <?php foreach ($filtered as $b):
          $isHall  = ($b['type'] ?? 'course') === 'hall';
          $bBranch = $branchById[$b['branchId'] ?? ''] ?? null;
        ?>
          <tr>
            <td class="cell-strong"><?= h($b['fullName']) ?></td>
            <td class="cell-muted" dir="ltr"><?= h($b['phone']) ?></td>
            <td class="cell-muted"><?= $isHall ? h($bt['typeHall']) : h($bt['typeCourse']) ?></td>
            <td><?= $isHall ? h($hallById[$b['hallId'] ?? '']['name'][$lang] ?? '') : h($courseById[$b['courseId'] ?? '']['name'][$lang] ?? '') ?></td>
            <td class="cell-muted"><?= $bBranch ? h($bBranch['name'][$lang]) : '—' ?></td>
            <td class="cell-muted"><?= h(format_date_h($b['createdAt'], $lang)) ?></td>
            <td><span class="badge badge-<?= h($b['status']) ?>"><?= h($T['status'][$b['status']] ?? '') ?></span></td>
            <td>
              <a href="bookings.php?view=<?= urlencode($b['id']) ?>" class="btn btn-secondary btn-sm">
                <span data-lucide="eye" style="width:13px;height:13px"></span><?= h($bt['viewDetails']) ?>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/admin_shell_end.php'; ?>
