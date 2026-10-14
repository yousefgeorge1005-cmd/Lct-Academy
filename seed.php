<?php
// افتح الرابط ده مرة واحدة بس من المتصفح بعد أول رفع للموقع، عشان ينشئ ملفات
// البيانات الأولية (courses.json, halls.json, settings.json, admin.json).
// آمن تسيبه بعد كده — مش بيكتب فوق أي ملف موجود بالفعل، فمش هيمسح أي بيانات
// حقيقية لو اتفتح تاني بالغلط.
require_once __DIR__ . '/includes/functions.php';

function seed_courses() {
    return [
        ['id'=>'c1','code'=>'LANG-EN-01','category'=>'language','icon'=>'languages','status'=>'active',
         'name'=>['ar'=>'اللغة الإنجليزية العامة والمحادثة','en'=>'General English & Conversation'],
         'tagline'=>['ar'=>'من الصفر لثقة كاملة في الكلام','en'=>'From zero to confident conversation'],
         'desc'=>['ar'=>'كورس شامل لبناء أساس قوي في القواعد والمفردات، مع تركيز كبير على المحادثة والاستماع من أول أسبوع، عشان تقدر تتكلم إنجليزي بثقة في شغلك وحياتك اليومية.','en'=>'A comprehensive course that builds a solid foundation in grammar and vocabulary, with heavy focus on speaking and listening from week one, so you can speak English confidently at work and in daily life.'],
         'level'=>['ar'=>'مبتدئ إلى متوسط','en'=>'Beginner to Intermediate'],
         'duration'=>['ar'=>'8 أسابيع','en'=>'8 weeks'],
         'price'=>1800,
         'instructor'=>['ar'=>'أ. مريم عادل','en'=>'Ms. Mariam Adel'],
         'schedule'=>['ar'=>'السبت والاثنين والأربعاء، 5:00 – 7:00 مساءً','en'=>'Sat, Mon, Wed, 5:00 – 7:00 PM'],
         'seatsTotal'=>16,'seatsBooked'=>11,
         'curriculum'=>['ar'=>['أساسيات القواعد والأزمنة','بناء مفردات يومية عملية','تدريبات محادثة جماعية أسبوعية','مهارات الاستماع والنطق','مشروع تقديم نهائي بالإنجليزي'],
                        'en'=>['Grammar & tenses fundamentals','Practical everyday vocabulary','Weekly group conversation practice','Listening & pronunciation skills','Final English presentation project']],
         'prerequisites'=>['ar'=>'لا يوجد','en'=>'None']],

        ['id'=>'c2','code'=>'LANG-EN-02','category'=>'language','icon'=>'award','status'=>'active',
         'name'=>['ar'=>'تحضير اختبار الآيلتس (IELTS)','en'=>'IELTS Exam Preparation'],
         'tagline'=>['ar'=>'استعد لدرجة تفتحلك أبواب السفر والدراسة','en'=>'Prepare for the score that opens study & travel doors'],
         'desc'=>['ar'=>'كورس مكثف مخصص لاجتياز اختبار الآيلتس، بيغطي المهارات الأربعة (قراءة، كتابة، استماع، محادثة) مع اختبارات تجريبية دورية وتقييم فردي لنقاط الضعف.','en'=>'An intensive course dedicated to passing the IELTS exam, covering all four skills with regular mock tests and individual feedback on weak points.'],
         'level'=>['ar'=>'متوسط إلى متقدم','en'=>'Intermediate to Advanced'],
         'duration'=>['ar'=>'6 أسابيع','en'=>'6 weeks'],
         'price'=>2500,
         'instructor'=>['ar'=>'أ. كريم فتحي','en'=>'Mr. Karim Fathy'],
         'schedule'=>['ar'=>'الأحد والثلاثاء والخميس، 6:00 – 8:30 مساءً','en'=>'Sun, Tue, Thu, 6:00 – 8:30 PM'],
         'seatsTotal'=>14,'seatsBooked'=>14,
         'curriculum'=>['ar'=>['استراتيجيات قسم القراءة','تقنيات الكتابة الأكاديمية','مهارات الاستماع المكثف','محاكاة اختبار المحادثة','اختبار تجريبي كامل كل أسبوعين'],
                        'en'=>['Reading section strategies','Academic writing techniques','Intensive listening skills','Speaking test simulation','Full mock exam every two weeks']],
         'prerequisites'=>['ar'=>'مستوى متوسط في الإنجليزية على الأقل','en'=>'At least an intermediate English level']],

        ['id'=>'c3','code'=>'LANG-FR-01','category'=>'language','icon'=>'languages','status'=>'active',
         'name'=>['ar'=>'اللغة الفرنسية (من الصفر)','en'=>'French Language (From Scratch)'],
         'tagline'=>['ar'=>'أول خطوة لعالم الفرنكوفونية','en'=>'Your first step into the Francophone world'],
         'desc'=>['ar'=>'تعلم الفرنسية بأسلوب تفاعلي بيربط بين القواعد والمحادثة اليومية، مناسب تمامًا للمبتدئين اللي عايزين يبنوا أساس صح من البداية.','en'=>'Learn French through an interactive approach connecting grammar with everyday conversation — perfect for beginners.'],
         'level'=>['ar'=>'مبتدئ (A1-A2)','en'=>'Beginner (A1-A2)'],
         'duration'=>['ar'=>'10 أسابيع','en'=>'10 weeks'],
         'price'=>2000,
         'instructor'=>['ar'=>'أ. نور الهدى سامي','en'=>'Ms. Nour Elhoda Sami'],
         'schedule'=>['ar'=>'السبت والثلاثاء، 4:00 – 6:00 مساءً','en'=>'Sat, Tue, 4:00 – 6:00 PM'],
         'seatsTotal'=>18,'seatsBooked'=>6,
         'curriculum'=>['ar'=>['الأبجدية والنطق الصحيح','التحيات والتعارف','قواعد الأزمنة الأساسية','محادثات الحياة اليومية','مشاهد تفاعلية وأفلام قصيرة'],
                        'en'=>['Alphabet & correct pronunciation','Greetings & introductions','Basic tense grammar','Everyday life conversations','Interactive scenes & short films']],
         'prerequisites'=>['ar'=>'لا يوجد','en'=>'None']],

        ['id'=>'c4','code'=>'LANG-DE-01','category'=>'language','icon'=>'languages','status'=>'active',
         'name'=>['ar'=>'اللغة الألمانية (مستوى A1)','en'=>'German Language (Level A1)'],
         'tagline'=>['ar'=>'مناسب لخطط الهجرة والدراسة في ألمانيا','en'=>'Ideal for study & migration plans to Germany'],
         'desc'=>['ar'=>'أساسيات اللغة الألمانية بمعايير معتمدة، تحضيرًا لاختبارات الهجرة أو الدراسة، مع تدريب مكثف على النطق الصحيح من البداية.','en'=>'German language fundamentals to accredited standards, preparing you for migration or study exams.'],
         'level'=>['ar'=>'مبتدئ (A1)','en'=>'Beginner (A1)'],
         'duration'=>['ar'=>'12 أسبوع','en'=>'12 weeks'],
         'price'=>2400,
         'instructor'=>['ar'=>'أ. عمر الشريف','en'=>'Mr. Omar Elsherif'],
         'schedule'=>['ar'=>'الأحد والأربعاء، 5:00 – 7:00 مساءً','en'=>'Sun, Wed, 5:00 – 7:00 PM'],
         'seatsTotal'=>16,'seatsBooked'=>9,
         'curriculum'=>['ar'=>['أساسيات النطق والأبجدية','مفردات المواقف اليومية','تركيب الجملة الألماني','تدريب على اختبار Start Deutsch 1','محادثات تطبيقية موجهة'],
                        'en'=>['Pronunciation & alphabet basics','Everyday situational vocabulary','German sentence structure','Start Deutsch 1 exam practice','Guided applied conversations']],
         'prerequisites'=>['ar'=>'لا يوجد','en'=>'None']],

        ['id'=>'c5','code'=>'COMP-OF-01','category'=>'computer','icon'=>'monitor','status'=>'active',
         'name'=>['ar'=>'حزمة مايكروسوفت أوفيس الاحترافية','en'=>'Microsoft Office Professional'],
         'tagline'=>['ar'=>'Word و Excel و PowerPoint من الأساسيات للاحتراف','en'=>'Word, Excel & PowerPoint from basics to pro'],
         'desc'=>['ar'=>'كورس عملي شامل على برامج Word وExcel وPowerPoint، مع تطبيقات حقيقية من بيئة العمل زي التقارير والمعادلات والعروض التقديمية الاحترافية.','en'=>'A comprehensive hands-on course covering Word, Excel and PowerPoint with real workplace applications.'],
         'level'=>['ar'=>'مبتدئ إلى متوسط','en'=>'Beginner to Intermediate'],
         'duration'=>['ar'=>'6 أسابيع','en'=>'6 weeks'],
         'price'=>1500,
         'instructor'=>['ar'=>'أ. ياسمين طارق','en'=>'Ms. Yasmin Tarek'],
         'schedule'=>['ar'=>'السبت والاثنين، 3:00 – 5:00 مساءً','en'=>'Sat, Mon, 3:00 – 5:00 PM'],
         'seatsTotal'=>14,'seatsBooked'=>5,
         'curriculum'=>['ar'=>['تنسيق المستندات الاحترافية في Word','المعادلات والدوال الأساسية في Excel','الجداول المحورية وتحليل البيانات','تصميم عروض PowerPoint مؤثرة','مشروع تطبيقي متكامل'],
                        'en'=>['Professional document formatting in Word','Core formulas & functions in Excel','Pivot tables & data analysis','Designing impactful PowerPoint decks','Integrated final project']],
         'prerequisites'=>['ar'=>'إلمام أساسي باستخدام الكمبيوتر','en'=>'Basic computer familiarity']],

        ['id'=>'c6','code'=>'COMP-GD-01','category'=>'computer','icon'=>'image','status'=>'active',
         'name'=>['ar'=>'التصميم الجرافيكي (فوتوشوب وإليستريتور)','en'=>'Graphic Design (Photoshop & Illustrator)'],
         'tagline'=>['ar'=>'حوّل أفكارك لتصميمات احترافية','en'=>'Turn your ideas into professional designs'],
         'desc'=>['ar'=>'تعلم أساسيات وأدوات فوتوشوب وإليستريتور خطوة بخطوة، وابني بورتفوليو حقيقي من مشاريع تصميم هوية بصرية وسوشيال ميديا.','en'=>'Learn Photoshop and Illustrator tools step by step, and build a real portfolio.'],
         'level'=>['ar'=>'مبتدئ','en'=>'Beginner'],
         'duration'=>['ar'=>'8 أسابيع','en'=>'8 weeks'],
         'price'=>2200,
         'instructor'=>['ar'=>'أ. مصطفى جمال','en'=>'Mr. Mostafa Gamal'],
         'schedule'=>['ar'=>'الأحد والثلاثاء، 4:00 – 6:30 مساءً','en'=>'Sun, Tue, 4:00 – 6:30 PM'],
         'seatsTotal'=>12,'seatsBooked'=>8,
         'curriculum'=>['ar'=>['أساسيات فوتوشوب وتحرير الصور','الرسم بالمتجهات في إليستريتور','تصميم الهوية البصرية واللوجو','تصميمات السوشيال ميديا','بناء بورتفوليو نهائي'],
                        'en'=>['Photoshop fundamentals & photo editing','Vector drawing in Illustrator','Brand identity & logo design','Social media design','Building a final portfolio']],
         'prerequisites'=>['ar'=>'لا يوجد','en'=>'None']],

        ['id'=>'c7','code'=>'COMP-WD-01','category'=>'computer','icon'=>'layers','status'=>'active',
         'name'=>['ar'=>'تطوير الويب (HTML وCSS وJavaScript)','en'=>'Web Development (HTML, CSS & JavaScript)'],
         'tagline'=>['ar'=>'ابني موقعك الأول بإيدك','en'=>'Build your first website with your own hands'],
         'desc'=>['ar'=>'كورس عملي 100% لتعلم بناء مواقع الويب من الصفر، من هيكلة الصفحات بـHTML، للتنسيق بـCSS، للتفاعل الحقيقي بـJavaScript.','en'=>'A 100% hands-on course to learn web building from scratch.'],
         'level'=>['ar'=>'مبتدئ','en'=>'Beginner'],
         'duration'=>['ar'=>'10 أسابيع','en'=>'10 weeks'],
         'price'=>2800,
         'instructor'=>['ar'=>'م. أحمد رضا','en'=>'Eng. Ahmed Reda'],
         'schedule'=>['ar'=>'السبت والاثنين والأربعاء، 6:00 – 8:00 مساءً','en'=>'Sat, Mon, Wed, 6:00 – 8:00 PM'],
         'seatsTotal'=>14,'seatsBooked'=>12,
         'curriculum'=>['ar'=>['هيكلة الصفحات بـHTML5','التنسيق والتخطيط بـCSS3','أساسيات JavaScript والتفاعلية','بناء موقع متجاوب Responsive','مشروع تخرج: موقع شخصي كامل'],
                        'en'=>['Page structuring with HTML5','Styling & layout with CSS3','JavaScript fundamentals','Building a responsive website','Capstone project']],
         'prerequisites'=>['ar'=>'لا يوجد','en'=>'None']],

        ['id'=>'c8','code'=>'COMP-PY-01','category'=>'computer','icon'=>'sparkles','status'=>'active',
         'name'=>['ar'=>'البرمجة بلغة بايثون للمبتدئين','en'=>'Python Programming for Beginners'],
         'tagline'=>['ar'=>'أول لغة برمجة لعالم الداتا والذكاء الاصطناعي','en'=>'Your first language into data & AI'],
         'desc'=>['ar'=>'مقدمة عملية قوية للبرمجة باستخدام بايثون، من الصفر لحد بناء مشاريع حقيقية زي أدوات تحليل بيانات بسيطة وألعاب صغيرة.','en'=>'A strong hands-on introduction to programming with Python, from zero to building real projects.'],
         'level'=>['ar'=>'مبتدئ','en'=>'Beginner'],
         'duration'=>['ar'=>'8 أسابيع','en'=>'8 weeks'],
         'price'=>2600,
         'instructor'=>['ar'=>'م. سارة حسام','en'=>'Eng. Sara Hossam'],
         'schedule'=>['ar'=>'الأحد والخميس، 5:00 – 7:30 مساءً','en'=>'Sun, Thu, 5:00 – 7:30 PM'],
         'seatsTotal'=>14,'seatsBooked'=>3,
         'curriculum'=>['ar'=>['أساسيات البرمجة والمتغيرات','الجمل الشرطية والحلقات التكرارية','الدوال والتعامل مع البيانات','مقدمة في مكتبات تحليل البيانات','مشروع تطبيقي نهائي'],
                        'en'=>['Programming basics & variables','Conditionals & loops','Functions & working with data','Intro to data analysis libraries','Final applied project']],
         'prerequisites'=>['ar'=>'لا يوجد','en'=>'None']],
    ];
}

function seed_halls() {
    return [
        ['id'=>'h1','branchId'=>'br1','name'=>['ar'=>'قاعة النور','en'=>'Al Noor Hall'],'type'=>['ar'=>'قاعة لغات','en'=>'Language Hall'],'capacity'=>20,'pricePerHour'=>150,'amenities'=>['smartboard'=>true,'projector'=>false,'ac'=>true],'slots'=>[]],
        ['id'=>'h2','branchId'=>'br1','name'=>['ar'=>'قاعة الإبداع','en'=>'Al Ibda Hall'],'type'=>['ar'=>'قاعة لغات','en'=>'Language Hall'],'capacity'=>16,'pricePerHour'=>120,'amenities'=>['smartboard'=>false,'projector'=>false,'ac'=>true],'slots'=>[]],
        ['id'=>'h3','branchId'=>'br2','name'=>['ar'=>'معمل الحاسب الآلي','en'=>'Computer Lab'],'type'=>['ar'=>'معمل كمبيوتر','en'=>'Computer Lab'],'capacity'=>14,'pricePerHour'=>200,'amenities'=>['smartboard'=>false,'projector'=>true,'ac'=>true],'slots'=>[]],
    ];
}

function seed_settings() {
    return [
        'branches' => [
            ['id'=>'br1','name'=>['ar'=>'فرع الإسماعيلية','en'=>'Ismailia Branch'],
             'address'=>['ar'=>'شارع شبيين برج بانوراما أمام الموقف','en'=>'Sharabin St., Panorama Tower, Opposite Bus Station'],
             'phone'=>'01000000000','email'=>'ismailia@lct-academy.eg',
             'hours'=>['ar'=>'9:00 صباحاً – 9:00 مساءً','en'=>'9:00 AM – 9:00 PM']],
            ['id'=>'br2','name'=>['ar'=>'فرع التلاتيني','en'=>'El-Talateeny Branch'],
             'address'=>['ar'=>'عند محمصة الدراويش','en'=>'Near Al-Darawish Roastery'],
             'phone'=>'01000000001','email'=>'talateeny@lct-academy.eg',
             'hours'=>['ar'=>'9:00 صباحاً – 9:00 مساءً','en'=>'9:00 AM – 9:00 PM']],
        ],
    ];
}

if (!file_exists(COURSES_FILE)) { save_courses(seed_courses()); echo "courses.json created\n"; } else { echo "courses.json already exists, skipped\n"; }
if (!file_exists(HALLS_FILE)) { save_halls(seed_halls()); echo "halls.json created\n"; } else { echo "halls.json already exists, skipped\n"; }
if (!file_exists(SETTINGS_FILE)) { save_settings(seed_settings()); echo "settings.json created (branches)\n"; } else { echo "settings.json already exists, skipped\n"; }
if (!file_exists(ADMIN_FILE)) {
    save_admin_users([
        ['role' => 'admin', 'password_hash' => password_hash('LCT-2026', PASSWORD_DEFAULT)],
        ['role' => 'manager', 'password_hash' => password_hash('LCTMANAGER2026', PASSWORD_DEFAULT)],
    ]);
    echo "admin.json created (admin PIN: LCT-2026, manager PIN: LCTMANAGER2026 — please change both after first login)\n";
} else {
    echo "admin.json already exists, skipped\n";
}
if (!file_exists(BLOCKED_NUMBERS_FILE)) { save_blocked_numbers([]); echo "blocked_numbers.json created\n"; } else { echo "blocked_numbers.json already exists, skipped\n"; }
if (!file_exists(CLOUDINARY_CREDENTIALS_FILE)) {
    echo "NOTE: cloudinary_credentials.json is missing — ID photo uploads will fall back to local storage until you add it.\n";
}
