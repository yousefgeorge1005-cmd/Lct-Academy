<?php

function translations($lang) {
    $ar = [
        'dir' => 'rtl',
        'brand' => ['name' => 'LCT', 'tag' => 'طريقك للنجاح'],
        'nav' => ['home' => 'الرئيسية', 'courses' => 'الكورسات', 'halls' => 'حجز القاعات', 'myPage' => 'صفحتي'],
        'hero' => [
            'eyebrow' => 'أكاديمية معتمدة لتدريب اللغات والكمبيوتر',
            'title1' => 'طريقك', 'title2' => 'للنجاح',
            'subtitle' => 'كورسات لغات وكمبيوتر بإشراف مدربين متخصصين، وحجز إلكتروني موثّق يضمن مكانك بمصداقية كاملة من أول خطوة.',
            'ctaCourses' => 'تصفح الكورسات', 'ctaLookup' => 'تابع حالة حجزي',
            'trust1' => 'توثيق هوية إجباري', 'trust2' => 'مراجعة يدوية قبل التأكيد', 'trust3' => 'صفحة خاصة لكل طالب',
            'stat1n' => '+8', 'stat1l' => 'برامج تدريبية', 'stat2n' => '+30', 'stat2l' => 'دفعة متخرجة', 'stat3n' => '100%', 'stat3l' => 'حجوزات موثّقة',
        ],
        'depts' => [
            'eyebrow' => 'الأقسام التدريبية', 'title' => 'اختر قسمك',
            'sub' => 'برنامجان أساسيان، وداخل كل واحد مسارات متدرجة بمستويات مختلفة',
            'langTitle' => 'قسم اللغات', 'langDesc' => 'دورات مكثفة في أشهر اللغات الحية، بمستويات متدرجة من الصفر وحتى الاحتراف.',
            'compTitle' => 'قسم الكمبيوتر', 'compDesc' => 'برامج عملية تؤهلك لسوق العمل في التصميم والبرمجة والتطبيقات المكتبية.',
            'viewAll' => 'عرض كل الكورسات', 'seatsLeft' => 'مقعد متاح', 'seatsFull' => 'اكتمل العدد',
        ],
        'why' => [
            'eyebrow' => 'لماذا LCT', 'title' => 'ضمانات مسجّلة لكل طالب', 'sub' => 'نظام حجز مبني على المصداقية أولًا، مش بس السهولة',
            'f1t' => 'حجز موثّق بالكامل', 'f1d' => 'صورة البطاقة الشخصية إلزامية مع كل استمارة، ولا يُعتمد أي حجز إلا بعد مراجعة فريق الإدارة يدويًا.',
            'f2t' => 'مقاعد حقيقية', 'f2d' => 'عدّاد مقاعد حي لكل كورس ولكل قاعة، فمفيش حجز بعد اكتمال العدد المتاح فعليًا.',
            'f3t' => 'صفحة الطالب', 'f3d' => 'كل طالب له صفحة خاصة يتابع منها كورساته وحالة كل حجز أولًا بأول، بدون ما يتصل يسأل.',
            'f4t' => 'إدارة شاملة', 'f4d' => 'لوحة تحكم واحدة لإدارة الكورسات ومواعيد القاعات وكل الحجوزات، مع تصدير البيانات لملف Excel.',
        ],
        'ctaBand' => ['title' => 'جاهز تبدأ رحلتك؟', 'sub' => 'احجز مكانك دلوقتي، وفريقنا هيتواصل معاك لتأكيد الحجز خلال 24 ساعة.', 'btn' => 'احجز مكانك الآن'],
        'footer' => [
            'desc' => 'أكاديمية LCT لتدريب اللغات والكمبيوتر — تسجيل موثّق، ومتابعة كاملة لكل طالب من أول يوم.',
            'quick' => 'روابط سريعة', 'contact' => 'تواصل معنا', 'rights' => 'جميع الحقوق محفوظة',
        ],
        'coursesPage' => [
            'title' => 'دليل الكورسات', 'sub' => 'افلتر حسب القسم أو دور على اسم الكورس اللي يهمك',
            'searchPh' => 'ابحث باسم الكورس...', 'all' => 'الكل', 'language' => 'اللغات', 'computer' => 'الكمبيوتر',
            'noResults' => 'مفيش كورسات مطابقة للبحث',
        ],
        'course' => [
            'level' => 'المستوى', 'duration' => 'المدة', 'schedule' => 'المواعيد', 'instructor' => 'المدرب',
            'price' => 'السعر', 'seats' => 'المقاعد المتاحة', 'prerequisites' => 'المتطلبات السابقة',
            'curriculum' => 'محاور الكورس', 'bookThis' => 'احجز هذا الكورس', 'backToCourses' => 'رجوع لكل الكورسات',
            'code' => 'كود الكورس', 'egp' => 'جنيه', 'perCourse' => 'لكامل الكورس',
        ],
        'status' => ['pending' => 'قيد المراجعة', 'confirmed' => 'مؤكد', 'rejected' => 'مرفوض', 'completed' => 'مكتمل', 'cancelled' => 'ملغي'],
        'booking' => [
            'title' => 'استمارة التسجيل', 'stepLabels' => ['البيانات الشخصية', 'توثيق الهوية', 'المراجعة والتأكيد'],
            'course' => 'الكورس المحجوز', 'fullName' => 'الاسم بالكامل', 'fullNamePh' => 'اكتب اسمك رباعي زي ما هو في البطاقة',
            'phone' => 'رقم الموبايل', 'phonePh' => '01xxxxxxxxx', 'email' => 'البريد الإلكتروني', 'emailPh' => 'example@email.com',
            'optional' => 'اختياري', 'nationalId' => 'الرقم القومي',
            'notes' => 'ملاحظات إضافية', 'notesPh' => 'أي حاجة حابب تقولهالنا قبل الحجز...',
            'branch' => 'الفرع', 'selectBranch' => 'اختر الفرع الذي ستحضر فيه',
            'idCardTitle' => 'صورة البطاقة الشخصية',
            'idCardHint' => 'مطلوبة إجباريًا لتوثيق الحجز ومنع الحجوزات الوهمية. الصورة بتتشاف من فريق الإدارة بس للمراجعة.',
            'idCardUpload' => 'ارفع صورة البطاقة', 'idCardDrag' => 'اسحب الصورة هنا أو اضغط للاختيار',
            'idCardFormats' => 'JPG أو PNG — أقل من 8 ميجا',
            'agreeLabel' => 'أقر بأن البيانات المدخلة صحيحة وأتحمل مسؤولية أي بيانات غير صحيحة',
            'reviewNote' => 'بعد الإرسال، هيتحول حجزك لحالة "قيد المراجعة" لحد ما فريقنا يراجع البيانات وصورة البطاقة ويأكد الحجز.',
            'back' => 'السابق', 'next' => 'التالي', 'submit' => 'إرسال الحجز',
            'successTitle' => 'تم استلام حجزك', 'successDesc' => 'حجزك دلوقتي قيد المراجعة، وهتلاقي حالته محدثة في صفحتك الخاصة بمجرد ما فريقنا يراجعه.',
            'successRef' => 'رقم مرجع الحجز', 'goToMyPage' => 'روح لصفحتي', 'backHome' => 'رجوع للرئيسية',
            'err' => [
                'required' => 'الحقل ده مطلوب', 'phone' => 'رقم موبايل مصري غير صحيح (لازم يبدأ بـ 01 و11 رقم)',
                'nationalId' => 'الرقم القومي لازم يكون 14 رقم', 'idCard' => 'لازم ترفع صورة البطاقة عشان تكمل الحجز',
                'agree' => 'لازم توافق على إقرار صحة البيانات', 'email' => 'بريد إلكتروني غير صحيح',
                'duplicate' => 'عندك حجز بنفس الرقم على نفس الكورس مقدم بالفعل وقيد المراجعة',
                'fileType' => 'الصورة لازم تكون JPG أو PNG', 'fileSize' => 'حجم الصورة كبير أوي، اختار صورة أصغر من 8 ميجا',
                'seatsFull' => 'للأسف المقاعد اكتملت في الكورس ده', 'general' => 'حصل خطأ، حاول تاني.',
                'blocked' => 'مش قادرين نكمل الحجز بالرقم ده. تواصل معانا مباشرة لو في استفسار.',
            ],
        ],
        'halls' => [
            'title' => 'حجز القاعات', 'sub' => 'قاعاتنا متاحة للتأجير للفعاليات والتدريبات الخارجية — اختار التاريخ والوقت المناسبين واحجزهم بنفس آلية التوثيق.',
            'capacity' => 'السعة', 'noSlots' => 'مفيش قاعات في هذا الفرع حاليًا',
            'bookNow' => 'احجز هذه القاعة', 'backToHalls' => 'رجوع لكل القاعات', 'bookingTitle' => 'حجز قاعة',
            'selectedHall' => 'القاعة المختارة', 'personCount' => 'عدد الأشخاص المتوقع', 'purpose' => 'الغرض من الحجز',
            'purposePh' => 'مثال: ورشة عمل، اجتماع، دورة تدريبية خارجية...',
            'bookingDate' => 'تاريخ الحجز', 'startTime' => 'من الساعة', 'endTime' => 'إلى الساعة',
            'pricePerHour' => 'سعر الساعة', 'estimatedCost' => 'التكلفة التقريبية',
            'egpPerHour' => 'جنيه/ساعة', 'selectBranch' => 'اختر الفرع',
            'allBranches' => 'كل الفروع', 'branch' => 'الفرع',
            'amenitySmartboard' => 'سبورة ذكية', 'amenityProjector' => 'بروجيكتور', 'amenityAc' => 'مكيفة',
            'conflictError' => 'هذا الوقت مش متاح في القاعة دي، اختار وقت تاني.',
            'dateError' => 'التاريخ لازم يكون من بكره فأكتر.',
            'timeError' => 'وقت الانتهاء لازم يكون بعد وقت البداية.',
            'statusNote' => [
                'pending' => 'حجزك قيد المراجعة من فريق الإدارة دلوقتي.',
                'confirmed' => 'تم تأكيد حجز القاعة رسميًا. اتشرفنا بيك!',
                'rejected' => 'للأسف الحجز ده اترفض. تواصل معانا لمعرفة السبب.',
                'cancelled' => 'تم إلغاء هذا الحجز.',
            ],
        ],
        'portal' => [
            'title' => 'صفحة الطالب', 'sub' => 'ادخل رقم الموبايل اللي حجزت بيه أو رقم المرجع (اللي وصلك بعد الحجز) عشان تتابع كل حجوزاتك',
            'phonePh' => 'رقم الموبايل أو رقم المرجع (مثال LCT-123456)', 'searchBtn' => 'عرض حجوزاتي',
            'notFoundTitle' => 'مفيش حجوزات بالرقم ده', 'notFoundDesc' => 'تأكد من رقم الموبايل أو رقم المرجع، أو احجز أول كورس ليك دلوقتي',
            'welcomeBack' => 'أهلاً بيك', 'myBookings' => 'حجوزاتي', 'bookedOn' => 'تاريخ الحجز',
            'newBooking' => 'حجز كورس جديد', 'hallBookingLabel' => 'حجز قاعة',
            'statusNote' => [
                'pending' => 'حجزك قيد المراجعة من فريق الإدارة دلوقتي.', 'confirmed' => 'تم تأكيد حجزك رسميًا. اتشرفنا بيك معانا!',
                'rejected' => 'للأسف الحجز ده اترفض. تواصل معانا لمعرفة السبب.', 'completed' => 'مبروك، خلصت ده بنجاح.',
                'cancelled' => 'تم إلغاء هذا الحجز. تواصل معانا لو عندك أي استفسار.',
            ],
        ],
        'common' => [
            'close' => 'إغلاق', 'cancel' => 'إلغاء', 'save' => 'حفظ', 'delete' => 'حذف', 'edit' => 'تعديل',
            'add' => 'إضافة', 'search' => 'بحث', 'all' => 'الكل', 'required' => 'إجباري', 'optional' => 'اختياري',
            'actions' => 'إجراءات', 'noData' => 'لا توجد بيانات', 'darkMode' => 'الوضع الداكن', 'lightMode' => 'الوضع الفاتح',
            'language' => 'English', 'saved' => 'تم الحفظ بنجاح', 'egp' => 'جنيه',
        ],
        'admin' => [
            'loginTitle' => 'دخول فريق الإدارة', 'loginSub' => 'الدخول مخصص لفريق LCT فقط', 'pinLabel' => 'كلمة مرور الإدارة',
            'loginBtn' => 'دخول', 'loginErr' => 'كلمة المرور غير صحيحة', 'logout' => 'تسجيل خروج',
            'tabs' => ['overview' => 'نظرة عامة', 'site_editor' => 'التعديل على الموقع', 'courses' => 'الكورسات', 'halls' => 'القاعات والمواعيد', 'schedule' => 'جدول القاعات', 'bookings' => 'الحجوزات', 'customers' => 'العملاء', 'blocklist' => 'الأرقام المحظورة', 'settings' => 'الإعدادات'],
            'overview' => [
                'title' => 'نظرة عامة على النظام', 'totalBookings' => 'إجمالي الحجوزات', 'pending' => 'قيد المراجعة',
                'confirmed' => 'حجوزات مؤكدة', 'totalCustomers' => 'عدد العملاء', 'totalCourses' => 'الكورسات المتاحة',
                'recent' => 'أحدث الحجوزات', 'viewAll' => 'عرض الكل', 'noRecent' => 'لا توجد حجوزات بعد',
            ],
            'courses' => [
                'title' => 'إدارة الكورسات', 'add' => 'إضافة كورس جديد', 'edit' => 'تعديل الكورس',
                'active' => 'متاح', 'inactive' => 'متوقف', 'deleteConfirm' => 'متأكد إنك عايز تحذف الكورس ده؟',
                'rosterLink' => 'كشف الحضور (إكسيل)',
                'table' => ['code' => 'الكود', 'name' => 'الاسم', 'category' => 'القسم', 'price' => 'السعر', 'seats' => 'المقاعد', 'status' => 'الحالة', 'actions' => 'إجراءات'],
                'fields' => [
                    'nameAr' => 'اسم الكورس (عربي)', 'nameEn' => 'اسم الكورس (إنجليزي)', 'code' => 'كود الكورس', 'category' => 'القسم',
                    'descAr' => 'الوصف (عربي)', 'descEn' => 'الوصف (إنجليزي)', 'levelAr' => 'المستوى (عربي)', 'levelEn' => 'المستوى (إنجليزي)',
                    'duration' => 'المدة', 'price' => 'السعر (جنيه)', 'instructorAr' => 'المدرب (عربي)', 'instructorEn' => 'المدرب (إنجليزي)',
                    'seatsTotal' => 'إجمالي المقاعد', 'seatsBooked' => 'المقاعد المحجوزة', 'status' => 'الحالة',
                ],
            ],
            'halls' => [
                'title' => 'القاعات ومواعيد التأجير', 'addHall' => 'إضافة قاعة', 'editHall' => 'تعديل القاعة', 'capacity' => 'السعة',
                'pricePerHour' => 'سعر الإيجار (جنيه/ساعة)', 'branch' => 'الفرع', 'selectBranch' => 'اختر الفرع',
                'amenitiesLabel' => 'المميزات المتاحة', 'amenitySmartboard' => 'سبورة ذكية', 'amenityProjector' => 'بروجيكتور', 'amenityAc' => 'مكيفة',
                'addSlot' => 'إضافة ميعاد يدوي', 'editSlot' => 'تعديل الميعاد', 'available' => 'متاح', 'booked' => 'محجوز', 'noSlots' => 'لا يوجد حجوزات للقاعة دي',
                'deleteHallConfirm' => 'حذف القاعة وكل مواعيدها؟', 'deleteSlotConfirm' => 'حذف هذا الميعاد؟',
            ],
            'schedule' => [
                'title' => 'جدول القاعات', 'intro' => 'خريطة بصرية لكل مواعيد القاعات المحجوزة في الأسبوع — عشان تعرف بسرعة إيه الفاضي وإيه المحجوز.',
                'today' => 'الأسبوع الحالي', 'allBranches' => 'كل الفروع',
                'legendPending' => 'قيد المراجعة', 'legendConfirmed' => 'مؤكد',
                'noHalls' => 'مفيش قاعات لعرضها',
            ],
            'roster' => [
                'title' => 'كشف حضور', 'sheetName' => 'كشف حضور',
                'confirmedCount' => 'عدد الطلاب المؤكدين', 'noConfirmed' => 'مفيش طلاب متأكدين في الكورس ده لسه.',
                'sessionsLabel' => 'عدد المحاضرات', 'exportBtn' => 'تصدير كشف الحضور (Excel)',
                'csvFallbackNote' => 'السيرفر مش مفعّل عليه إنشاء ملفات Excel مباشرة، فهيتم التصدير كملف CSV بدلاً منه (بيتفتح في Excel برضو).',
                'colNum' => '#', 'colName' => 'الاسم بالكامل', 'colPhone' => 'رقم الموبايل', 'colNationalId' => 'الرقم القومي', 'colLecture' => 'محاضرة',
            ],
            'bookings' => [
                'title' => 'كل الحجوزات', 'searchPh' => 'ابحث بالاسم أو رقم الموبايل...', 'viewDetails' => 'عرض التفاصيل',
                'table' => ['name' => 'الاسم', 'phone' => 'الموبايل', 'course' => 'الكورس', 'date' => 'تاريخ الحجز', 'status' => 'الحالة', 'actions' => 'إجراءات'],
                'filterStatus' => 'الحالة',
                'confirm' => 'تأكيد الحجز', 'reject' => 'رفض الحجز', 'markCompleted' => 'تحديد كمكتمل',
                'cancel' => 'إلغاء الحجز', 'cancelConfirm' => 'متأكد إنك عايز تلغي الحجز ده؟ مش هيرجعش تاني.',
                'editTime' => 'تعديل الميعاد', 'editTimeTitle' => 'تعديل تاريخ ووقت الحجز',
                'idCardLabel' => 'صورة البطاقة المرفقة', 'noResults' => 'لا توجد حجوزات مطابقة', 'detailsTitle' => 'تفاصيل الحجز',
                'rejectReason' => 'سبب الرفض (اختياري)', 'typeCourse' => 'حجز كورس', 'typeHall' => 'حجز قاعة',
                'branch' => 'الفرع', 'slotDate' => 'تاريخ الميعاد', 'slotTime' => 'وقت الميعاد',
                'deletedMsg' => 'تم حذف الحجز نهائيًا', 'blockedMsg' => 'تم حظر الرقم',
                'deleteBooking' => 'حذف نهائي', 'deletePermanentConfirm' => 'هتحذف الحجز ده نهائيًا ومش هترجعله تاني (مش زي الإلغاء). متأكد؟',
                'blockThisNumber' => 'حظر هذا الرقم', 'blockNumberConfirm' => 'تحظر الرقم %s؟ مش هيقدر يسجل حجوزات جديدة تاني.',
                'numberAlreadyBlocked' => 'الرقم ده محظور بالفعل', 'blockedFromBooking' => 'اتحظر من صفحة تفاصيل حجز',
                'hallConflictError' => 'الميعاد ده اتأكد بالفعل لحجز تاني على نفس القاعة. ارفض الحجز ده أو عدّل ميعاده لوقت تاني.',
                'courseFullError' => 'مقاعد الكورس ده كملت خلاص. عشان تأكد الحجز ده لازم الأول تزود عدد المقاعد أو ترفض حجز متأكد تاني.',
                'overlapNotice' => 'في %d طلب تاني لنفس القاعة بيتعارض مع الميعاد ده. أول طلب تأكده هياخد الميعاد، والباقي هيحتاج يتعدل ميعاده أو يترفض.',
            ],
            'customers' => ['title' => 'سجل العملاء', 'searchPh' => 'ابحث بالاسم أو الموبايل...', 'viewProfile' => 'عرض الصفحة', 'noCustomers' => 'لا يوجد عملاء بعد',
                'deleteCustomer' => 'حذف العميل نهائيًا', 'deleteConfirm' => 'هتحذف بيانات "%s" وكل حجوزاته نهائيًا، ومش هترجع تاني. متأكد؟', 'deletedMsg' => 'تم حذف العميل نهائيًا',
                'table' => ['name' => 'الاسم', 'phone' => 'الموبايل', 'email' => 'البريد', 'bookings' => 'عدد الحجوزات', 'lastBooking' => 'آخر حجز', 'actions' => 'إجراءات']],
            'settings' => [
                'title' => 'الإعدادات', 'changePin' => 'تغيير كلمة مرور الإدارة', 'currentPin' => 'كلمة المرور الحالية',
                'newPin' => 'كلمة المرور الجديدة', 'confirmPin' => 'تأكيد كلمة المرور الجديدة', 'savePin' => 'حفظ كلمة المرور',
                'pinChanged' => 'تم تغيير كلمة المرور بنجاح', 'pinMismatch' => 'كلمتا المرور الجديدتان غير متطابقتين',
                'pinWrong' => 'كلمة المرور الحالية غير صحيحة', 'exportTitle' => 'تصدير البيانات',
                'exportDesc' => 'تصدير كل بيانات العملاء والحجوزات في ملف CSV منظم، يتفتح مباشرة في Excel.',
                'exportBtn' => 'تصدير البيانات (CSV)',
                'branchesTitle' => 'إدارة الفروع', 'addBranch' => 'إضافة فرع', 'editBranch' => 'تعديل الفرع',
                'branchNameAr' => 'اسم الفرع (عربي)', 'branchNameEn' => 'اسم الفرع (إنجليزي)',
                'branchAddressAr' => 'العنوان (عربي)', 'branchAddressEn' => 'العنوان (إنجليزي)',
                'branchPhone' => 'رقم التليفون', 'branchEmail' => 'البريد الإلكتروني',
                'branchHoursAr' => 'مواعيد العمل (عربي)', 'branchHoursEn' => 'مواعيد العمل (إنجليزي)',
                'deleteBranchConfirm' => 'حذف الفرع ده؟',
                'pinRoleNote' => 'بتغيّر كلمة مرور حساب:', 'roleAdmin' => 'الإدارة', 'roleManager' => 'المدير (صلاحيات كاملة)',
                'blocklistTitle' => 'حظر أرقام الموبايل', 'blocklistDesc' => 'امنع أرقام معيّنة من تسجيل حجوزات جديدة على الموقع.',
                'blocklistBtn' => 'إدارة الأرقام المحظورة',
            ],
            'blocklist' => [
                'title' => 'الأرقام المحظورة', 'intro' => 'أي رقم موبايل هنا مش هيقدر يبعت حجز جديد (كورس أو قاعة) من على الموقع.',
                'addTitle' => 'حظر رقم جديد', 'phoneLabel' => 'رقم الموبايل', 'reasonLabel' => 'السبب',
                'reasonPh' => 'مثال: حجوزات وهمية متكررة', 'blockBtn' => 'حظر الرقم',
                'blockedMsg' => 'تم حظر الرقم بنجاح', 'unblockedMsg' => 'تم إلغاء الحظر',
                'noneTitle' => 'مفيش أرقام محظورة دلوقتي', 'noneDesc' => 'أي رقم تحظره هيظهر هنا.',
                'blockedOn' => 'تاريخ الحظر', 'unblockBtn' => 'إلغاء الحظر', 'unblockConfirm' => 'تفك حظر الرقم ده؟',
                'invalidPhone' => 'رقم الموبايل غير صحيح', 'alreadyBlocked' => 'الرقم ده محظور بالفعل',
            ],
        ],
    ];

    $en = [
        'dir' => 'ltr',
        'brand' => ['name' => 'LCT', 'tag' => 'Your Way To Success'],
        'nav' => ['home' => 'Home', 'courses' => 'Courses', 'halls' => 'Book a Hall', 'myPage' => 'My Page'],
        'hero' => [
            'eyebrow' => 'Accredited Languages & Computer Training Academy',
            'title1' => 'Your Way', 'title2' => 'To Success',
            'subtitle' => 'Language and computer courses led by specialist trainers, with a fully verified online booking flow that secures your seat with complete credibility.',
            'ctaCourses' => 'Browse Courses', 'ctaLookup' => 'Track My Booking',
            'trust1' => 'ID verification required', 'trust2' => 'Manually reviewed before confirmation', 'trust3' => 'A dedicated page for every student',
            'stat1n' => '8+', 'stat1l' => 'Training programs', 'stat2n' => '30+', 'stat2l' => 'Graduated batches', 'stat3n' => '100%', 'stat3l' => 'Verified bookings',
        ],
        'depts' => [
            'eyebrow' => 'Training Departments', 'title' => 'Choose Your Department',
            'sub' => 'Two core programs, each with graded tracks across levels',
            'langTitle' => 'Languages Department', 'langDesc' => 'Intensive courses in the most in-demand living languages, from absolute beginner to fluency.',
            'compTitle' => 'Computer Department', 'compDesc' => 'Hands-on programs that prepare you for the job market in design, development and office tools.',
            'viewAll' => 'View All Courses', 'seatsLeft' => 'seats left', 'seatsFull' => 'Fully booked',
        ],
        'why' => [
            'eyebrow' => 'Why LCT', 'title' => 'Guarantees on record for every student', 'sub' => 'A booking system built on credibility first, not just convenience',
            'f1t' => 'Fully verified booking', 'f1d' => 'A national ID photo is required on every form, and no booking is confirmed until the admin team reviews it manually.',
            'f2t' => 'Real seat availability', 'f2d' => 'A live seat counter for every course and hall — no bookings once actual capacity is reached.',
            'f3t' => 'Student page', 'f3d' => 'Every student gets a dedicated page to track their courses and the status of every booking, in real time.',
            'f4t' => 'Complete management', 'f4d' => 'One dashboard to manage courses, hall schedules and every booking, with data export to Excel.',
        ],
        'ctaBand' => ['title' => 'Ready to start your journey?', 'sub' => 'Reserve your seat now, and our team will reach out to confirm your booking within 24 hours.', 'btn' => 'Reserve Your Seat'],
        'footer' => [
            'desc' => 'LCT Academy for Languages & Computer Training — verified enrollment, and full tracking for every student from day one.',
            'quick' => 'Quick Links', 'contact' => 'Contact Us', 'rights' => 'All rights reserved',
        ],
        'coursesPage' => [
            'title' => 'Course Catalog', 'sub' => "Filter by department or search for the course you're after",
            'searchPh' => 'Search course name...', 'all' => 'All', 'language' => 'Languages', 'computer' => 'Computer',
            'noResults' => 'No courses match your search',
        ],
        'course' => [
            'level' => 'Level', 'duration' => 'Duration', 'schedule' => 'Schedule', 'instructor' => 'Instructor',
            'price' => 'Price', 'seats' => 'Seats Available', 'prerequisites' => 'Prerequisites',
            'curriculum' => 'Course Outline', 'bookThis' => 'Book This Course', 'backToCourses' => 'Back to all courses',
            'code' => 'Course Code', 'egp' => 'EGP', 'perCourse' => 'for the full course',
        ],
        'status' => ['pending' => 'Pending Review', 'confirmed' => 'Confirmed', 'rejected' => 'Rejected', 'completed' => 'Completed', 'cancelled' => 'Cancelled'],
        'booking' => [
            'title' => 'Registration Form', 'stepLabels' => ['Personal Details', 'ID Verification', 'Review & Confirm'],
            'course' => 'Selected Course', 'fullName' => 'Full Name', 'fullNamePh' => 'Enter your name as it appears on your ID',
            'phone' => 'Mobile Number', 'phonePh' => '01xxxxxxxxx', 'email' => 'Email Address', 'emailPh' => 'example@email.com',
            'optional' => 'optional', 'nationalId' => 'National ID Number',
            'notes' => 'Additional Notes', 'notesPh' => "Anything you'd like us to know before booking...",
            'branch' => 'Branch', 'selectBranch' => 'Choose the branch you will attend',
            'idCardTitle' => 'National ID Photo',
            'idCardHint' => 'Required to verify your booking and prevent fake reservations. Only the admin team can view it, for review purposes only.',
            'idCardUpload' => 'Upload ID Photo', 'idCardDrag' => 'Drag an image here or click to choose',
            'idCardFormats' => 'JPG or PNG — under 8MB',
            'agreeLabel' => 'I confirm the information provided is accurate and I take responsibility for any incorrect data',
            'reviewNote' => 'After submitting, your booking status will be set to "Pending Review" until our team reviews your data and ID photo and confirms the booking.',
            'back' => 'Back', 'next' => 'Next', 'submit' => 'Submit Booking',
            'successTitle' => 'Your booking was received', 'successDesc' => "Your booking is now pending review. You'll see its status update on your student page as soon as our team reviews it.",
            'successRef' => 'Booking Reference', 'goToMyPage' => 'Go to My Page', 'backHome' => 'Back to Home',
            'err' => [
                'required' => 'This field is required', 'phone' => 'Invalid Egyptian mobile number (must start with 01 and be 11 digits)',
                'nationalId' => 'National ID must be 14 digits', 'idCard' => 'You must upload your ID photo to complete the booking',
                'agree' => 'You must agree to the data accuracy declaration', 'email' => 'Invalid email address',
                'duplicate' => 'You already have a pending booking with this number for this course',
                'fileType' => 'The image must be JPG or PNG', 'fileSize' => 'The image is too large, please choose a file under 8MB',
                'seatsFull' => 'Unfortunately this course is fully booked', 'general' => 'Something went wrong, please try again.',
                'blocked' => "We're unable to complete a booking with this number. Please contact us directly if you have questions.",
            ],
        ],
        'halls' => [
            'title' => 'Book a Hall', 'sub' => 'Our halls are available to rent for events and external training sessions — choose your date and time and book through the same verified process.',
            'capacity' => 'Capacity', 'noSlots' => 'No halls available at this branch currently',
            'bookNow' => 'Book This Hall', 'backToHalls' => 'Back to all halls', 'bookingTitle' => 'Hall Booking',
            'selectedHall' => 'Selected Hall', 'personCount' => 'Expected Number of People', 'purpose' => 'Purpose of Booking',
            'purposePh' => 'e.g. workshop, meeting, external training session...',
            'bookingDate' => 'Booking Date', 'startTime' => 'Start Time', 'endTime' => 'End Time',
            'pricePerHour' => 'Price per Hour', 'estimatedCost' => 'Estimated Cost',
            'egpPerHour' => 'EGP/hr', 'selectBranch' => 'Select Branch',
            'allBranches' => 'All Branches', 'branch' => 'Branch',
            'amenitySmartboard' => 'Smart Board', 'amenityProjector' => 'Projector', 'amenityAc' => 'Air Conditioned',
            'conflictError' => 'This time is already booked for this hall, please choose a different time.',
            'dateError' => 'Date must be tomorrow or later.',
            'timeError' => 'End time must be after start time.',
            'statusNote' => [
                'pending' => 'Your booking is currently under review by our admin team.',
                'confirmed' => 'Your hall booking has been officially confirmed. Welcome!',
                'rejected' => 'Unfortunately this booking was rejected. Contact us to find out why.',
                'cancelled' => 'This booking has been cancelled.',
            ],
        ],
        'portal' => [
            'title' => 'Student Page', 'sub' => 'Enter the mobile number you used to book, or the reference number you received, to track all your bookings',
            'phonePh' => 'Mobile number or reference number (e.g. LCT-123456)', 'searchBtn' => 'View My Bookings',
            'notFoundTitle' => 'No bookings found', 'notFoundDesc' => 'Double check the mobile or reference number, or book your first course now',
            'welcomeBack' => 'Welcome back', 'myBookings' => 'My Bookings', 'bookedOn' => 'Booked on',
            'newBooking' => 'Book another course', 'hallBookingLabel' => 'Hall Booking',
            'statusNote' => [
                'pending' => 'Your booking is currently under review by our admin team.', 'confirmed' => 'Your booking has been officially confirmed. Welcome aboard!',
                'rejected' => 'Unfortunately this booking was rejected. Contact us to find out why.', 'completed' => "Congratulations, you've successfully completed this.",
                'cancelled' => 'This booking has been cancelled. Contact us if you have any questions.',
            ],
        ],
        'common' => [
            'close' => 'Close', 'cancel' => 'Cancel', 'save' => 'Save', 'delete' => 'Delete', 'edit' => 'Edit',
            'add' => 'Add', 'search' => 'Search', 'all' => 'All', 'required' => 'Required', 'optional' => 'Optional',
            'actions' => 'Actions', 'noData' => 'No data available', 'darkMode' => 'Dark Mode', 'lightMode' => 'Light Mode',
            'language' => 'العربية', 'saved' => 'Saved successfully', 'egp' => 'EGP',
        ],
        'admin' => [
            'loginTitle' => 'Admin Team Login', 'loginSub' => 'Access restricted to the LCT team only', 'pinLabel' => 'Admin Password',
            'loginBtn' => 'Log In', 'loginErr' => 'Incorrect password', 'logout' => 'Log Out',
            'tabs' => ['overview' => 'Overview', 'site_editor' => 'Edit Website', 'courses' => 'Courses', 'halls' => 'Halls & Schedule', 'schedule' => 'Hall Calendar', 'bookings' => 'Bookings', 'customers' => 'Customers', 'blocklist' => 'Blocked Numbers', 'settings' => 'Settings'],
            'overview' => [
                'title' => 'System Overview', 'totalBookings' => 'Total Bookings', 'pending' => 'Pending Review',
                'confirmed' => 'Confirmed Bookings', 'totalCustomers' => 'Total Customers', 'totalCourses' => 'Active Courses',
                'recent' => 'Recent Bookings', 'viewAll' => 'View all', 'noRecent' => 'No bookings yet',
            ],
            'courses' => [
                'title' => 'Manage Courses', 'add' => 'Add New Course', 'edit' => 'Edit Course',
                'active' => 'Active', 'inactive' => 'Inactive', 'deleteConfirm' => 'Are you sure you want to delete this course?',
                'rosterLink' => 'Attendance Sheet (Excel)',
                'table' => ['code' => 'Code', 'name' => 'Name', 'category' => 'Department', 'price' => 'Price', 'seats' => 'Seats', 'status' => 'Status', 'actions' => 'Actions'],
                'fields' => [
                    'nameAr' => 'Course Name (Arabic)', 'nameEn' => 'Course Name (English)', 'code' => 'Course Code', 'category' => 'Department',
                    'descAr' => 'Description (Arabic)', 'descEn' => 'Description (English)', 'levelAr' => 'Level (Arabic)', 'levelEn' => 'Level (English)',
                    'duration' => 'Duration', 'price' => 'Price (EGP)', 'instructorAr' => 'Instructor (Arabic)', 'instructorEn' => 'Instructor (English)',
                    'seatsTotal' => 'Total Seats', 'seatsBooked' => 'Seats Booked', 'status' => 'Status',
                ],
            ],
            'halls' => [
                'title' => 'Halls & Rental Schedule', 'addHall' => 'Add Hall', 'editHall' => 'Edit Hall', 'capacity' => 'Capacity',
                'pricePerHour' => 'Rental Price (EGP/hour)', 'branch' => 'Branch', 'selectBranch' => 'Select Branch',
                'amenitiesLabel' => 'Available Amenities', 'amenitySmartboard' => 'Smart Board', 'amenityProjector' => 'Projector', 'amenityAc' => 'Air Conditioned',
                'addSlot' => 'Add Manual Slot', 'editSlot' => 'Edit Slot', 'available' => 'Available', 'booked' => 'Booked', 'noSlots' => 'No bookings for this hall yet',
                'deleteHallConfirm' => 'Delete this hall and all its time slots?', 'deleteSlotConfirm' => 'Delete this time slot?',
            ],
            'schedule' => [
                'title' => 'Hall Schedule', 'intro' => "A visual map of every hall booking this week — so you can see what's free and what's taken at a glance.",
                'today' => 'This Week', 'allBranches' => 'All Branches',
                'legendPending' => 'Pending Review', 'legendConfirmed' => 'Confirmed',
                'noHalls' => 'No halls to show',
            ],
            'roster' => [
                'title' => 'Attendance Sheet', 'sheetName' => 'Attendance Sheet',
                'confirmedCount' => 'Confirmed Students', 'noConfirmed' => 'No confirmed students on this course yet.',
                'sessionsLabel' => 'Number of Lectures', 'exportBtn' => 'Export Attendance Sheet (Excel)',
                'csvFallbackNote' => "This server doesn't have direct Excel file generation enabled, so this will export as a CSV file instead (still opens fine in Excel).",
                'colNum' => '#', 'colName' => 'Full Name', 'colPhone' => 'Mobile Number', 'colNationalId' => 'National ID', 'colLecture' => 'Lecture',
            ],
            'bookings' => [
                'title' => 'All Bookings', 'searchPh' => 'Search by name or mobile number...', 'viewDetails' => 'View Details',
                'table' => ['name' => 'Name', 'phone' => 'Mobile', 'course' => 'Course', 'date' => 'Booked On', 'status' => 'Status', 'actions' => 'Actions'],
                'filterStatus' => 'Status',
                'confirm' => 'Confirm Booking', 'reject' => 'Reject Booking', 'markCompleted' => 'Mark Completed',
                'cancel' => 'Cancel Booking', 'cancelConfirm' => 'Are you sure you want to cancel this booking? This cannot be undone.',
                'editTime' => 'Edit Appointment', 'editTimeTitle' => 'Edit Hall Booking Date & Time',
                'idCardLabel' => 'Attached ID Photo', 'noResults' => 'No matching bookings', 'detailsTitle' => 'Booking Details',
                'rejectReason' => 'Rejection reason (optional)', 'typeCourse' => 'Course booking', 'typeHall' => 'Hall booking',
                'branch' => 'Branch', 'slotDate' => 'Slot Date', 'slotTime' => 'Slot Time',
                'deletedMsg' => 'Booking permanently deleted', 'blockedMsg' => 'Number blocked',
                'deleteBooking' => 'Delete Permanently', 'deletePermanentConfirm' => "This will permanently delete this booking — it can't be undone (unlike Cancel). Are you sure?",
                'blockThisNumber' => 'Block This Number', 'blockNumberConfirm' => 'Block %s? They will no longer be able to submit new bookings.',
                'numberAlreadyBlocked' => 'This number is already blocked', 'blockedFromBooking' => 'Blocked from a booking detail page',
                'hallConflictError' => 'This time is already confirmed for another booking on this hall. Reject this one or edit it to a different time.',
                'courseFullError' => 'This course is already at full capacity. To confirm this booking, either increase the seat count or cancel another confirmed booking first.',
                'overlapNotice' => 'There is %d other request for this same hall overlapping this time. Whichever gets confirmed first takes the slot — the rest will need a new time or a rejection.',
            ],
            'customers' => ['title' => 'Customer Records', 'searchPh' => 'Search by name or mobile...', 'viewProfile' => 'View Page', 'noCustomers' => 'No customers registered yet',
                'deleteCustomer' => 'Delete Customer Permanently', 'deleteConfirm' => 'This will permanently delete "%s" and all their bookings — it cannot be undone. Are you sure?', 'deletedMsg' => 'Customer permanently deleted',
                'table' => ['name' => 'Name', 'phone' => 'Mobile', 'email' => 'Email', 'bookings' => 'Bookings', 'lastBooking' => 'Last Booking', 'actions' => 'Actions']],
            'settings' => [
                'title' => 'Settings', 'changePin' => 'Change Admin Password', 'currentPin' => 'Current Password',
                'newPin' => 'New Password', 'confirmPin' => 'Confirm New Password', 'savePin' => 'Save Password',
                'pinChanged' => 'Password changed successfully', 'pinMismatch' => "The new passwords don't match",
                'pinWrong' => 'Current password is incorrect', 'exportTitle' => 'Data Export',
                'exportDesc' => 'Export all customer and booking data into a structured CSV file that opens directly in Excel.',
                'exportBtn' => 'Export Data (CSV)',
                'branchesTitle' => 'Manage Branches', 'addBranch' => 'Add Branch', 'editBranch' => 'Edit Branch',
                'branchNameAr' => 'Branch Name (Arabic)', 'branchNameEn' => 'Branch Name (English)',
                'branchAddressAr' => 'Address (Arabic)', 'branchAddressEn' => 'Address (English)',
                'branchPhone' => 'Phone Number', 'branchEmail' => 'Email Address',
                'branchHoursAr' => 'Working Hours (Arabic)', 'branchHoursEn' => 'Working Hours (English)',
                'deleteBranchConfirm' => 'Delete this branch?',
                'pinRoleNote' => 'Changing the PIN for account:', 'roleAdmin' => 'Admin', 'roleManager' => 'Manager (full access)',
                'blocklistTitle' => 'Block Mobile Numbers', 'blocklistDesc' => 'Stop specific numbers from submitting new bookings on the site.',
                'blocklistBtn' => 'Manage Blocked Numbers',
            ],
            'blocklist' => [
                'title' => 'Blocked Numbers', 'intro' => "Any mobile number here can't submit a new booking (course or hall) on the site.",
                'addTitle' => 'Block a New Number', 'phoneLabel' => 'Mobile Number', 'reasonLabel' => 'Reason',
                'reasonPh' => 'e.g. repeated fake bookings', 'blockBtn' => 'Block Number',
                'blockedMsg' => 'Number blocked successfully', 'unblockedMsg' => 'Unblocked successfully',
                'noneTitle' => 'No blocked numbers yet', 'noneDesc' => 'Any number you block will show up here.',
                'blockedOn' => 'Blocked On', 'unblockBtn' => 'Unblock', 'unblockConfirm' => 'Unblock this number?',
                'invalidPhone' => 'Invalid mobile number', 'alreadyBlocked' => 'This number is already blocked',
            ],
        ],
    ];

    $customFile = __DIR__ . '/../data/translations.json';
    if (file_exists($customFile)) {
        $contents = @file_get_contents($customFile);
        if ($contents) {
            $custom = json_decode($contents, true);
            if (is_array($custom)) {
                if (!empty($custom['ar']) && is_array($custom['ar'])) {
                    $ar = array_replace_recursive($ar, $custom['ar']);
                }
                if (!empty($custom['en']) && is_array($custom['en'])) {
                    $en = array_replace_recursive($en, $custom['en']);
                }
                if (!empty($custom['replacements']['ar']) && is_array($custom['replacements']['ar'])) {
                    $ar = apply_text_replacements_recursive($ar, $custom['replacements']['ar']);
                }
                if (!empty($custom['replacements']['en']) && is_array($custom['replacements']['en'])) {
                    $en = apply_text_replacements_recursive($en, $custom['replacements']['en']);
                }
            }
        }
    }

    return $lang === 'en' ? $en : $ar;
}

function apply_text_replacements_recursive($data, $replacements) {
    if (empty($replacements) || !is_array($replacements)) return $data;
    if (is_string($data)) {
        return strtr($data, $replacements);
    }
    if (is_array($data)) {
        foreach ($data as $k => $v) {
            $data[$k] = apply_text_replacements_recursive($v, $replacements);
        }
    }
    return $data;
}


function get_custom_translations() {
    $customFile = __DIR__ . '/../data/translations.json';
    if (!file_exists($customFile)) return ['ar' => [], 'en' => []];
    $contents = @file_get_contents($customFile);
    $data = json_decode($contents, true);
    return is_array($data) ? $data : ['ar' => [], 'en' => []];
}

function save_custom_translations($data) {
    $customFile = __DIR__ . '/../data/translations.json';
    $dir = dirname($customFile);
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    $tmp = $customFile . '.tmp_' . uniqid();
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (file_put_file_contents_safe($tmp, $json)) {
        return rename($tmp, $customFile);
    }
    return false;
}

function file_put_file_contents_safe($filepath, $data) {
    $fp = fopen($filepath, 'w');
    if (!$fp) return false;
    flock($fp, LOCK_EX);
    fwrite($fp, $data);
    flock($fp, LOCK_UN);
    fclose($fp);
    return true;
}

function flatten_translation_array($array, $prefix = '') {
    $result = [];
    foreach ($array as $key => $value) {
        $newKey = $prefix === '' ? (string)$key : $prefix . '.' . $key;
        if (is_array($value)) {
            $result = array_merge($result, flatten_translation_array($value, $newKey));
        } else {
            $result[$newKey] = (string)$value;
        }
    }
    return $result;
}

function unflatten_translation_array($flat) {
    $result = [];
    foreach ($flat as $key => $value) {
        $keys = explode('.', $key);
        $temp = &$result;
        foreach ($keys as $i => $subKey) {
            if ($i === count($keys) - 1) {
                $temp[$subKey] = $value;
            } else {
                if (!isset($temp[$subKey]) || !is_array($temp[$subKey])) {
                    $temp[$subKey] = [];
                }
                $temp = &$temp[$subKey];
            }
        }
    }
    return $result;
}

