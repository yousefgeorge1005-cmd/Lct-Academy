<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/translations.php';
$current_page = 'halls';

$halls    = load_halls();
$branches = load_branches();
$hall = find_hall($halls, $_GET['hall'] ?? '');

require __DIR__ . '/includes/header.php';

if (!$hall):
?>
  <div class="container section">
    <div class="empty-state">
      <span data-lucide="file-warning" style="width:44px;height:44px"></span>
      <h3><?= $lang === 'ar' ? 'القاعة غير موجودة' : 'Hall not found' ?></h3>
    </div>
    <div style="text-align:center;margin-top:20px">
      <a href="<?= BASE_URL ?>/halls.php" class="btn btn-secondary"><?= h($T['halls']['backToHalls']) ?></a>
    </div>
  </div>
<?php else:
  $br = find_branch($branches, $hall['branchId'] ?? '');
  $err = $_GET['error'] ?? '';
  $errMsgMap = [
    'conflict'   => $T['halls']['conflictError'],
    'dateError'  => $T['halls']['dateError'],
    'timeError'  => $T['halls']['timeError'],
    'duplicate'  => $lang === 'ar' ? 'عندك طلب حجز بنفس الرقم على نفس القاعة في نفس الوقت ده.' : 'You already have a pending booking for this hall at this time.',
    'idcard'     => $T['booking']['err']['idCard'],
    'filetype'   => $T['booking']['err']['fileType'],
    'filesize'   => $T['booking']['err']['fileSize'],
    'validation' => $lang === 'ar' ? 'في بيانات ناقصة أو غير صحيحة، راجع الاستمارة.' : 'Some fields are missing or invalid — please check the form.',
    'blocked'    => $T['booking']['err']['blocked'],
    'general'    => $T['booking']['err']['general'],
  ];
  $old = [
    'full_name'    => $_GET['full_name'] ?? '',
    'phone'        => $_GET['phone'] ?? '',
    'email'        => $_GET['email'] ?? '',
    'national_id'  => $_GET['national_id'] ?? '',
    'notes'        => $_GET['notes'] ?? '',
    'booking_date' => $_GET['booking_date'] ?? '',
    'start_time'   => $_GET['start_time'] ?? '09:00',
    'end_time'     => $_GET['end_time'] ?? '11:00',
  ];
  $tomorrow = date('Y-m-d', strtotime('+1 day'));
?>
<div class="container section">
  <div class="booking-shell">
    <div class="section-head center">
      <span class="eyebrow"><?= h($T['halls']['bookingTitle']) ?></span>
      <h1 class="section-title" style="font-size:28px"><?= h($hall['name'][$lang]) ?></h1>
      <?php if ($br): ?>
        <p class="section-sub">
          <span data-lucide="map-pin" style="width:14px;height:14px;vertical-align:-2px"></span>
          <?= h($br['name'][$lang]) ?> — <?= h($br['address'][$lang]) ?>
        </p>
      <?php endif; ?>
    </div>

    <?php if ($err && isset($errMsgMap[$err])): ?>
      <div class="toast-banner error"><span data-lucide="alert-triangle" style="width:16px;height:16px"></span><?= h($errMsgMap[$err]) ?></div>
    <?php endif; ?>

    <div class="stepper">
      <?php foreach ($T['booking']['stepLabels'] as $i => $label): ?>
        <div class="step-item"><span class="step-circle"><?= $i + 1 ?></span><span class="step-label"><?= h($label) ?></span></div>
        <?php if ($i < count($T['booking']['stepLabels']) - 1): ?><div class="step-line"></div><?php endif; ?>
      <?php endforeach; ?>
    </div>

    <form method="post" action="<?= BASE_URL ?>/submit_booking.php" enctype="multipart/form-data" class="card form-card"
      data-wizard data-err-required="<?= h($T['booking']['err']['required']) ?>" data-err-phone="<?= h($T['booking']['err']['phone']) ?>"
      data-err-email="<?= h($T['booking']['err']['email']) ?>" data-err-nid="<?= h($T['booking']['err']['nationalId']) ?>"
      data-err-agree="<?= h($T['booking']['err']['agree']) ?>" data-submitting="<?= h($T['booking']['submit']) ?>"
      data-err-time="<?= h($T['halls']['timeError']) ?>">
      <input type="hidden" name="type" value="hall">
      <input type="hidden" name="hall_id" value="<?= h($hall['id']) ?>">
      <input type="hidden" name="lang" value="<?= h($lang) ?>">

      <!-- STEP 1: Date/Time + Personal Details -->
      <div data-step="1">
        <!-- Hall info summary -->
        <div class="card" style="background:var(--surface-2);padding:14px 18px;margin-bottom:22px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
          <div>
            <div style="font-weight:700"><?= h($hall['name'][$lang]) ?></div>
            <div class="cell-muted" style="font-size:12.5px"><?= h($hall['type'][$lang]) ?> · <?= h($T['halls']['capacity']) ?>: <?= h($hall['capacity']) ?></div>
            <?php $am = $hall['amenities'] ?? []; if (!empty($am['smartboard']) || !empty($am['projector']) || !empty($am['ac'])): ?>
            <div style="display:flex;gap:6px;margin-top:6px;flex-wrap:wrap">
              <?php if (!empty($am['smartboard'])): ?><span class="badge badge-neutral" style="font-size:10.5px"><span data-lucide="presentation" style="width:10px;height:10px;vertical-align:-1px"></span> <?= h($T['halls']['amenitySmartboard']) ?></span><?php endif; ?>
              <?php if (!empty($am['projector'])): ?><span class="badge badge-neutral" style="font-size:10.5px"><span data-lucide="projector" style="width:10px;height:10px;vertical-align:-1px"></span> <?= h($T['halls']['amenityProjector']) ?></span><?php endif; ?>
              <?php if (!empty($am['ac'])): ?><span class="badge badge-neutral" style="font-size:10.5px"><span data-lucide="snowflake" style="width:10px;height:10px;vertical-align:-1px"></span> <?= h($T['halls']['amenityAc']) ?></span><?php endif; ?>
            </div>
            <?php endif; ?>
          </div>
          <?php if (!empty($hall['pricePerHour'])): ?>
          <div id="cost-display" style="font-size:14px;font-weight:700;color:var(--gold)">
            <?= h($hall['pricePerHour']) ?> <?= h($T['halls']['egpPerHour']) ?>
          </div>
          <?php endif; ?>
        </div>

        <!-- Date & Time pickers -->
        <div class="form-row-2">
          <div class="form-group">
            <label class="form-label"><?= h($T['halls']['bookingDate']) ?> <span class="required-star">*</span></label>
            <input class="form-input" type="date" name="booking_date" id="booking_date"
              value="<?= h($old['booking_date']) ?>" min="<?= $tomorrow ?>" required>
          </div>
          <div></div>
        </div>
        <div class="form-row-2">
          <div class="form-group">
            <label class="form-label"><?= h($T['halls']['startTime']) ?> <span class="required-star">*</span></label>
            <input class="form-input" type="time" name="start_time" id="start_time"
              value="<?= h($old['start_time']) ?>" dir="ltr" required>
          </div>
          <div class="form-group">
            <label class="form-label"><?= h($T['halls']['endTime']) ?> <span class="required-star">*</span></label>
            <input class="form-input" type="time" name="end_time" id="end_time"
              value="<?= h($old['end_time']) ?>" dir="ltr" required>
          </div>
        </div>

        <?php if (!empty($hall['pricePerHour'])): ?>
        <div id="cost-estimate" class="toast-banner" style="background:var(--surface-2);color:var(--text);border-color:var(--gold);display:none;margin-bottom:16px">
          <span data-lucide="calculator" style="width:15px;height:15px"></span>
          <span id="cost-text"></span>
        </div>
        <?php endif; ?>

        <!-- Personal info -->
        <div class="form-group">
          <label class="form-label"><?= h($T['booking']['fullName']) ?></label>
          <input class="form-input" name="full_name" value="<?= h($old['full_name']) ?>" placeholder="<?= h($T['booking']['fullNamePh']) ?>">
        </div>
        <div class="form-row-2">
          <div class="form-group">
            <label class="form-label"><?= h($T['booking']['phone']) ?></label>
            <input class="form-input" name="phone" value="<?= h($old['phone']) ?>" placeholder="<?= h($T['booking']['phonePh']) ?>" dir="ltr" maxlength="11" inputmode="numeric">
          </div>
          <div class="form-group">
            <label class="form-label"><?= h($T['booking']['email']) ?> <span class="opt-tag">(<?= h($T['booking']['optional']) ?>)</span></label>
            <input class="form-input" name="email" value="<?= h($old['email']) ?>" placeholder="<?= h($T['booking']['emailPh']) ?>" dir="ltr">
          </div>
        </div>
        <div class="form-row-2">
          <div class="form-group">
            <label class="form-label"><?= h($T['booking']['nationalId']) ?> <span class="required-star">*</span></label>
            <input class="form-input" name="national_id" value="<?= h($old['national_id']) ?>" dir="ltr" maxlength="14" inputmode="numeric">
          </div>
          <div class="form-group">
            <label class="form-label"><?= h($T['halls']['personCount']) ?></label>
            <input class="form-input" type="number" name="person_count" min="1" max="<?= h($hall['capacity']) ?>" dir="ltr">
          </div>
        </div>
        <div class="form-group" style="margin-bottom:0">
          <label class="form-label"><?= h($T['halls']['purpose']) ?></label>
          <textarea class="form-textarea" name="notes" placeholder="<?= h($T['halls']['purposePh']) ?>"><?= h($old['notes']) ?></textarea>
        </div>
      </div>

      <!-- STEP 2: Payment Receipt & ID Photo -->
      <div data-step="2" style="display:none">
        <!-- Instructions box -->
        <div class="instructions-card" style="background: linear-gradient(135deg, rgba(234, 179, 8, 0.12) 0%, rgba(245, 158, 11, 0.18) 100%); border: 2px dashed #f59e0b; border-radius: 12px; padding: 18px 20px; margin-bottom: 22px;">
          <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px; color:#d97706; font-weight:800; font-size:16px;">
            <span data-lucide="alert-circle" style="width:22px; height:22px"></span>
            <span style="letter-spacing:1px;">INSTRUCTIONS!!</span>
          </div>
          <ul style="margin:0; padding-right:20px; line-height:1.8; font-size:15px; font-weight:600; color:var(--text-main);">
            <li><strong style="color:var(--primary);">رسوم التسجيل:</strong> 100 جنيه فقط</li>
            <li>يتم دفع رسوم التسجيل عبر Vodafone Cash قبل استكمال بيانات التسجيل.</li>
            <li>لن يتم اعتماد التسجيل إلا بعد التأكد من إتمام عملية الدفع.</li>
            <li><strong style="color:var(--primary);">رقم فودافون كاش:</strong> <span dir="ltr" style="font-family:monospace; font-size:17px; font-weight:bold; color:#0284c7; background:var(--surface); padding:3px 10px; border-radius:6px; border:1px solid #0284c7; display:inline-block; margin-top:4px;">01005373384</span></li>
          </ul>
        </div>

        <!-- Payment receipt photo upload -->
        <div style="margin-bottom:24px;">
          <h3 style="font-size:17px;margin-bottom:6px;display:flex;align-items:center;gap:6px;">
            <span data-lucide="receipt" style="width:18px;height:18px;color:var(--primary)"></span>
            <?= $lang === 'ar' ? 'صورة إيصال التحويل (Vodafone Cash)' : 'Payment Receipt Photo' ?> <span class="required-star">*</span>
          </h3>
          <p style="font-size:13.5px;color:var(--text-soft);margin-bottom:14px;line-height:1.6">
            <?= $lang === 'ar' ? 'ارفع صورة شاشة أو صورة إيصال تحويل رسوم التسجيل (100 جنيه). لن يتم إتمام التسجيل بدون رفع الصورة.' : 'Upload the 100 EGP payment transfer receipt photo.' ?>
          </p>
          <div class="dropzone dropzone-payment" data-dz-payment>
            <span data-lucide="upload-cloud"></span>
            <div class="dropzone-text"><?= $lang === 'ar' ? 'اسحب صورة إيصال التحويل هنا أو اضغط للاختيار' : 'Drag receipt photo here or click to select' ?></div>
            <div class="dropzone-hint"><?= h($T['booking']['idCardFormats']) ?></div>
          </div>
          <input type="file" name="payment_photo" accept="image/png,image/jpeg" style="display:none">
          <div class="id-preview" data-payment-preview style="display:none;margin-top:12px">
            <img alt="Payment Receipt preview">
            <div class="id-preview-actions">
              <button type="button" class="btn btn-secondary btn-sm" data-change-payment-photo>
                <span data-lucide="pencil" style="width:13px;height:13px"></span>
                <?= $lang === 'ar' ? 'تغيير صورة الإيصال' : 'Change Receipt Photo' ?>
              </button>
            </div>
          </div>
        </div>

        <!-- ID Card photo upload -->
        <div>
          <h3 style="font-size:17px;margin-bottom:6px"><?= h($T['booking']['idCardTitle']) ?> <span class="required-star">*</span></h3>
          <p style="font-size:13.5px;color:var(--text-soft);margin-bottom:14px;line-height:1.6"><?= h($T['booking']['idCardHint']) ?></p>
          <div class="dropzone dropzone-id" data-dz-id>
            <span data-lucide="upload"></span>
            <div class="dropzone-text"><?= h($T['booking']['idCardDrag']) ?></div>
            <div class="dropzone-hint"><?= h($T['booking']['idCardFormats']) ?></div>
          </div>
          <input type="file" name="id_photo" accept="image/png,image/jpeg" style="display:none">
          <div class="id-preview" data-id-preview style="display:none;margin-top:12px">
            <img alt="ID preview">
            <div class="id-preview-actions">
              <button type="button" class="btn btn-secondary btn-sm" data-change-photo>
                <span data-lucide="pencil" style="width:13px;height:13px"></span><?= h($T['booking']['idCardUpload']) ?>
              </button>
            </div>
          </div>
        </div>

        <div class="privacy-note" style="margin-top:16px;">
          <span data-lucide="lock" style="width:15px;height:15px"></span>
          <span><?= $lang === 'ar' ? 'الصور بتتخزن بشكل آمن وبيشوفها فريق الإدارة بس عشان يراجع الطلب.' : 'The photos are stored securely and only seen by the admin team to review your request.' ?></span>
        </div>
      </div>

      <!-- STEP 3: Review -->
      <div data-step="3" style="display:none">
        <h3 style="font-size:17px;margin-bottom:16px"><?= $lang === 'ar' ? 'راجع بياناتك قبل الإرسال' : 'Review your details before submitting' ?></h3>
        <div class="review-block">
          <div class="review-row"><span><?= h($T['halls']['selectedHall']) ?></span><span><?= h($hall['name'][$lang]) ?></span></div>
          <?php if ($br): ?><div class="review-row"><span><?= h($T['halls']['branch']) ?></span><span><?= h($br['name'][$lang]) ?></span></div><?php endif; ?>
          <div class="review-row"><span><?= h($T['halls']['bookingDate']) ?></span><span id="review-date" dir="ltr"></span></div>
          <div class="review-row"><span><?= h($T['halls']['startTime']) ?> – <?= h($T['halls']['endTime']) ?></span><span id="review-time" dir="ltr"></span></div>
          <div class="review-row"><span><?= h($T['booking']['fullName']) ?></span><span data-review-field="full_name"></span></div>
          <div class="review-row"><span><?= h($T['booking']['phone']) ?></span><span data-review-field="phone" dir="ltr"></span></div>
        </div>
        
        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
          <div style="flex:1;min-width:140px;">
            <div style="font-size:12px;color:var(--text-soft);margin-bottom:4px;"><?= $lang === 'ar' ? 'صورة إيصال الدفع' : 'Payment Receipt' ?></div>
            <img data-review-payment-photo alt="" style="display:none;max-height:160px;width:100%;object-fit:cover;border-radius:10px;border:1px solid var(--border)">
          </div>
          <div style="flex:1;min-width:140px;">
            <div style="font-size:12px;color:var(--text-soft);margin-bottom:4px;"><?= $lang === 'ar' ? 'صورة البطاقة الشخصية' : 'ID Card' ?></div>
            <img data-review-id-photo alt="" style="display:none;max-height:160px;width:100%;object-fit:cover;border-radius:10px;border:1px solid var(--border)">
          </div>
        </div>

        <p style="font-size:12.5px;color:var(--text-soft);margin-bottom:14px;line-height:1.6"><?= h($T['booking']['reviewNote']) ?></p>

        <label class="agree-row" style="align-items:flex-start; gap:12px; padding:16px; background:var(--surface-2); border-radius:12px; border:1px solid var(--border); margin-top:18px;">
          <input type="checkbox" name="agree" value="1" style="margin-top:4px;">
          <span style="font-size:14px; line-height:1.8; color:var(--text-main);">
            اقر انا <strong data-pledge-name style="color:var(--primary); text-decoration:underline;">______</strong> بصحة جميع البيانات المقدمة والتي تم تعبئتها من قبلي و اتعهد بتحمل جميع الاجرائات القانونية حيال ثبوت خلاف ذلك . اتعهد بالحضور في المواعيد المحددة من قبل الاكاديميه
          </span>
        </label>
      </div>

      <div class="form-nav">
        <button type="button" class="btn btn-secondary" data-nav-back style="visibility:hidden">
          <span data-lucide="<?= $dir === 'rtl' ? 'arrow-right' : 'arrow-left' ?>" style="width:16px;height:16px"></span><?= h($T['booking']['back']) ?>
        </button>
        <button type="button" class="btn btn-primary" data-nav-next>
          <?= h($T['booking']['next']) ?><span data-lucide="<?= $dir === 'rtl' ? 'arrow-left' : 'arrow-right' ?>" style="width:16px;height:16px"></span>
        </button>
        <button type="button" class="btn btn-primary" data-nav-submit style="display:none">
          <span data-lucide="check-circle-2" style="width:16px;height:16px"></span><?= h($T['booking']['submit']) ?>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
// Estimated cost calculator
(function() {
  var pricePerHour = <?= (int)($hall['pricePerHour'] ?? 0) ?>;
  var labelEst = <?= json_encode($T['halls']['estimatedCost']) ?>;
  var labelEgp = <?= json_encode($T['common']['egp']) ?>;

  function calcCost() {
    var d = document.getElementById('booking_date').value;
    var s = document.getElementById('start_time').value;
    var e = document.getElementById('end_time').value;
    var revDate = document.getElementById('review-date');
    var revTime = document.getElementById('review-time');
    if (revDate) revDate.textContent = d;
    if (revTime) revTime.textContent = s + ' – ' + e;

    if (!pricePerHour || !s || !e) return;
    var sm = parseInt(s.split(':')[0])*60 + parseInt(s.split(':')[1]);
    var em = parseInt(e.split(':')[0])*60 + parseInt(e.split(':')[1]);
    var hrs = (em - sm) / 60;
    var box = document.getElementById('cost-estimate');
    var txt = document.getElementById('cost-text');
    if (!box || !txt) return;
    if (hrs <= 0) { box.style.display = 'none'; return; }
    var cost = Math.ceil(hrs * pricePerHour);
    txt.textContent = labelEst + ': ' + cost + ' ' + labelEgp + ' (' + hrs.toFixed(1) + ' <?= $lang === "ar" ? "ساعة" : "hr" ?>)';
    box.style.display = 'flex';
  }

  ['booking_date','start_time','end_time'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('change', calcCost);
  });
})();
</script>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
