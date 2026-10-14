<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/translations.php';
require_admin();

$lang = current_lang();
$theme = current_theme();
$T = translations($lang);
$dir = $T['dir'];

$editLang = $_GET['edit_lang'] ?? 'ar';
if (!in_array($editLang, ['ar', 'en'])) $editLang = 'ar';

$notice = '';

// Handle AJAX Save & Form Posts
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'api_save_key' || $action === 'save_key') {
        $key = trim($_POST['key'] ?? '');
        $val = $_POST['value'] ?? '';
        $oldVal = trim($_POST['old_value'] ?? '');
        $targetLang = $_POST['lang'] ?? $editLang;
        if (!in_array($targetLang, ['ar', 'en'])) $targetLang = 'ar';

        $custom = get_custom_translations();
        if (!isset($custom[$targetLang]) || !is_array($custom[$targetLang])) {
            $custom[$targetLang] = [];
        }

        // Set nested key value if valid key path
        if ($key !== '' && strpos($key, 'custom_') !== 0) {
            $keys = explode('.', $key);
            $ptr = &$custom[$targetLang];
            foreach ($keys as $i => $k) {
                if ($i === count($keys) - 1) {
                    $ptr[$k] = $val;
                } else {
                    if (!isset($ptr[$k]) || !is_array($ptr[$k])) {
                        $ptr[$k] = [];
                    }
                    $ptr = &$ptr[$k];
                }
            }
        }

        // Also add global text replacement mapping if old_value was supplied
        if ($oldVal !== '' && $oldVal !== $val) {
            if (!isset($custom['replacements'][$targetLang]) || !is_array($custom['replacements'][$targetLang])) {
                $custom['replacements'][$targetLang] = [];
            }
            $custom['replacements'][$targetLang][$oldVal] = $val;
        }

        save_custom_translations($custom);

        if ($action === 'api_save_key') {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => true, 'message' => $targetLang === 'ar' ? 'تم حفظ التعديل بنجاح' : 'Saved successfully']);
            exit;
        }
        $notice = 'success:' . ($targetLang === 'ar' ? 'تم حفظ التعديل بنجاح' : 'Saved successfully');
    }


    if ($action === 'save_batch') {
        $targetLang = $_POST['target_lang'] ?? $editLang;
        if (!in_array($targetLang, ['ar', 'en'])) $targetLang = 'ar';
        $fields = $_POST['fields'] ?? [];

        if (is_array($fields) && !empty($fields)) {
            $custom = get_custom_translations();
            if (!isset($custom[$targetLang]) || !is_array($custom[$targetLang])) {
                $custom[$targetLang] = [];
            }

            foreach ($fields as $key => $val) {
                $keys = explode('.', $key);
                $ptr = &$custom[$targetLang];
                foreach ($keys as $i => $k) {
                    if ($i === count($keys) - 1) {
                        $ptr[$k] = $val;
                    } else {
                        if (!isset($ptr[$k]) || !is_array($ptr[$k])) {
                            $ptr[$k] = [];
                        }
                        $ptr = &$ptr[$k];
                    }
                }
            }
            save_custom_translations($custom);
            header('Location: site_editor.php?edit_lang=' . $targetLang . '&saved=1');
            exit;
        }
    }

    if ($action === 'reset_translations') {
        $targetLang = $_POST['target_lang'] ?? 'all';
        $custom = get_custom_translations();
        if ($targetLang === 'ar') {
            $custom['ar'] = [];
            if (isset($custom['replacements']['ar'])) $custom['replacements']['ar'] = [];
        } elseif ($targetLang === 'en') {
            $custom['en'] = [];
            if (isset($custom['replacements']['en'])) $custom['replacements']['en'] = [];
        } else {
            $custom = ['ar' => [], 'en' => [], 'replacements' => ['ar' => [], 'en' => []]];
        }
        save_custom_translations($custom);
        header('Location: site_editor.php?reset=1');
        exit;
    }


    if ($action === 'save_branches_halls') {
        $branchesData = $_POST['branches'] ?? [];
        $hallsData = $_POST['halls'] ?? [];

        if (!empty($branchesData)) {
            update_json_file(SETTINGS_FILE, function ($all) use ($branchesData) {
                if (!isset($all['branches'])) $all['branches'] = [];
                foreach ($all['branches'] as &$b) {
                    if (isset($branchesData[$b['id']])) {
                        $bd = $branchesData[$b['id']];
                        if (!empty($bd['name_ar'])) $b['name']['ar'] = trim($bd['name_ar']);
                        if (!empty($bd['name_en'])) $b['name']['en'] = trim($bd['name_en']);
                        if (!empty($bd['address_ar'])) $b['address']['ar'] = trim($bd['address_ar']);
                        if (!empty($bd['address_en'])) $b['address']['en'] = trim($bd['address_en']);
                        if (!empty($bd['phone'])) $b['phone'] = trim($bd['phone']);
                        if (!empty($bd['email'])) $b['email'] = trim($bd['email']);
                        if (!empty($bd['hours_ar'])) $b['hours']['ar'] = trim($bd['hours_ar']);
                    }
                }
                unset($b);
                return $all;
            });
        }

        if (!empty($hallsData)) {
            update_json_file(HALLS_FILE, function ($all) use ($hallsData) {
                foreach ($all as &$h) {
                    if (isset($hallsData[$h['id']])) {
                        $hd = $hallsData[$h['id']];
                        if (!empty($hd['name_ar'])) $h['name']['ar'] = trim($hd['name_ar']);
                        if (!empty($hd['name_en'])) $h['name']['en'] = trim($hd['name_en']);
                        if (!empty($hd['type_ar'])) $h['type']['ar'] = trim($hd['type_ar']);
                        if (!empty($hd['capacity'])) $h['capacity'] = (int)$hd['capacity'];
                        if (!empty($hd['price_per_hour'])) $h['pricePerHour'] = (int)$hd['price_per_hour'];
                    }
                }
                unset($h);
                return $all;
            });
        }

        header('Location: site_editor.php?saved=1#branch-halls-sec');
        exit;
    }
}

$editorBranches = load_branches();
$editorHalls = load_halls();


// Get full current translation array for the selected edit language
$fullTranslations = translations($editLang);
$flatTranslations = flatten_translation_array($fullTranslations);

// Define UI Sections for editing
$sections = [
    'hero' => [
        'title' => $lang === 'ar' ? '🏠 الواجهة الرئيسية و Hero' : '🏠 Hero & Main Page',
        'icon' => 'sparkles',
        'keys' => [
            'brand.name' => $lang === 'ar' ? 'اسم الأكاديمية (Brand Name)' : 'Brand Name',
            'brand.tag' => $lang === 'ar' ? 'شعار الأكاديمية (Tagline)' : 'Tagline',
            'hero.eyebrow' => $lang === 'ar' ? 'الشريط العلوي (Eyebrow Badge)' : 'Hero Eyebrow Badge',
            'hero.title1' => $lang === 'ar' ? 'العنوان الرئيسي - الجزء الأول' : 'Hero Title - Part 1',
            'hero.title2' => $lang === 'ar' ? 'العنوان الرئيسي - الجزء الملون' : 'Hero Title - Accent',
            'hero.subtitle' => $lang === 'ar' ? 'الوصف الفرعي بالـ Hero' : 'Hero Subtitle',
            'hero.ctaCourses' => $lang === 'ar' ? 'نص زر الكورسات' : 'Courses Button Text',
            'hero.ctaLookup' => $lang === 'ar' ? 'نص زر متابعة الحجز' : 'Lookup Button Text',
            'hero.trust1' => $lang === 'ar' ? 'ميزة التوثيق 1' : 'Trust Point 1',
            'hero.trust2' => $lang === 'ar' ? 'ميزة التوثيق 2' : 'Trust Point 2',
            'hero.trust3' => $lang === 'ar' ? 'ميزة التوثيق 3' : 'Trust Point 3',
            'hero.stat1l' => $lang === 'ar' ? 'تسمية الإحصائية 1' : 'Stat Label 1',
            'hero.stat1n' => $lang === 'ar' ? 'رقم الإحصائية 1' : 'Stat Num 1',
            'hero.stat2l' => $lang === 'ar' ? 'تسمية الإحصائية 2' : 'Stat Label 2',
            'hero.stat2n' => $lang === 'ar' ? 'رقم الإحصائية 2' : 'Stat Num 2',
            'hero.stat3l' => $lang === 'ar' ? 'تسمية الإحصائية 3' : 'Stat Label 3',
            'hero.stat3n' => $lang === 'ar' ? 'رقم الإحصائية 3' : 'Stat Num 3',
        ]
    ],
    'depts' => [
        'title' => $lang === 'ar' ? '📌 قسم الأقسام التدريبية' : '📌 Training Departments',
        'icon' => 'grid',
        'keys' => [
            'depts.eyebrow' => $lang === 'ar' ? 'العنوان التجريبي فوق الأقسام' : 'Departments Eyebrow',
            'depts.title' => $lang === 'ar' ? 'عنوان قسم الأقسام' : 'Departments Section Title',
            'depts.sub' => $lang === 'ar' ? 'الوصف الفرعي للأقسام' : 'Departments Subtitle',
            'depts.langTitle' => $lang === 'ar' ? 'عنوان قسم اللغات' : 'Languages Dept Title',
            'depts.langDesc' => $lang === 'ar' ? 'وصف قسم اللغات' : 'Languages Dept Desc',
            'depts.compTitle' => $lang === 'ar' ? 'عنوان قسم الكمبيوتر' : 'Computer Dept Title',
            'depts.compDesc' => $lang === 'ar' ? 'وصف قسم الكمبيوتر' : 'Computer Dept Desc',
            'depts.viewAll' => $lang === 'ar' ? 'زر عرض كل الكورسات' : 'View All Courses Text',
        ]
    ],
    'why' => [
        'title' => $lang === 'ar' ? '🛡️ قسم لماذا LCT والضمانات' : '🛡️ Why LCT & Guarantees',
        'icon' => 'shield-check',
        'keys' => [
            'why.eyebrow' => $lang === 'ar' ? 'شارة لماذا LCT' : 'Why LCT Eyebrow',
            'why.title' => $lang === 'ar' ? 'عنوان الضمانات الرئيسي' : 'Guarantees Main Title',
            'why.sub' => $lang === 'ar' ? 'الوصف الفرعي للضمانات' : 'Guarantees Subtitle',
            'why.f1t' => $lang === 'ar' ? 'عنوان الميزة 1 (حجز موثق)' : 'Feature 1 Title',
            'why.f1d' => $lang === 'ar' ? 'تفاصيل الميزة 1' : 'Feature 1 Details',
            'why.f2t' => $lang === 'ar' ? 'عنوان الميزة 2 (مقاعد حقيقية)' : 'Feature 2 Title',
            'why.f2d' => $lang === 'ar' ? 'تفاصيل الميزة 2' : 'Feature 2 Details',
            'why.f3t' => $lang === 'ar' ? 'عنوان الميزة 3 (صفحة الطالب)' : 'Feature 3 Title',
            'why.f3d' => $lang === 'ar' ? 'تفاصيل الميزة 3' : 'Feature 3 Details',
            'why.f4t' => $lang === 'ar' ? 'عنوان الميزة 4 (إدارة شاملة)' : 'Feature 4 Title',
            'why.f4d' => $lang === 'ar' ? 'تفاصيل الميزة 4' : 'Feature 4 Details',
        ]
    ],
    'ctaBand' => [
        'title' => $lang === 'ar' ? '📢 الشريط الترويجي (Call to Action)' : '📢 CTA Callout Band',
        'icon' => 'megaphone',
        'keys' => [
            'ctaBand.title' => $lang === 'ar' ? 'عنوان الشريط الترويجي' : 'CTA Title',
            'ctaBand.sub' => $lang === 'ar' ? 'الوصف الترويجي' : 'CTA Subtitle',
            'ctaBand.btn' => $lang === 'ar' ? 'نص زر الحجز' : 'CTA Button Text',
        ]
    ],
    'nav' => [
        'title' => $lang === 'ar' ? '🧭 روابط القائمة الرئيسية والهيدر' : '🧭 Header & Navigation Links',
        'icon' => 'compass',
        'keys' => [
            'nav.home' => $lang === 'ar' ? 'رابط الرئيسية' : 'Home Link',
            'nav.courses' => $lang === 'ar' ? 'رابط الكورسات' : 'Courses Link',
            'nav.halls' => $lang === 'ar' ? 'رابط القاعات' : 'Halls Link',
            'nav.myPage' => $lang === 'ar' ? 'رابط صفحة الطالب' : 'Student Page Link',
        ]
    ],
    'footer' => [
        'title' => $lang === 'ar' ? '🔻 التذييل والفوتر (Footer)' : '🔻 Footer & Rights',
        'icon' => 'layout-grid',
        'keys' => [
            'footer.desc' => $lang === 'ar' ? 'وصف الأكاديمية بالفوتر' : 'Footer Academy Desc',
            'footer.quick' => $lang === 'ar' ? 'عنوان روابط سريعة' : 'Quick Links Heading',
            'footer.contact' => $lang === 'ar' ? 'عنوان تواصل معنا' : 'Contact Us Heading',
            'footer.rights' => $lang === 'ar' ? 'جملة حقوق الملكية' : 'Rights Reserved Text',
        ]
    ],
    'coursesPage' => [
        'title' => $lang === 'ar' ? '📚 صفحة دليل الكورسات' : '📚 Courses Catalog Page',
        'icon' => 'book-open',
        'keys' => [
            'coursesPage.title' => $lang === 'ar' ? 'عنوان صفحة الكورسات' : 'Courses Page Title',
            'coursesPage.sub' => $lang === 'ar' ? 'وصف صفحة الكورسات' : 'Courses Page Subtitle',
            'coursesPage.searchPh' => $lang === 'ar' ? 'نص البحث بالحقل' : 'Search Placeholder',
            'coursesPage.all' => $lang === 'ar' ? 'تسمية فلتر الكل' : 'All Filter Text',
            'coursesPage.language' => $lang === 'ar' ? 'تسمية فلتر اللغات' : 'Languages Filter Text',
            'coursesPage.computer' => $lang === 'ar' ? 'تسمية فلتر الكمبيوتر' : 'Computer Filter Text',
        ]
    ],
    'halls' => [
        'title' => $lang === 'ar' ? '🏛️ صفحة حجز القاعات' : '🏛️ Halls Booking Page',
        'icon' => 'building-2',
        'keys' => [
            'halls.title' => $lang === 'ar' ? 'عنوان صفحة القاعات' : 'Halls Page Title',
            'halls.sub' => $lang === 'ar' ? 'وصف صفحة القاعات' : 'Halls Page Subtitle',
            'halls.bookNow' => $lang === 'ar' ? 'زر احجز هذه القاعة' : 'Book Hall Button',
            'halls.selectedHall' => $lang === 'ar' ? 'تسمية القاعة المختارة' : 'Selected Hall Label',
            'halls.amenitySmartboard' => $lang === 'ar' ? 'تسمية سبورة ذكية' : 'Smartboard Label',
            'halls.amenityProjector' => $lang === 'ar' ? 'تسمية بروجيكتور' : 'Projector Label',
            'halls.amenityAc' => $lang === 'ar' ? 'تسمية مكيفة' : 'AC Label',
        ]
    ],
    'portal' => [
        'title' => $lang === 'ar' ? '🎓 صفحة الطالب والمتابعة' : '🎓 Student Portal Page',
        'icon' => 'user',
        'keys' => [
            'portal.title' => $lang === 'ar' ? 'عنوان صفحة الطالب' : 'Portal Title',
            'portal.sub' => $lang === 'ar' ? 'وصف صفحة الطالب' : 'Portal Subtitle',
            'portal.phonePh' => $lang === 'ar' ? 'نص حقل البحث برقم الهاتف/المرجع' : 'Portal Search Placeholder',
            'portal.searchBtn' => $lang === 'ar' ? 'زر عرض حجوزاتي' : 'Portal Search Button',
            'portal.welcomeBack' => $lang === 'ar' ? 'جملة أهلاً بك' : 'Welcome Back Label',
            'portal.myBookings' => $lang === 'ar' ? 'عنوان حجوزاتي' : 'My Bookings Heading',
        ]
    ],
    'booking' => [
        'title' => $lang === 'ar' ? '📝 استمارة التسجيل ورسائل النماذج' : '📝 Booking Form & Texts',
        'icon' => 'file-text',
        'keys' => [
            'booking.title' => $lang === 'ar' ? 'عنوان استمارة التسجيل' : 'Registration Title',
            'booking.fullName' => $lang === 'ar' ? 'تسمية الاسم بالكامل' : 'Full Name Label',
            'booking.phone' => $lang === 'ar' ? 'تسمية رقم الموبايل' : 'Phone Label',
            'booking.nationalId' => $lang === 'ar' ? 'تسمية الرقم القومي' : 'National ID Label',
            'booking.idCardTitle' => $lang === 'ar' ? 'عنوان رفع صورة البطاقة' : 'ID Upload Title',
            'booking.idCardHint' => $lang === 'ar' ? 'ملاحظة توثيق الهوية' : 'ID Upload Hint',
            'booking.submit' => $lang === 'ar' ? 'زر إرسال الحجز' : 'Submit Button Text',
            'booking.successTitle' => $lang === 'ar' ? 'عنوان نجاح الحجز' : 'Success Title',
            'booking.successDesc' => $lang === 'ar' ? 'وصف نجاح الحجز' : 'Success Description',
        ]
    ]
];

$active_tab = 'site_editor';
require __DIR__ . '/../includes/admin_shell_start.php';
?>

<style>
.editor-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 15px;
  margin-bottom: 24px;
  background: var(--surface-1, #fff);
  padding: 20px;
  border-radius: 12px;
  border: 1px solid var(--border);
  box-shadow: 0 4px 12px rgba(0,0,0,0.03);
}
.editor-title {
  display: flex;
  align-items: center;
  gap: 12px;
}
.editor-title h2 { margin: 0; font-size: 20px; font-weight: 700; }
.lang-switcher {
  display: flex;
  gap: 6px;
  background: var(--surface-2, #f1f5f9);
  padding: 4px;
  border-radius: 8px;
}
.lang-tab {
  padding: 6px 14px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
  color: var(--text-soft);
  transition: all 0.2s ease;
}
.lang-tab.active {
  background: var(--primary, #0284c7);
  color: #fff;
  box-shadow: 0 2px 6px rgba(2,132,199,0.3);
}
.search-box-wrap {
  position: relative;
  width: 100%;
  max-width: 420px;
}
.search-box-wrap input {
  width: 100%;
  padding: 10px 14px 10px 38px;
  border-radius: 8px;
  border: 1px solid var(--border);
  background: var(--surface-1);
  color: var(--text);
  font-size: 13.5px;
}
.search-box-wrap .search-icon {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  inset-inline-start: 12px;
  color: var(--text-muted);
  pointer-events: none;
}
.view-tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  border-bottom: 2px solid var(--border);
  padding-bottom: 2px;
}
.view-tab-btn {
  background: none;
  border: none;
  padding: 10px 18px;
  font-size: 14px;
  font-weight: 600;
  color: var(--text-soft);
  cursor: pointer;
  border-bottom: 3px solid transparent;
  margin-bottom: -3px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.view-tab-btn.active {
  color: var(--primary);
  border-bottom-color: var(--primary);
}
.section-accordion {
  background: var(--surface-1);
  border: 1px solid var(--border);
  border-radius: 12px;
  margin-bottom: 16px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.02);
}
.accordion-head {
  padding: 16px 20px;
  background: var(--surface-1);
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  font-weight: 700;
  font-size: 15px;
  user-select: none;
  transition: background 0.2s;
}
.accordion-head:hover {
  background: var(--surface-2);
}
.accordion-body {
  padding: 20px;
  border-top: 1px solid var(--border);
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 16px;
  background: var(--bg-surface, #fafafa);
}
.field-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  background: var(--surface-1);
  padding: 14px;
  border-radius: 8px;
  border: 1px solid var(--border);
}
.field-label {
  font-size: 12.5px;
  font-weight: 700;
  color: var(--text-soft);
  display: flex;
  justify-content: space-between;
}
.field-path {
  font-size: 11px;
  color: var(--text-muted);
  font-family: monospace;
}
.field-group input, .field-group textarea {
  width: 100%;
  padding: 9px 12px;
  border-radius: 6px;
  border: 1px solid var(--border);
  background: var(--surface-1);
  color: var(--text);
  font-size: 13.5px;
  font-family: inherit;
  transition: border-color 0.2s;
}
.field-group input:focus, .field-group textarea:focus {
  border-color: var(--primary);
  outline: none;
  box-shadow: 0 0 0 3px rgba(2,132,199,0.15);
}
.save-toast {
  position: fixed;
  bottom: 24px;
  inset-inline-end: 24px;
  background: #10b981;
  color: #fff;
  padding: 12px 20px;
  border-radius: 8px;
  font-weight: 600;
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
  z-index: 99999;
  display: flex;
  align-items: center;
  gap: 8px;
  transform: translateY(100px);
  opacity: 0;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.save-toast.show {
  transform: translateY(0);
  opacity: 1;
}

/* Canvas Mode */
.canvas-frame-container {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.canvas-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: var(--surface-1);
  padding: 10px 16px;
  border-radius: 8px;
  border: 1px solid var(--border);
  gap: 10px;
  flex-wrap: wrap;
}
.canvas-frame {
  width: 100%;
  height: 700px;
  border: 1px solid var(--border);
  border-radius: 10px;
  background: #fff;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}
</style>

<div class="editor-header">
  <div class="editor-title">
    <span data-lucide="edit-3" style="width:24px;height:24px;color:var(--primary)"></span>
    <div>
      <h2><?= $lang === 'ar' ? 'التعديل على الموقع (محرر النصوص المباشر)' : 'Website Live Editor' ?></h2>
      <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
        <?= $lang === 'ar' ? 'عدّل أي كلمة، عنوان، أو وصف في الموقع مباشرة بنقرة واحدة' : 'Edit any word, title, or description on the site in real-time' ?>
      </div>
    </div>
  </div>

  <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
    <div class="lang-switcher">
      <a href="site_editor.php?edit_lang=ar" class="lang-tab <?= $editLang === 'ar' ? 'active' : '' ?>">🇸🇦 اللغة العربية</a>
      <a href="site_editor.php?edit_lang=en" class="lang-tab <?= $editLang === 'en' ? 'active' : '' ?>">🇬🇧 English</a>
    </div>

    <form method="post" data-confirm="<?= $lang === 'ar' ? 'هل أنت تأكد من إعادة النص الأصلي لجميع العبارات بالموقع؟' : 'Reset all custom site texts to default?' ?>">
      <input type="hidden" name="action" value="reset_translations">
      <input type="hidden" name="target_lang" value="<?= $editLang ?>">
      <button type="submit" class="btn btn-secondary" style="padding:7px 12px;font-size:12px;color:#ef4444;border-color:rgba(239,68,68,0.3)">
        <span data-lucide="rotate-ccw" style="width:14px;height:14px"></span>
        <?= $lang === 'ar' ? 'إعادة النص الأصلي' : 'Reset Defaults' ?>
      </button>
    </form>
  </div>
</div>

<?php if (isset($_GET['saved'])): ?>
  <div class="toast-banner success"><span data-lucide="check-circle-2" style="width:16px;height:16px"></span><?= $lang === 'ar' ? 'تم حفظ التعديلات بنجاح في الموقع!' : 'Changes saved successfully to the live site!' ?></div>
<?php endif; ?>

<?php if (isset($_GET['reset'])): ?>
  <div class="toast-banner success"><span data-lucide="check-circle-2" style="width:16px;height:16px"></span><?= $lang === 'ar' ? 'تم إعادة النصوص الأصلية بنجاح' : 'Reset to default texts successfully' ?></div>
<?php endif; ?>

<div class="view-tabs">
  <button type="button" class="view-tab-btn active" id="tabFormBtn" onclick="switchViewMode('form')">
    <span data-lucide="list-checks" style="width:17px;height:17px"></span>
    <?= $lang === 'ar' ? 'تعديل جميع نصوص الموقع حسب الأقسام' : 'Categorized Text Form' ?>
  </button>
  <button type="button" class="view-tab-btn" id="tabCanvasBtn" onclick="switchViewMode('canvas')">
    <span data-lucide="eye" style="width:17px;height:17px"></span>
    <?= $lang === 'ar' ? 'معاينة شاشة الموقع الحية' : 'Live Canvas Preview' ?>
  </button>
</div>

<!-- MODE 1: FORM SECTION EDITOR -->
<div id="formViewMode">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;gap:12px;flex-wrap:wrap;">
    <div class="search-box-wrap">
      <span data-lucide="search" class="search-icon" style="width:16px;height:16px"></span>
      <input type="text" id="siteStringSearch" placeholder="<?= $lang === 'ar' ? 'ابحث عن أي كلمة أو عبارة بالموقع...' : 'Search any text or phrase on the site...' ?>" onkeyup="filterSiteStrings()">
    </div>

    <button type="button" class="btn btn-gold" onclick="submitMainBatchForm()">
      <span data-lucide="save" style="width:16px;height:16px"></span>
      <?= $lang === 'ar' ? 'حفظ جميع التعديلات' : 'Save All Changes' ?>
    </button>
  </div>

  <form id="batchSaveForm" method="post">
    <input type="hidden" name="action" value="save_batch">
    <input type="hidden" name="target_lang" value="<?= $editLang ?>">

    <?php foreach ($sections as $secId => $sec): ?>
      <div class="section-accordion" id="sec-accordion-<?= $secId ?>">
        <div class="accordion-head" onclick="toggleAccordion('<?= $secId ?>')">
          <div style="display:flex;align-items:center;gap:10px;">
            <span data-lucide="<?= $sec['icon'] ?>" style="width:18px;height:18px;color:var(--primary)"></span>
            <span><?= h($sec['title']) ?></span>
          </div>
          <span data-lucide="chevron-down" id="acc-icon-<?= $secId ?>" style="width:18px;height:18px;transition:transform 0.2s"></span>
        </div>

        <div class="accordion-body" id="acc-body-<?= $secId ?>">
          <?php foreach ($sec['keys'] as $keyPath => $label): ?>
            <?php 
              $val = $flatTranslations[$keyPath] ?? '';
              $isLong = strlen($val) > 45 || strpos($val, "\n") !== false;
            ?>
            <div class="field-group" data-string-item data-search-text="<?= h(mb_strtolower($label . ' ' . $val . ' ' . $keyPath)) ?>">
              <div class="field-label">
                <span><?= h($label) ?></span>
                <span class="field-path"><?= h($keyPath) ?></span>
              </div>

              <?php if ($isLong): ?>
                <textarea name="fields[<?= h($keyPath) ?>]" rows="3" onblur="autoSaveField('<?= h($keyPath) ?>', this.value)"><?= h($val) ?></textarea>
              <?php else: ?>
                <input type="text" name="fields[<?= h($keyPath) ?>]" value="<?= h($val) ?>" onblur="autoSaveField('<?= h($keyPath) ?>', this.value)">
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </form>

  <!-- ACCORDION: BRANCHES & HALLS EDITING -->
  <div class="section-accordion" id="branch-halls-sec">
    <div class="accordion-head" onclick="toggleAccordion('branches-halls')">
      <div style="display:flex;align-items:center;gap:10px;">
        <span data-lucide="building" style="width:18px;height:18px;color:var(--primary)"></span>
        <span><?= $lang === 'ar' ? '🏢 تعديل بيانات فرع الإسماعيلية وفرع التلاتيني والقاعات' : '🏢 Edit Ismailia & El-Talatini Branches & Halls' ?></span>
      </div>
      <span data-lucide="chevron-down" id="acc-icon-branches-halls" style="width:18px;height:18px;transition:transform 0.2s"></span>
    </div>

    <div class="accordion-body" id="acc-body-branches-halls" style="display:grid; grid-template-columns:1fr; gap:20px;">
      <form method="post" style="display:flex; flex-direction:column; gap:20px;">
        <input type="hidden" name="action" value="save_branches_halls">

        <h4 style="font-size:15px; border-bottom:1px solid var(--border); padding-bottom:8px; margin:0; color:var(--primary);">
          📍 <?= $lang === 'ar' ? 'بيانات الفروع (فرع الإسماعيلية + فرع التلاتيني)' : 'Branches Data' ?>
        </h4>

        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:16px;">
          <?php foreach ($editorBranches as $br): ?>
            <div class="card" style="padding:16px; background:var(--surface-1); border:1px solid var(--border);">
              <div style="font-weight:bold; font-size:14px; margin-bottom:12px; color:var(--primary); display:flex; align-items:center; gap:6px;">
                <span data-lucide="map-pin" style="width:16px;height:16px"></span>
                <?= h($br['name']['ar']) ?> (<?= h($br['id']) ?>)
              </div>

              <div style="display:flex; flex-direction:column; gap:10px;">
                <div>
                  <label class="field-label"><?= $lang === 'ar' ? 'اسم الفرع (عربي)' : 'Branch Name (AR)' ?></label>
                  <input type="text" name="branches[<?= h($br['id']) ?>][name_ar]" value="<?= h($br['name']['ar'] ?? '') ?>">
                </div>
                <div>
                  <label class="field-label"><?= $lang === 'ar' ? 'اسم الفرع (إنجليزي)' : 'Branch Name (EN)' ?></label>
                  <input type="text" name="branches[<?= h($br['id']) ?>][name_en]" value="<?= h($br['name']['en'] ?? '') ?>">
                </div>
                <div>
                  <label class="field-label"><?= $lang === 'ar' ? 'العنوان (عربي)' : 'Address (AR)' ?></label>
                  <input type="text" name="branches[<?= h($br['id']) ?>][address_ar]" value="<?= h($br['address']['ar'] ?? '') ?>">
                </div>
                <div>
                  <label class="field-label"><?= $lang === 'ar' ? 'العنوان (إنجليزي)' : 'Address (EN)' ?></label>
                  <input type="text" name="branches[<?= h($br['id']) ?>][address_en]" value="<?= h($br['address']['en'] ?? '') ?>">
                </div>
                <div>
                  <label class="field-label"><?= $lang === 'ar' ? 'رقم الهاتف' : 'Phone Number' ?></label>
                  <input type="text" name="branches[<?= h($br['id']) ?>][phone]" value="<?= h($br['phone'] ?? '') ?>">
                </div>
                <div>
                  <label class="field-label"><?= $lang === 'ar' ? 'مواعيد العمل (عربي)' : 'Working Hours (AR)' ?></label>
                  <input type="text" name="branches[<?= h($br['id']) ?>][hours_ar]" value="<?= h($br['hours']['ar'] ?? '') ?>">
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <h4 style="font-size:15px; border-bottom:1px solid var(--border); padding-bottom:8px; margin-top:10px; margin-bottom:0; color:var(--primary);">
          🏛️ <?= $lang === 'ar' ? 'بيانات قاعات الحجز المتاحة' : 'Available Halls Data' ?>
        </h4>

        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:16px;">
          <?php foreach ($editorHalls as $hl): 
            $brObj = find_branch($editorBranches, $hl['branchId'] ?? '');
          ?>
            <div class="card" style="padding:16px; background:var(--surface-1); border:1px solid var(--border);">
              <div style="font-weight:bold; font-size:14px; margin-bottom:4px; color:var(--text);">
                <?= h($hl['name']['ar']) ?>
              </div>
              <div style="font-size:11.5px; color:var(--gold); font-weight:600; margin-bottom:12px;">
                📍 <?= $brObj ? h($brObj['name']['ar']) : 'فرع' ?>
              </div>

              <div style="display:flex; flex-direction:column; gap:10px;">
                <div>
                  <label class="field-label"><?= $lang === 'ar' ? 'اسم القاعة (عربي)' : 'Hall Name (AR)' ?></label>
                  <input type="text" name="halls[<?= h($hl['id']) ?>][name_ar]" value="<?= h($hl['name']['ar'] ?? '') ?>">
                </div>
                <div>
                  <label class="field-label"><?= $lang === 'ar' ? 'اسم القاعة (إنجليزي)' : 'Hall Name (EN)' ?></label>
                  <input type="text" name="halls[<?= h($hl['id']) ?>][name_en]" value="<?= h($hl['name']['en'] ?? '') ?>">
                </div>
                <div>
                  <label class="field-label"><?= $lang === 'ar' ? 'نوع القاعة (عربي)' : 'Hall Type (AR)' ?></label>
                  <input type="text" name="halls[<?= h($hl['id']) ?>][type_ar]" value="<?= h($hl['type']['ar'] ?? '') ?>">
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                  <div>
                    <label class="field-label"><?= $lang === 'ar' ? 'السعة (شخص)' : 'Capacity' ?></label>
                    <input type="number" name="halls[<?= h($hl['id']) ?>][capacity]" value="<?= (int)($hl['capacity'] ?? 0) ?>">
                  </div>
                  <div>
                    <label class="field-label"><?= $lang === 'ar' ? 'السعر/ساعة (ج)' : 'Price/hr' ?></label>
                    <input type="number" name="halls[<?= h($hl['id']) ?>][price_per_hour]" value="<?= (int)($hl['pricePerHour'] ?? 0) ?>">
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div>
          <button type="submit" class="btn btn-gold" style="padding:10px 20px;">
            <span data-lucide="save" style="width:16px;height:16px"></span>
            <?= $lang === 'ar' ? 'حفظ تعديلات الفروع والقاعات' : 'Save Branches & Halls Changes' ?>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- MODE 2: LIVE CANVAS PREVIEW -->
<div id="canvasViewMode" style="display:none;">
  <div class="canvas-frame-container">
    <div class="canvas-toolbar">
      <div style="display:flex;align-items:center;gap:10px;">
        <label style="font-size:13px;font-weight:600"><?= $lang === 'ar' ? 'اختر الصفحة للمعاينة:' : 'Select Page:' ?></label>
        <select id="canvasPageSelect" onchange="changeCanvasPage(this.value)" style="padding:6px 12px;border-radius:6px;border:1px solid var(--border);background:var(--surface-1);color:var(--text);font-size:13px">
          <option value="../index.php"><?= $lang === 'ar' ? 'الرئيسية (Home)' : 'Home Page' ?></option>
          <option value="../courses.php"><?= $lang === 'ar' ? 'دليل الكورسات (Courses)' : 'Courses Catalog' ?></option>
          <option value="../halls.php"><?= $lang === 'ar' ? 'حجز القاعات (Halls)' : 'Halls Rental' ?></option>
          <option value="../portal.php"><?= $lang === 'ar' ? 'صفحة الطالب (Student Portal)' : 'Student Portal' ?></option>
        </select>
      </div>

      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <div style="display:flex; align-items:center; gap:8px; background:var(--surface-2); padding:4px 10px; border-radius:8px; border:1px solid var(--border);">
          <span style="font-size:12.5px; font-weight:700; color:var(--text-soft);" id="editModeLabel">
            👁️ <?= $lang === 'ar' ? 'وضع التصفح العادي' : 'Browsing Mode' ?>
          </span>
          <button type="button" id="toggleEditModeBtn" onclick="toggleCanvasEditMode()" class="btn btn-secondary btn-sm" style="padding:5px 12px; font-size:12px; font-weight:bold; display:inline-flex; align-items:center; gap:6px;">
            <span data-lucide="edit-3" style="width:14px;height:14px"></span>
            <span id="toggleEditModeText"><?= $lang === 'ar' ? 'تفعيل وضع التعديل' : 'Enable Edit Mode' ?></span>
          </button>
        </div>

        <button type="button" class="icon-btn" onclick="setCanvasViewport('100%')" title="Desktop"><span data-lucide="monitor" style="width:16px;height:16px"></span></button>
        <button type="button" class="icon-btn" onclick="setCanvasViewport('768px')" title="Tablet"><span data-lucide="tablet" style="width:16px;height:16px"></span></button>
        <button type="button" class="icon-btn" onclick="setCanvasViewport('375px')" title="Mobile"><span data-lucide="smartphone" style="width:16px;height:16px"></span></button>
        <a id="canvasExternalLink" href="../index.php" target="_blank" class="btn btn-secondary" style="padding:6px 12px;font-size:12px">
          <span data-lucide="external-link" style="width:14px;height:14px"></span>
          <?= $lang === 'ar' ? 'فتح الموقع نافذة مستقلة' : 'Open in New Tab' ?>
        </a>
      </div>
    </div>

    <iframe id="livePreviewIframe" class="canvas-frame" src="../index.php?lang=<?= $editLang ?>"></iframe>
  </div>
</div>

<div id="saveToast" class="save-toast">
  <span data-lucide="check-circle-2" style="width:18px;height:18px"></span>
  <span id="saveToastText"><?= $lang === 'ar' ? 'تم حفظ التعديل فوراً!' : 'Saved changes!' ?></span>
</div>

<!-- Click-to-Edit Modal for Canvas Mode -->
<div id="elementEditModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999999; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
  <div style="background:var(--surface-1, #fff); padding:24px; border-radius:14px; max-width:520px; width:92%; border:1px solid var(--border); box-shadow:0 25px 50px rgba(0,0,0,0.35);">
    <h3 style="margin-top:0; font-size:17px; display:flex; align-items:center; gap:8px; color:var(--primary);">
      <span data-lucide="edit-3" style="width:20px;height:20px"></span>
      <?= $lang === 'ar' ? 'تعديل النص المحدد بالموقع' : 'Edit Selected Site Text' ?>
    </h3>
    <p style="font-size:12.5px; color:var(--text-muted); margin-bottom:14px;">
      <?= $lang === 'ar' ? 'اكتب النص الجديد للعنوان أو الفقرة وسيطبق فوراً على موقع العميل:' : 'Type the new text to update live:' ?>
    </p>
    <textarea id="modalEditTextarea" rows="4" style="width:100%; padding:12px; border-radius:8px; border:1px solid var(--border); background:var(--surface-1); color:var(--text); font-size:14px; font-family:inherit; margin-bottom:18px;"></textarea>
    <div style="display:flex; justify-content:flex-end; gap:10px;">
      <button type="button" class="btn btn-secondary" onclick="closeElementEditModal()"><?= $lang === 'ar' ? 'إلغاء' : 'Cancel' ?></button>
      <button type="button" class="btn btn-gold" onclick="saveElementEditModal()">
        <span data-lucide="check" style="width:16px;height:16px"></span>
        <?= $lang === 'ar' ? 'حفظ التعديل المباشر' : 'Save Live Text' ?>
      </button>
    </div>
  </div>
</div>


<script>
const currentEditLang = '<?= $editLang ?>';

function switchViewMode(mode) {
  const formMode = document.getElementById('formViewMode');
  const canvasMode = document.getElementById('canvasViewMode');
  const tabFormBtn = document.getElementById('tabFormBtn');
  const tabCanvasBtn = document.getElementById('tabCanvasBtn');

  if (mode === 'canvas') {
    formMode.style.display = 'none';
    canvasMode.style.display = 'block';
    tabFormBtn.classList.remove('active');
    tabCanvasBtn.classList.add('active');
  } else {
    canvasMode.style.display = 'none';
    formMode.style.display = 'block';
    tabCanvasBtn.classList.remove('active');
    tabFormBtn.classList.add('active');
  }
}

function toggleAccordion(id) {
  const body = document.getElementById('acc-body-' + id);
  const icon = document.getElementById('acc-icon-' + id);
  if (body.style.display === 'none') {
    body.style.display = 'grid';
    icon.style.transform = 'rotate(0deg)';
  } else {
    body.style.display = 'none';
    icon.style.transform = 'rotate(-90deg)';
  }
}

function filterSiteStrings() {
  const query = document.getElementById('siteStringSearch').value.toLowerCase().trim();
  const items = document.querySelectorAll('[data-string-item]');

  items.forEach(item => {
    const text = item.getAttribute('data-search-text') || '';
    if (query === '' || text.includes(query)) {
      item.style.display = 'flex';
    } else {
      item.style.display = 'none';
    }
  });

  // Expand all accordions when searching
  if (query !== '') {
    document.querySelectorAll('.accordion-body').forEach(b => b.style.display = 'grid');
  }
}

function autoSaveField(key, value) {
  const formData = new FormData();
  formData.append('action', 'api_save_key');
  formData.append('key', key);
  formData.append('value', value);
  formData.append('lang', currentEditLang);

  fetch('site_editor.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      showToast('<?= $lang === 'ar' ? "تم تحديث " : "Updated " ?> (' + key + ')');
      // Refresh iframe preview if open
      const iframe = document.getElementById('livePreviewIframe');
      if (iframe && iframe.contentWindow) {
        iframe.contentWindow.location.reload();
      }
    }
  })
  .catch(err => console.error('Save error:', err));
}

function submitMainBatchForm() {
  document.getElementById('batchSaveForm').submit();
}

function showToast(msg) {
  const toast = document.getElementById('saveToast');
  const text = document.getElementById('saveToastText');
  text.textContent = msg;
  toast.classList.add('show');
  setTimeout(() => {
    toast.classList.remove('show');
  }, 2500);
}

function changeCanvasPage(page) {
  const iframe = document.getElementById('livePreviewIframe');
  const extLink = document.getElementById('canvasExternalLink');
  const targetUrl = page + '?lang=' + currentEditLang;
  iframe.src = targetUrl;
  extLink.href = targetUrl;
}

function setCanvasViewport(width) {
  const iframe = document.getElementById('livePreviewIframe');
  iframe.style.maxWidth = width;
  iframe.style.margin = '0 auto';
}

// Canvas Interactive Click-to-Edit Setup
let activeTargetEl = null;
let activeOldText = '';
let isEditModeEnabled = false;
const flatTranslationsMap = <?= json_encode($flatTranslations) ?>;

function toggleCanvasEditMode() {
  isEditModeEnabled = !isEditModeEnabled;
  const btn = document.getElementById('toggleEditModeBtn');
  const txt = document.getElementById('toggleEditModeText');
  const lbl = document.getElementById('editModeLabel');
  const iframe = document.getElementById('livePreviewIframe');

  if (isEditModeEnabled) {
    if (btn) btn.className = 'btn btn-gold btn-sm';
    if (txt) txt.textContent = '<?= $lang === 'ar' ? 'إيقاف التعديل (العودة للتصفح)' : 'Disable Edit Mode' ?>';
    if (lbl) lbl.textContent = '✏️ <?= $lang === 'ar' ? 'وضع التعديل مفعل' : 'Edit Mode Active' ?>';
    showToast('<?= $lang === 'ar' ? "تم تفعيل وضع التعديل! اضغط على أي نص بالموقع لتعديله" : "Edit Mode Active — Click any text to edit" ?>');
  } else {
    if (btn) btn.className = 'btn btn-secondary btn-sm';
    if (txt) txt.textContent = '<?= $lang === 'ar' ? 'تفعيل وضع التعديل' : 'Enable Edit Mode' ?>';
    if (lbl) lbl.textContent = '👁️ <?= $lang === 'ar' ? 'وضع التصفح العادي' : 'Browsing Mode' ?>';
    showToast('<?= $lang === 'ar' ? "تم العودة لوضع التصفح العادي" : "Returned to Browsing Mode" ?>');
  }

  if (iframe && iframe.contentDocument && iframe.contentDocument.body) {
    if (isEditModeEnabled) {
      iframe.contentDocument.body.classList.add('lct-edit-active');
    } else {
      iframe.contentDocument.body.classList.remove('lct-edit-active');
    }
  }
}

function findKeyByText(text) {
  text = text.trim();
  for (const [key, val] of Object.entries(flatTranslationsMap)) {
    if (val.trim() === text) return key;
  }
  return '';
}

function setupIframeInteractiveEditing() {
  const iframe = document.getElementById('livePreviewIframe');
  if (!iframe) return;

  iframe.onload = function() {
    try {
      const doc = iframe.contentDocument || iframe.contentWindow.document;
      if (!doc) return;

      // Inject hover styles (active only when body has lct-edit-active class)
      const style = doc.createElement('style');
      style.textContent = `
        body.lct-edit-active [data-lct-editable]:hover {
          outline: 2px dashed #0284c7 !important;
          outline-offset: 3px !important;
          cursor: pointer !important;
          box-shadow: 0 0 12px rgba(2,132,199,0.35) !important;
          border-radius: 4px;
          transition: outline 0.1s;
        }
        body.lct-edit-active [data-lct-editable]:hover::after {
          content: '✏️ عدّل';
          position: absolute;
          background: #0284c7;
          color: #fff;
          font-size: 11px;
          padding: 2px 7px;
          border-radius: 4px;
          margin-top: -22px;
          pointer-events: none;
          z-index: 99999;
          font-family: sans-serif;
        }
      `;
      doc.head.appendChild(style);

      if (isEditModeEnabled && doc.body) {
        doc.body.classList.add('lct-edit-active');
      }

      // Mark all visible text-bearing elements
      const selectors = [
        'h1','h2','h3','h4','h5','h6',
        'p',
        'span:not(:has(span))',
        'a:not(:has(h1,h2,h3,h4))',
        'button',
        'label',
        'li',
        'td','th',
        'div.hero-stat-label',
        'div.footer-link',
        'div.dept-card-name',
        'div.dept-card-tag'
      ].join(', ');

      doc.querySelectorAll(selectors).forEach(el => {
        const text = el.innerText?.trim();
        if (!text || text.length < 2 || text.length > 400) return; // skip empty or huge blocks
        if (el.children.length > 2) return; // skip wrappers with many children

        el.setAttribute('data-lct-editable', '1');
        el.style.position = 'relative'; // for ::after pseudo-element to show

        el.addEventListener('click', function(e) {
          if (!isEditModeEnabled) {
            // Browsing Mode: Allow normal click & page navigation!
            return;
          }
          // Edit Mode: Intercept click to open live edit modal
          e.preventDefault();
          e.stopPropagation();
          activeTargetEl = el;
          activeOldText = el.innerText.trim();
          document.getElementById('modalEditTextarea').value = activeOldText;
          const modal = document.getElementById('elementEditModal');
          modal.style.display = 'flex';
          document.getElementById('modalEditTextarea').focus();
        });
      });
    } catch(e) {
      console.warn('Click-to-edit setup error:', e);
    }
  };
}


function closeElementEditModal() {
  document.getElementById('elementEditModal').style.display = 'none';
  activeTargetEl = null;
  activeOldText = '';
}

function saveElementEditModal() {
  const newText = document.getElementById('modalEditTextarea').value.trim();
  if (activeTargetEl && newText !== '') {
    activeTargetEl.innerText = newText;
    const oldText = activeOldText;
    const matchedKey = findKeyByText(oldText);

    const formData = new FormData();
    formData.append('action', 'api_save_key');
    formData.append('key', matchedKey);
    formData.append('old_value', oldText);
    formData.append('value', newText);
    formData.append('lang', currentEditLang);

    fetch('site_editor.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        showToast('<?= $lang === 'ar' ? "تم حفظ التعديل بنجاح!" : "Saved text!" ?>');
        const iframe = document.getElementById('livePreviewIframe');
        if (iframe && iframe.contentWindow) {
          iframe.contentWindow.location.reload();
        }
      }
    });
  }
  closeElementEditModal();
}

// Initial Auto-Switch Mode Check
document.addEventListener('DOMContentLoaded', function() {
  setupIframeInteractiveEditing();

  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('view') === 'canvas') {
    switchViewMode('canvas');
  }
});
</script>


<?php require __DIR__ . '/../includes/admin_shell_end.php'; ?>

