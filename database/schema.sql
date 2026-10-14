-- ============================================================================
-- LCT Academy — قاعدة البيانات (MySQL / MariaDB)
-- ============================================================================
-- ملاحظة مهمة:
-- الموقع حاليًا بيشتغل بملفات JSON مباشرة (مجلد data/) من غير أي قاعدة بيانات،
-- وده بيخليه شغال على أي استضافة من غير إعدادات إضافية.
--
-- الملف ده بيوفرلك نفس هيكل البيانات (الفروع، القاعات، الكورسات، العملاء،
-- الحجوزات...) كجداول SQL جاهزة، عشان:
--   • تقدر ترفعها على قاعدة بيانات MySQL في الاستضافة (phpMyAdmin مثلاً)
--     كنسخة احتياطية أو مرجعية بصيغة قواعد بيانات.
--   • تبقى جاهزة لو حبيت مستقبلاً تحول الموقع يشتغل بقاعدة بيانات
--     بدل ملفات الـ JSON (تطوير إضافي منفصل).
--
-- الاستيراد: افتح phpMyAdmin > اختار الداتا بيز بتاعتك > Import > اختار الملف ده.
-- تحذير: الأوامر تحت بتعمل DROP TABLE لو الجداول دي موجودة قبل كده بنفس
-- الأسماء، يعني هتتمسح وتتعمل من جديد. لو عندك بيانات فعلية على نفس الأسماء
-- دي، اعمل نسخة احتياطية الأول.
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------------
-- جدول الفروع (Branches)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `branches`;
CREATE TABLE `branches` (
  `id`           VARCHAR(40)   NOT NULL,
  `name_ar`      VARCHAR(150)  NOT NULL,
  `name_en`      VARCHAR(150)  NOT NULL,
  `address_ar`   VARCHAR(255)  NOT NULL,
  `address_en`   VARCHAR(255)  NOT NULL,
  `phone`        VARCHAR(30)   NOT NULL,
  `email`        VARCHAR(150)  NULL,
  `hours_ar`     VARCHAR(150)  NULL,
  `hours_en`     VARCHAR(150)  NULL,
  `created_at`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- جدول القاعات (Halls) — تابعة لفرع معيّن، وليها سعر إيجار بالساعة
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `halls`;
CREATE TABLE `halls` (
  `id`              VARCHAR(40)   NOT NULL,
  `branch_id`       VARCHAR(40)   NULL,
  `name_ar`         VARCHAR(150)  NOT NULL,
  `name_en`         VARCHAR(150)  NOT NULL,
  `type_ar`         VARCHAR(150)  NULL,
  `type_en`         VARCHAR(150)  NULL,
  `capacity`        INT UNSIGNED  NOT NULL DEFAULT 0,
  `price_per_hour`  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `has_smartboard`  TINYINT(1)    NOT NULL DEFAULT 0,
  `has_projector`   TINYINT(1)    NOT NULL DEFAULT 0,
  `has_ac`          TINYINT(1)    NOT NULL DEFAULT 0,
  `created_at`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_halls_branch` (`branch_id`),
  CONSTRAINT `fk_halls_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- جدول المواعيد المحجوبة يدويًا على قاعة (صيانة، استخدام داخلي...)
-- ده منفصل عن حجوزات العملاء (اللي موجودة في جدول bookings تحت)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `hall_blocked_times`;
CREATE TABLE `hall_blocked_times` (
  `id`         VARCHAR(40)  NOT NULL,
  `hall_id`    VARCHAR(40)  NOT NULL,
  `date`       DATE         NOT NULL,
  `start_time` TIME         NOT NULL,
  `end_time`   TIME         NOT NULL,
  `reason_ar`  VARCHAR(255) NULL,
  `reason_en`  VARCHAR(255) NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_blocked_hall_date` (`hall_id`, `date`),
  CONSTRAINT `fk_blocked_hall` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- جدول الكورسات (Courses)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `courses`;
CREATE TABLE `courses` (
  `id`               VARCHAR(40)   NOT NULL,
  `code`             VARCHAR(40)   NOT NULL,
  `category`         VARCHAR(40)   NOT NULL,          -- language | computer
  `icon`             VARCHAR(60)   NULL,
  `status`           VARCHAR(20)   NOT NULL DEFAULT 'active',
  `name_ar`          VARCHAR(200)  NOT NULL,
  `name_en`          VARCHAR(200)  NOT NULL,
  `tagline_ar`       VARCHAR(255)  NULL,
  `tagline_en`       VARCHAR(255)  NULL,
  `description_ar`   TEXT          NULL,
  `description_en`   TEXT          NULL,
  `level_ar`         VARCHAR(100)  NULL,
  `level_en`         VARCHAR(100)  NULL,
  `duration_ar`      VARCHAR(100)  NULL,
  `duration_en`      VARCHAR(100)  NULL,
  `price`            DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `instructor_ar`    VARCHAR(150)  NULL,
  `instructor_en`    VARCHAR(150)  NULL,
  `schedule_ar`      VARCHAR(255)  NULL,
  `schedule_en`      VARCHAR(255)  NULL,
  `seats_total`      INT UNSIGNED  NOT NULL DEFAULT 0,
  `seats_booked`     INT UNSIGNED  NOT NULL DEFAULT 0,   -- عدد المقاعد المؤكدة فقط (بيزيد لما الإدارة تأكد الحجز، مش وقت التقديم)
  `prerequisites_ar` VARCHAR(255)  NULL,
  `prerequisites_en` VARCHAR(255)  NULL,
  `created_at`       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_courses_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- عناصر المنهج لكل كورس (قائمة نقاط مرتبة)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `course_curriculum_items`;
CREATE TABLE `course_curriculum_items` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `course_id`  VARCHAR(40)   NOT NULL,
  `position`   INT UNSIGNED  NOT NULL DEFAULT 1,
  `item_ar`    VARCHAR(255)  NOT NULL,
  `item_en`    VARCHAR(255)  NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_curriculum_course` (`course_id`, `position`),
  CONSTRAINT `fk_curriculum_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- جدول العملاء (Customers) — رقم الموبايل هو المعرّف الأساسي، زي منطق
-- الموقع الحالي (ملف واحد لكل عميل بناءً على رقمه)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `phone`        VARCHAR(20)   NOT NULL,
  `full_name`    VARCHAR(150)  NOT NULL,
  `email`        VARCHAR(150)  NULL,
  `national_id`  VARCHAR(20)   NOT NULL,
  `created_at`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- جدول الحجوزات (Bookings) — كورسات وقاعات مع بعض في نفس الجدول،
-- والحقول الخاصة بالقاعة (branch/slot) بتفضل NULL في حجز الكورس والعكس.
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `bookings`;
CREATE TABLE `bookings` (
  `id`                VARCHAR(40)   NOT NULL,
  `ref`               VARCHAR(30)   NOT NULL,          -- رقم المرجع اللي بيتبين للعميل، مثال LCT-482913
  `customer_phone`    VARCHAR(20)   NOT NULL,
  `type`              ENUM('course','hall') NOT NULL,
  `status`            ENUM('pending','confirmed','rejected','completed','cancelled') NOT NULL DEFAULT 'pending',

  `course_id`         VARCHAR(40)   NULL,               -- لو type = course
  `hall_id`           VARCHAR(40)   NULL,               -- لو type = hall
  `branch_id`         VARCHAR(40)   NULL,               -- الفرع المختار (للكورس) أو فرع القاعة نفسها

  `slot_date`         DATE          NULL,               -- تاريخ الحجز (القاعات فقط) — العميل هو اللي بيختاره
  `slot_start`        TIME          NULL,
  `slot_end`          TIME          NULL,
  `person_count`      INT UNSIGNED  NULL,

  `notes`             TEXT          NULL,
  `id_photo_path`     VARCHAR(500)  NULL,               -- رابط Cloudinary أو مسار محلي كنسخة احتياطية
  `id_photo_is_cloudinary` TINYINT(1) NOT NULL DEFAULT 0,

  `reject_reason`     VARCHAR(255)  NULL,

  `created_at`        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at`        DATETIME      NULL,
  `time_updated_at`   DATETIME      NULL,               -- آخر مرة الإدارة عدّلت ميعاد القاعة
  `cancelled_at`       DATETIME      NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_bookings_ref` (`ref`),
  KEY `idx_bookings_customer` (`customer_phone`),
  KEY `idx_bookings_status` (`status`),
  KEY `idx_bookings_type` (`type`),
  KEY `idx_bookings_course` (`course_id`),
  KEY `idx_bookings_hall_date` (`hall_id`, `slot_date`),
  KEY `idx_bookings_branch` (`branch_id`),
  CONSTRAINT `fk_bookings_customer` FOREIGN KEY (`customer_phone`) REFERENCES `customers` (`phone`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bookings_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_bookings_hall` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_bookings_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- جدول مستخدمي لوحة التحكم — صفين بس حاليًا: admin (صلاحيات عادية) و
-- manager (صلاحيات كاملة: حذف نهائي وحظر أرقام)، كل واحد بكلمة مرور مشفّرة bcrypt
-- خاصة بيه
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `id`             TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role`           ENUM('admin','manager') NOT NULL,
  `password_hash`  VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- جدول أرقام الموبايل المحظورة من الحجز (صلاحية المدير فقط)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `blocked_numbers`;
CREATE TABLE `blocked_numbers` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `phone`       VARCHAR(20)  NOT NULL,
  `reason`      VARCHAR(255) NULL,
  `blocked_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_blocked_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- بيانات مبدئية (Seed Data) — نفس البيانات الموجودة حاليًا في ملفات JSON
-- بالموقع، عشان لو استوردت الملف ده تلاقي نفس الفروع والقاعات والكورسات
-- ============================================================================

-- ---------- الفروع ----------
INSERT INTO `branches` (`id`, `name_ar`, `name_en`, `address_ar`, `address_en`, `phone`, `email`, `hours_ar`, `hours_en`) VALUES
('br1', 'فرع الإسماعيلية', 'Ismailia Branch', 'شارع شبيين برج بانوراما أمام الموقف', 'Sharabin St., Panorama Tower, Opposite Bus Station', '01000000000', 'ismailia@lct-academy.eg', '9:00 صباحاً – 9:00 مساءً', '9:00 AM – 9:00 PM'),
('br2', 'فرع التلاتيني', 'El-Talateeny Branch', 'عند محمصة الدراويش', 'Near Al-Darawish Roastery', '01000000001', 'talateeny@lct-academy.eg', '9:00 صباحاً – 9:00 مساءً', '9:00 AM – 9:00 PM');

-- ---------- القاعات ----------
INSERT INTO `halls` (`id`, `branch_id`, `name_ar`, `name_en`, `type_ar`, `type_en`, `capacity`, `price_per_hour`, `has_smartboard`, `has_projector`, `has_ac`) VALUES
('h1', 'br1', 'قاعة النور', 'Al Noor Hall', 'قاعة لغات', 'Language Hall', 20, 150, 1, 0, 1),
('h2', 'br1', 'قاعة الإبداع', 'Al Ibda Hall', 'قاعة لغات', 'Language Hall', 16, 120, 0, 0, 1),
('h3', 'br2', 'معمل الحاسب الآلي', 'Computer Lab', 'معمل كمبيوتر', 'Computer Lab', 14, 200, 0, 1, 1);

-- ---------- الكورسات ----------
INSERT INTO `courses`
(`id`, `code`, `category`, `icon`, `status`, `name_ar`, `name_en`, `tagline_ar`, `tagline_en`, `description_ar`, `description_en`, `level_ar`, `level_en`, `duration_ar`, `duration_en`, `price`, `instructor_ar`, `instructor_en`, `schedule_ar`, `schedule_en`, `seats_total`, `seats_booked`, `prerequisites_ar`, `prerequisites_en`)
VALUES
('c1', 'LANG-EN-01', 'language', 'languages', 'active', 'اللغة الإنجليزية العامة والمحادثة', 'General English & Conversation', 'من الصفر لثقة كاملة في الكلام', 'From zero to confident conversation', 'كورس شامل لبناء أساس قوي في القواعد والمفردات، مع تركيز كبير على المحادثة والاستماع من أول أسبوع، عشان تقدر تتكلم إنجليزي بثقة في شغلك وحياتك اليومية.', 'A comprehensive course that builds a solid foundation in grammar and vocabulary, with heavy focus on speaking and listening from week one, so you can speak English confidently at work and in daily life.', 'مبتدئ إلى متوسط', 'Beginner to Intermediate', '8 أسابيع', '8 weeks', 1800, 'أ. مريم عادل', 'Ms. Mariam Adel', 'السبت والاثنين والأربعاء، 5:00 – 7:00 مساءً', 'Sat, Mon, Wed, 5:00 – 7:00 PM', 16, 11, 'لا يوجد', 'None'),
('c2', 'LANG-EN-02', 'language', 'award', 'active', 'تحضير اختبار الآيلتس (IELTS)', 'IELTS Exam Preparation', 'استعد لدرجة تفتحلك أبواب السفر والدراسة', 'Prepare for the score that opens study & travel doors', 'كورس مكثف مخصص لاجتياز اختبار الآيلتس، بيغطي المهارات الأربعة (قراءة، كتابة، استماع، محادثة) مع اختبارات تجريبية دورية وتقييم فردي لنقاط الضعف.', 'An intensive course dedicated to passing the IELTS exam, covering all four skills with regular mock tests and individual feedback on weak points.', 'متوسط إلى متقدم', 'Intermediate to Advanced', '6 أسابيع', '6 weeks', 2500, 'أ. كريم فتحي', 'Mr. Karim Fathy', 'الأحد والثلاثاء والخميس، 6:00 – 8:30 مساءً', 'Sun, Tue, Thu, 6:00 – 8:30 PM', 14, 14, 'مستوى متوسط في الإنجليزية على الأقل', 'At least an intermediate English level'),
('c3', 'LANG-FR-01', 'language', 'languages', 'active', 'اللغة الفرنسية (من الصفر)', 'French Language (From Scratch)', 'أول خطوة لعالم الفرنكوفونية', 'Your first step into the Francophone world', 'تعلم الفرنسية بأسلوب تفاعلي بيربط بين القواعد والمحادثة اليومية، مناسب تمامًا للمبتدئين اللي عايزين يبنوا أساس صح من البداية.', 'Learn French through an interactive approach connecting grammar with everyday conversation — perfect for beginners.', 'مبتدئ (A1-A2)', 'Beginner (A1-A2)', '10 أسابيع', '10 weeks', 2000, 'أ. نور الهدى سامي', 'Ms. Nour Elhoda Sami', 'السبت والثلاثاء، 4:00 – 6:00 مساءً', 'Sat, Tue, 4:00 – 6:00 PM', 18, 6, 'لا يوجد', 'None'),
('c4', 'LANG-DE-01', 'language', 'languages', 'active', 'اللغة الألمانية (مستوى A1)', 'German Language (Level A1)', 'مناسب لخطط الهجرة والدراسة في ألمانيا', 'Ideal for study & migration plans to Germany', 'أساسيات اللغة الألمانية بمعايير معتمدة، تحضيرًا لاختبارات الهجرة أو الدراسة، مع تدريب مكثف على النطق الصحيح من البداية.', 'German language fundamentals to accredited standards, preparing you for migration or study exams.', 'مبتدئ (A1)', 'Beginner (A1)', '12 أسبوع', '12 weeks', 2400, 'أ. عمر الشريف', 'Mr. Omar Elsherif', 'الأحد والأربعاء، 5:00 – 7:00 مساءً', 'Sun, Wed, 5:00 – 7:00 PM', 16, 9, 'لا يوجد', 'None'),
('c5', 'COMP-OF-01', 'computer', 'monitor', 'active', 'حزمة مايكروسوفت أوفيس الاحترافية', 'Microsoft Office Professional', 'Word و Excel و PowerPoint من الأساسيات للاحتراف', 'Word, Excel & PowerPoint from basics to pro', 'كورس عملي شامل على برامج Word وExcel وPowerPoint، مع تطبيقات حقيقية من بيئة العمل زي التقارير والمعادلات والعروض التقديمية الاحترافية.', 'A comprehensive hands-on course covering Word, Excel and PowerPoint with real workplace applications.', 'مبتدئ إلى متوسط', 'Beginner to Intermediate', '6 أسابيع', '6 weeks', 1500, 'أ. ياسمين طارق', 'Ms. Yasmin Tarek', 'السبت والاثنين، 3:00 – 5:00 مساءً', 'Sat, Mon, 3:00 – 5:00 PM', 14, 5, 'إلمام أساسي باستخدام الكمبيوتر', 'Basic computer familiarity'),
('c6', 'COMP-GD-01', 'computer', 'image', 'active', 'التصميم الجرافيكي (فوتوشوب وإليستريتور)', 'Graphic Design (Photoshop & Illustrator)', 'حوّل أفكارك لتصميمات احترافية', 'Turn your ideas into professional designs', 'تعلم أساسيات وأدوات فوتوشوب وإليستريتور خطوة بخطوة، وابني بورتفوليو حقيقي من مشاريع تصميم هوية بصرية وسوشيال ميديا.', 'Learn Photoshop and Illustrator tools step by step, and build a real portfolio.', 'مبتدئ', 'Beginner', '8 أسابيع', '8 weeks', 2200, 'أ. مصطفى جمال', 'Mr. Mostafa Gamal', 'الأحد والثلاثاء، 4:00 – 6:30 مساءً', 'Sun, Tue, 4:00 – 6:30 PM', 12, 8, 'لا يوجد', 'None'),
('c7', 'COMP-WD-01', 'computer', 'layers', 'active', 'تطوير الويب (HTML وCSS وJavaScript)', 'Web Development (HTML, CSS & JavaScript)', 'ابني موقعك الأول بإيدك', 'Build your first website with your own hands', 'كورس عملي 100% لتعلم بناء مواقع الويب من الصفر، من هيكلة الصفحات بـHTML، للتنسيق بـCSS، للتفاعل الحقيقي بـJavaScript.', 'A 100% hands-on course to learn web building from scratch.', 'مبتدئ', 'Beginner', '10 أسابيع', '10 weeks', 2800, 'م. أحمد رضا', 'Eng. Ahmed Reda', 'السبت والاثنين والأربعاء، 6:00 – 8:00 مساءً', 'Sat, Mon, Wed, 6:00 – 8:00 PM', 14, 12, 'لا يوجد', 'None'),
('c8', 'COMP-PY-01', 'computer', 'sparkles', 'active', 'البرمجة بلغة بايثون للمبتدئين', 'Python Programming for Beginners', 'أول لغة برمجة لعالم الداتا والذكاء الاصطناعي', 'Your first language into data & AI', 'مقدمة عملية قوية للبرمجة باستخدام بايثون، من الصفر لحد بناء مشاريع حقيقية زي أدوات تحليل بيانات بسيطة وألعاب صغيرة.', 'A strong hands-on introduction to programming with Python, from zero to building real projects.', 'مبتدئ', 'Beginner', '8 أسابيع', '8 weeks', 2600, 'م. سارة حسام', 'Eng. Sara Hossam', 'الأحد والخميس، 5:00 – 7:30 مساءً', 'Sun, Thu, 5:00 – 7:30 PM', 14, 3, 'لا يوجد', 'None');

-- ---------- عناصر مناهج الكورسات ----------
INSERT INTO `course_curriculum_items` (`course_id`, `position`, `item_ar`, `item_en`) VALUES
('c1', 1, 'أساسيات القواعد والأزمنة', 'Grammar & tenses fundamentals'),
('c1', 2, 'بناء مفردات يومية عملية', 'Practical everyday vocabulary'),
('c1', 3, 'تدريبات محادثة جماعية أسبوعية', 'Weekly group conversation practice'),
('c1', 4, 'مهارات الاستماع والنطق', 'Listening & pronunciation skills'),
('c1', 5, 'مشروع تقديم نهائي بالإنجليزي', 'Final English presentation project'),
('c2', 1, 'استراتيجيات قسم القراءة', 'Reading section strategies'),
('c2', 2, 'تقنيات الكتابة الأكاديمية', 'Academic writing techniques'),
('c2', 3, 'مهارات الاستماع المكثف', 'Intensive listening skills'),
('c2', 4, 'محاكاة اختبار المحادثة', 'Speaking test simulation'),
('c2', 5, 'اختبار تجريبي كامل كل أسبوعين', 'Full mock exam every two weeks'),
('c3', 1, 'الأبجدية والنطق الصحيح', 'Alphabet & correct pronunciation'),
('c3', 2, 'التحيات والتعارف', 'Greetings & introductions'),
('c3', 3, 'قواعد الأزمنة الأساسية', 'Basic tense grammar'),
('c3', 4, 'محادثات الحياة اليومية', 'Everyday life conversations'),
('c3', 5, 'مشاهد تفاعلية وأفلام قصيرة', 'Interactive scenes & short films'),
('c4', 1, 'أساسيات النطق والأبجدية', 'Pronunciation & alphabet basics'),
('c4', 2, 'مفردات المواقف اليومية', 'Everyday situational vocabulary'),
('c4', 3, 'تركيب الجملة الألماني', 'German sentence structure'),
('c4', 4, 'تدريب على اختبار Start Deutsch 1', 'Start Deutsch 1 exam practice'),
('c4', 5, 'محادثات تطبيقية موجهة', 'Guided applied conversations'),
('c5', 1, 'تنسيق المستندات الاحترافية في Word', 'Professional document formatting in Word'),
('c5', 2, 'المعادلات والدوال الأساسية في Excel', 'Core formulas & functions in Excel'),
('c5', 3, 'الجداول المحورية وتحليل البيانات', 'Pivot tables & data analysis'),
('c5', 4, 'تصميم عروض PowerPoint مؤثرة', 'Designing impactful PowerPoint decks'),
('c5', 5, 'مشروع تطبيقي متكامل', 'Integrated final project'),
('c6', 1, 'أساسيات فوتوشوب وتحرير الصور', 'Photoshop fundamentals & photo editing'),
('c6', 2, 'الرسم بالمتجهات في إليستريتور', 'Vector drawing in Illustrator'),
('c6', 3, 'تصميم الهوية البصرية واللوجو', 'Brand identity & logo design'),
('c6', 4, 'تصميمات السوشيال ميديا', 'Social media design'),
('c6', 5, 'بناء بورتفوليو نهائي', 'Building a final portfolio'),
('c7', 1, 'هيكلة الصفحات بـHTML5', 'Page structuring with HTML5'),
('c7', 2, 'التنسيق والتخطيط بـCSS3', 'Styling & layout with CSS3'),
('c7', 3, 'أساسيات JavaScript والتفاعلية', 'JavaScript fundamentals'),
('c7', 4, 'بناء موقع متجاوب Responsive', 'Building a responsive website'),
('c7', 5, 'مشروع تخرج: موقع شخصي كامل', 'Capstone project'),
('c8', 1, 'أساسيات البرمجة والمتغيرات', 'Programming basics & variables'),
('c8', 2, 'الجمل الشرطية والحلقات التكرارية', 'Conditionals & loops'),
('c8', 3, 'الدوال والتعامل مع البيانات', 'Functions & working with data'),
('c8', 4, 'مقدمة في مكتبات تحليل البيانات', 'Intro to data analysis libraries'),
('c8', 5, 'مشروع تطبيقي نهائي', 'Final applied project');

-- ---------- حسابات لوحة التحكم الحالية (نفس الهاشات الموجودة في data/admin.json) ----------
-- دول تشفير bcrypt لكلمتي السر الحاليتين بتوعك، مش الباسورد نفسه، فمينفعش حد يعرف
-- كلمة السر منهم. لو غيرت أي باسورد من لوحة التحكم، الهاش القديم ده هيبقى قديم.
-- الباسورد الافتراضي لحساب المدير (manager) لو ده أول تشغيل: LCTMANAGER2026 — يستحسن تغيّره فورًا.
INSERT INTO `admin_users` (`role`, `password_hash`) VALUES
('admin', '$2y$10$PgPXxhwmT.yhdiH0S81mGus6Ihv1nGMI0aBScEqVUD.QgNGWWCrO6'),
('manager', '$2y$10$dxLLRg.FOEiaLMoG5p2OwOsSfcCynT3ysv0wA.QO6pgTB.1098ae6');

-- customers و bookings و blocked_numbers مقصود إنهم يفضلوا فاضيين هنا، لأنهم بيتملوا
-- أوتوماتيك أول ما العملاء يبدأوا يحجزوا، أو لما المدير يحظر رقم من لوحة التحكم.
