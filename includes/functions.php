<?php
require_once __DIR__ . '/config.php';

/* ===================== Low-level JSON file & Upstash Redis I/O ===================== */

function _redis_key_for_file($path) {
    $norm = str_replace('\\', '/', $path);
    if (strpos($norm, 'courses.json') !== false) return 'lct:courses';
    if (strpos($norm, 'halls.json') !== false) return 'lct:halls';
    if (strpos($norm, 'settings.json') !== false) return 'lct:settings';
    if (strpos($norm, 'admin.json') !== false) return 'lct:admin';
    if (strpos($norm, 'blocked_numbers.json') !== false) return 'lct:blocked_numbers';
    if (strpos($norm, 'cloudinary_credentials.json') !== false) return 'lct:cloudinary_credentials';
    if (preg_match('#customers/(\d+)\.json#', $norm, $m)) return 'lct:customer:' . $m[1];
    return null;
}

function read_json_file($path, $default = []) {
    if (function_exists('upstash_is_configured') && upstash_is_configured()) {
        $key = _redis_key_for_file($path);
        if ($key !== null) {
            $data = upstash_get($key);
            if ($data !== null) return $data;
        }
    }
    if (!file_exists($path)) return $default;
    $fp = @fopen($path, 'r');
    if (!$fp) return $default;
    flock($fp, LOCK_SH);
    $contents = stream_get_contents($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    $data = json_decode($contents, true);
    return is_null($data) ? $default : $data;
}

function write_json_file($path, $data) {
    if (function_exists('upstash_is_configured') && upstash_is_configured()) {
        $key = _redis_key_for_file($path);
        if ($key !== null) {
            upstash_set($key, $data);
            if (strpos($key, 'lct:customer:') === 0) {
                $phone = substr($key, strlen('lct:customer:'));
                upstash_sadd('lct:customers_set', $phone);
                $phones = upstash_get('lct:customers_phones', []);
                if (!is_array($phones)) $phones = [];
                if (!in_array($phone, $phones)) {
                    $phones[] = $phone;
                    upstash_set('lct:customers_phones', $phones);
                }
                upstash_del('lct:all_customers');
                upstash_del('lct:all_bookings');
            }
        }
    }
    $dir = dirname($path);
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    $tmp = $path . '.tmp_' . uniqid();
    $fp = @fopen($tmp, 'w');
    if ($fp) {
        flock($fp, LOCK_EX);
        fwrite($fp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        flock($fp, LOCK_UN);
        fclose($fp);
        @rename($tmp, $path);
    }
    return true;
}

/** Read-modify-write a JSON file or Upstash Redis key while ensuring safe persistence */
function update_json_file($path, callable $mutator) {
    $current = read_json_file($path, []);
    $updated = $mutator($current);
    write_json_file($path, $updated);
    return $updated;
}


/* ===================== Courses / Halls ===================== */

function load_courses() {
    static $cache = null;
    if ($cache !== null) return $cache;
    return $cache = read_json_file(COURSES_FILE, []);
}
function save_courses($courses) { return write_json_file(COURSES_FILE, $courses); }

function load_halls() {
    static $cache = null;
    if ($cache !== null) return $cache;
    return $cache = read_json_file(HALLS_FILE, []);
}
function save_halls($halls) { return write_json_file(HALLS_FILE, $halls); }


function find_course($courses, $id) {
    foreach ($courses as $c) if ($c['id'] === $id) return $c;
    return null;
}
function course_seats_left($course) {
    return max(0, (int)$course['seatsTotal'] - (int)$course['seatsBooked']);
}
function find_hall($halls, $id) {
    foreach ($halls as $h) if ($h['id'] === $id) return $h;
    return null;
}
function find_slot($hall, $slotId) {
    if (!$hall) return null;
    foreach ($hall['slots'] as $s) if ($s['id'] === $slotId) return $s;
    return null;
}

/* ===================== Customers (ONE FILE PER CUSTOMER) ===================== */

function sanitize_phone($phone) {
    return preg_replace('/[^0-9]/', '', (string)$phone);
}
function customer_file_path($phone) {
    $clean = sanitize_phone($phone);
    return CUSTOMERS_DIR . '/' . $clean . '.json';
}
function load_customer($phone) {
    $clean = sanitize_phone($phone);
    if (function_exists('upstash_is_configured') && upstash_is_configured()) {
        $data = upstash_get('lct:customer:' . $clean);
        if ($data !== null) return $data;
    }
    $path = customer_file_path($clean);
    if (!file_exists($path)) return null;
    return read_json_file($path, null);
}

/** Append a booking (course or hall) to a customer's own record, creating the record if needed. */
function add_booking_for_customer($phone, $profile, $booking) {
    $clean = sanitize_phone($phone);
    $path = customer_file_path($clean);
    return update_json_file($path, function ($data) use ($profile, $booking, $clean) {
        if (empty($data)) {
            $data = [
                'phone' => $clean,
                'fullName' => $profile['fullName'],
                'email' => $profile['email'] ?? '',
                'nationalId' => $profile['nationalId'] ?? '',
                'createdAt' => date('c'),
                'bookings' => [],
            ];
        }
        $data['fullName'] = $profile['fullName'];
        if (!empty($profile['email'])) $data['email'] = $profile['email'];
        if (!empty($profile['nationalId'])) $data['nationalId'] = $profile['nationalId'];
        $data['bookings'][] = $booking;
        return $data;
    });
}

function update_customer_booking_status($phone, $bookingId, $status, $reason = '') {
    $clean = sanitize_phone($phone);
    $path = customer_file_path($clean);
    return update_json_file($path, function ($data) use ($bookingId, $status, $reason) {
        if (empty($data)) return $data;
        foreach ($data['bookings'] as &$b) {
            if ($b['id'] === $bookingId) {
                $b['status'] = $status;
                if ($reason !== '') $b['rejectReason'] = $reason;
                $b['reviewedAt'] = date('c');
            }
        }
        unset($b);
        return $data;
    });
}

/** Scan every customer record (from Upstash Redis using cached aggregate or bulk MGET or local files) and return a flat array of all customers. */
function list_all_customers($forceRefresh = false) {
    static $memo = null;
    if ($memo !== null && !$forceRefresh) return $memo;

    $out = [];
    if (function_exists('upstash_is_configured') && upstash_is_configured()) {
        if (!$forceRefresh) {
            $cached = upstash_get('lct:all_customers');
            if (!empty($cached) && is_array($cached)) {
                return $memo = $cached;
            }
        }
        $phones = upstash_smembers('lct:customers_set');
        if (empty($phones)) {
            $phones = upstash_get('lct:customers_phones', []);
        }
        if (!empty($phones)) {
            $keys = array_map(function($p) { return 'lct:customer:' . $p; }, $phones);
            $out = upstash_mget($keys);
            if (!empty($out)) {
                upstash_set('lct:all_customers', $out);
                return $memo = $out;
            }
        }
    }
    if (!is_dir(CUSTOMERS_DIR)) return $memo = $out;
    foreach (scandir(CUSTOMERS_DIR) as $file) {
        if (substr($file, -5) !== '.json') continue;
        $raw = @file_get_contents(CUSTOMERS_DIR . '/' . $file);
        if ($raw) {
            $data = json_decode($raw, true);
            if ($data && is_array($data)) $out[] = $data;
        }
    }
    return $memo = $out;
}

/** Flatten every booking (course + hall) from every customer file into one list, newest first. */
function all_bookings($forceRefresh = false) {
    static $memo = null;
    if ($memo !== null && !$forceRefresh) return $memo;

    if (function_exists('upstash_is_configured') && upstash_is_configured() && !$forceRefresh) {
        $cached = upstash_get('lct:all_bookings');
        if (!empty($cached) && is_array($cached)) {
            return $memo = $cached;
        }
    }

    $out = [];
    foreach (list_all_customers($forceRefresh) as $customer) {
        foreach (($customer['bookings'] ?? []) as $b) {
            $b['phone'] = $customer['phone'];
            $b['fullName'] = $customer['fullName'];
            $b['email'] = $customer['email'] ?? '';
            $out[] = $b;
        }
    }
    usort($out, function ($a, $b) { return strcmp($b['createdAt'] ?? '', $a['createdAt'] ?? ''); });
    if (function_exists('upstash_is_configured') && upstash_is_configured() && !empty($out)) {
        upstash_set('lct:all_bookings', $out);
    }
    return $memo = $out;
}


function find_booking_owner($bookingId) {
    foreach (list_all_customers() as $customer) {
        foreach (($customer['bookings'] ?? []) as $b) {
            if ($b['id'] === $bookingId) return $customer['phone'];
        }
    }
    return null;
}

/** Find the phone number of whoever owns a booking with this customer-facing reference (e.g. "LCT-123456"). */
function find_phone_by_ref($ref) {
    $ref = trim((string)$ref);
    if ($ref === '') return null;
    foreach (list_all_customers() as $customer) {
        foreach (($customer['bookings'] ?? []) as $b) {
            if (isset($b['ref']) && strcasecmp(trim($b['ref']), $ref) === 0) return $customer['phone'];
        }
    }
    return null;
}


/** Has this phone number already got a pending/confirmed booking for this course or hall-slot? */
function has_duplicate_booking($phone, $targetType, $targetId) {
    $customer = load_customer($phone);
    if (!$customer) return false;
    foreach (($customer['bookings'] ?? []) as $b) {
        if (($b['status'] === 'pending' || $b['status'] === 'confirmed')
            && $b['type'] === $targetType
            && (($targetType === 'course' && $b['courseId'] === $targetId)
                || ($targetType === 'hall' && $b['slotId'] === $targetId))) {
            return true;
        }
    }
    return false;
}

/* ===================== Admin auth ===================== */

/* ===================== Admin users (two roles: admin, manager) ===================== */

function load_admin_users() {
    $data = read_json_file(ADMIN_FILE, ['users' => []]);
    // Back-compat: migrate a legacy single-hash admin.json on the fly (read-only, in memory).
    if (empty($data['users']) && !empty($data['password_hash'])) {
        return [['role' => 'admin', 'password_hash' => $data['password_hash']]];
    }
    return $data['users'] ?? [];
}
function save_admin_users($users) {
    return write_json_file(ADMIN_FILE, ['users' => array_values($users)]);
}
function find_admin_user($role) {
    foreach (load_admin_users() as $u) {
        if (($u['role'] ?? '') === $role) return $u;
    }
    return null;
}
function update_admin_password($role, $newHash) {
    $users = load_admin_users();
    $found = false;
    foreach ($users as &$u) {
        if (($u['role'] ?? '') === $role) { $u['password_hash'] = $newHash; $found = true; }
    }
    unset($u);
    if (!$found) $users[] = ['role' => $role, 'password_hash' => $newHash];
    save_admin_users($users);
}

function is_admin_logged_in() {
    if (!empty($_SESSION['lct_admin'])) return true;
    if (!empty($_COOKIE['lct_adm_auth'])) {
        $parts = explode(':', $_COOKIE['lct_adm_auth'], 2);
        if (count($parts) === 2) {
            [$role, $token] = $parts;
            if (hash_equals(hash_hmac('sha256', $role, 'lct_adm_secret_key_2026'), $token)) {
                $_SESSION['lct_admin'] = true;
                $_SESSION['lct_admin_role'] = $role;
                return true;
            }
        }
    }
    return false;
}

function current_admin_role() {
    return $_SESSION['lct_admin_role'] ?? 'admin';
}
/** The "manager" role can permanently delete bookings/customers and manage the phone blocklist. */
function is_manager() {
    return is_admin_logged_in() && current_admin_role() === 'manager';
}
function require_admin() {
    if (!is_admin_logged_in()) {
        header('Location: /admin/login.php');
        exit;
    }
}
/** Use on manager-only pages/actions. Sends a non-manager back to the dashboard, not just hides a button. */
function require_manager() {
    require_admin();
    if (!is_manager()) {
        header('Location: index.php?denied=1');
        exit;
    }
}

/* ===================== Validation ===================== */

function validate_phone_eg($phone) {
    return (bool)preg_match('/^01[0125][0-9]{8}$/', trim($phone));
}
function validate_email_addr($email) {
    if (trim($email) === '') return true;
    return (bool)filter_var(trim($email), FILTER_VALIDATE_EMAIL);
}
function validate_national_id($id) {
    return (bool)preg_match('/^\d{14}$/', trim($id));
}
function gen_booking_ref() {
    return 'LCT-' . random_int(100000, 999999);
}
function gen_uid($prefix) {
    return $prefix . '_' . bin2hex(random_bytes(5));
}

/* ===================== ID photo upload ===================== */

/**
 * Validates + saves an uploaded ID photo. Returns ['ok'=>true,'path'=>relative path]
 * or ['ok'=>false,'error'=>string].
 */
/* ===================== Lang / theme preference (cookies) ===================== */

function current_lang() {
    $l = $_COOKIE['lct_lang'] ?? 'ar';
    return $l === 'en' ? 'en' : 'ar';
}
function current_theme() {
    $t = $_COOKIE['lct_theme'] ?? 'light';
    return $t === 'dark' ? 'dark' : 'light';
}

function format_date_h($iso, $lang) {
    if (!$iso) return '';
    $ts = strtotime($iso);
    if (!$ts) return $iso;
    $months_ar = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
    if ($lang === 'ar') {
        return date('j', $ts) . ' ' . $months_ar[(int)date('n', $ts) - 1] . ' ' . date('Y', $ts);
    }
    return date('j M Y', $ts);
}
function format_datetime_h($iso, $lang) {
    if (!$iso) return '';
    $ts = strtotime($iso);
    if (!$ts) return $iso;
    return format_date_h($iso, $lang) . ' - ' . date('h:i A', $ts);
}

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/* ===================== Settings (branches, etc.) ===================== */

function load_settings() {
    static $cache = null;
    if ($cache !== null) return $cache;
    return $cache = read_json_file(SETTINGS_FILE, ['branches' => []]);
}
function save_settings($data) {
    return write_json_file(SETTINGS_FILE, $data);
}

function load_branches() {
    $s = load_settings();
    return $s['branches'] ?? [];
}
function find_branch($branches, $id) {
    foreach ($branches as $b) if ($b['id'] === $id) return $b;
    return null;
}

/* ===================== Phone blocklist (manager-only feature) ===================== */

function load_blocked_numbers() {
    return read_json_file(BLOCKED_NUMBERS_FILE, []);
}
function save_blocked_numbers($data) {
    return write_json_file(BLOCKED_NUMBERS_FILE, $data);
}
function is_phone_blocked($phone) {
    $clean = sanitize_phone($phone);
    if ($clean === '') return false;
    foreach (load_blocked_numbers() as $b) {
        if (sanitize_phone($b['phone'] ?? '') === $clean) return true;
    }
    return false;
}
function block_phone_number($phone, $reason = '') {
    $clean = sanitize_phone($phone);
    if ($clean === '' || is_phone_blocked($clean)) return false;
    $list = load_blocked_numbers();
    $list[] = ['phone' => $clean, 'reason' => trim($reason), 'blockedAt' => date('c')];
    return save_blocked_numbers($list);
}
function unblock_phone_number($phone) {
    $clean = sanitize_phone($phone);
    $list = load_blocked_numbers();
    $list = array_values(array_filter($list, fn($b) => sanitize_phone($b['phone'] ?? '') !== $clean));
    return save_blocked_numbers($list);
}

/* ===================== Hall slot conflict check ===================== */

/**
 * Check if a hall already has a pending/confirmed booking overlapping [start..end] on date.
 * $excludeSlotId lets you skip a specific slot when editing an existing booking.
 */
/**
 * Does hall $hallId already have a CONFIRMED booking overlapping [start..end] on date?
 * Only 'booked' (= admin-confirmed) slots block — several customers can have PENDING
 * requests for the same overlapping time at once; the hall stays open to all of them
 * until the admin confirms one, at which point this same check (used again from the
 * confirm action) stops a second overlapping slot from being confirmed too.
 * $excludeSlotId lets you skip a specific slot when confirming/editing that same slot.
 */
function hall_slot_conflicts($hallId, $date, $start, $end, $excludeSlotId = null) {
    $halls = load_halls();
    $hall = find_hall($halls, $hallId);
    if (!$hall) return false;
    $startMin = _time_to_minutes($start);
    $endMin   = _time_to_minutes($end);
    foreach ($hall['slots'] as $s) {
        if ($s['id'] === $excludeSlotId) continue;
        if ($s['date'] !== $date) continue;
        if ($s['status'] !== 'booked') continue;
        $sStart = _time_to_minutes($s['start']);
        $sEnd   = _time_to_minutes($s['end']);
        if ($startMin < $sEnd && $endMin > $sStart) return true;
    }
    return false;
}
function _time_to_minutes($t) {
    [$h, $m] = explode(':', $t);
    return (int)$h * 60 + (int)$m;
}

/**
 * How many OTHER requests (any status — pending or already confirmed) overlap this
 * hall/date/time? Purely informational, so the admin can see at a glance that several
 * customers are competing for the same slot before they confirm one of them.
 */
function count_overlapping_hall_slots($hallId, $date, $start, $end, $excludeSlotId = null) {
    $halls = load_halls();
    $hall = find_hall($halls, $hallId);
    if (!$hall) return 0;
    $startMin = _time_to_minutes($start);
    $endMin   = _time_to_minutes($end);
    $count = 0;
    foreach ($hall['slots'] as $s) {
        if ($s['id'] === $excludeSlotId) continue;
        if ($s['date'] !== $date) continue;
        if (!in_array($s['status'], ['pending', 'booked'])) continue;
        $sStart = _time_to_minutes($s['start']);
        $sEnd   = _time_to_minutes($s['end']);
        if ($startMin < $sEnd && $endMin > $sStart) $count++;
    }
    return $count;
}

/* ===================== Cancel booking ===================== */

function cancel_booking($bookingId) {
    $phone = find_booking_owner($bookingId);
    if (!$phone) return false;

    $customer = load_customer($phone);
    $booking = null;
    foreach (($customer['bookings'] ?? []) as $b) {
        if ($b['id'] === $bookingId) { $booking = $b; break; }
    }
    if (!$booking) return false;

    // Update customer record
    update_json_file(customer_file_path($phone), function ($data) use ($bookingId) {
        foreach ($data['bookings'] as &$b) {
            if ($b['id'] === $bookingId) {
                $b['status'] = 'cancelled';
                $b['cancelledAt'] = date('c');
            }
        }
        unset($b);
        return $data;
    });

    $type = $booking['type'] ?? 'course';

    if ($type === 'hall') {
        // Free up the slot
        update_json_file(HALLS_FILE, function ($all) use ($booking) {
            foreach ($all as &$h) {
                if ($h['id'] !== ($booking['hallId'] ?? '')) continue;
                $h['slots'] = array_values(array_filter($h['slots'], fn($s) => $s['id'] !== ($booking['slotId'] ?? '')));
            }
            unset($h);
            return $all;
        });
    } else {
        // Course: only a CONFIRMED seat was ever counted (seats increment on admin confirm,
        // not on submission) — so only refund if this booking had actually been confirmed.
        if ($booking['status'] === 'confirmed') {
            update_json_file(COURSES_FILE, function ($all) use ($booking) {
                foreach ($all as &$c) {
                    if ($c['id'] === ($booking['courseId'] ?? '')) {
                        $c['seatsBooked'] = max(0, (int)$c['seatsBooked'] - 1);
                    }
                }
                unset($c);
                return $all;
            });
        }
    }
    return true;
}

/* ===================== Permanent delete (manager-only) ===================== */

/**
 * Permanently remove a single booking record (not just mark it cancelled).
 * Frees the hall slot / refunds the course seat first, exactly like cancel_booking,
 * then actually deletes the entry from the customer's file.
 */
function delete_booking_permanently($bookingId) {
    $phone = find_booking_owner($bookingId);
    if (!$phone) return false;

    $customer = load_customer($phone);
    $booking = null;
    foreach (($customer['bookings'] ?? []) as $b) {
        if ($b['id'] === $bookingId) { $booking = $b; break; }
    }
    if (!$booking) return false;

    if (!in_array($booking['status'], ['cancelled', 'rejected'], true)) {
        $type = $booking['type'] ?? 'course';
        if ($type === 'hall') {
            update_json_file(HALLS_FILE, function ($all) use ($booking) {
                foreach ($all as &$h) {
                    if ($h['id'] !== ($booking['hallId'] ?? '')) continue;
                    $h['slots'] = array_values(array_filter($h['slots'], fn($s) => $s['id'] !== ($booking['slotId'] ?? '')));
                }
                unset($h);
                return $all;
            });
        } elseif ($booking['status'] === 'confirmed') {
            update_json_file(COURSES_FILE, function ($all) use ($booking) {
                foreach ($all as &$c) {
                    if ($c['id'] === ($booking['courseId'] ?? '')) {
                        $c['seatsBooked'] = max(0, (int)$c['seatsBooked'] - 1);
                    }
                }
                unset($c);
                return $all;
            });
        }
    }

    update_json_file(customer_file_path($phone), function ($data) use ($bookingId) {
        $data['bookings'] = array_values(array_filter($data['bookings'], fn($b) => $b['id'] !== $bookingId));
        return $data;
    });
    return true;
}

/**
 * Permanently remove a customer and ALL of their bookings, freeing any hall
 * slots / course seats those bookings were holding first.
 */
function delete_customer($phone) {
    $clean = sanitize_phone($phone);
    $customer = load_customer($clean);
    if (!$customer) return false;

    foreach (($customer['bookings'] ?? []) as $b) {
        if (in_array($b['status'], ['cancelled', 'rejected'], true)) continue;
        if (($b['type'] ?? 'course') === 'hall') {
            update_json_file(HALLS_FILE, function ($all) use ($b) {
                foreach ($all as &$h) {
                    if ($h['id'] !== ($b['hallId'] ?? '')) continue;
                    $h['slots'] = array_values(array_filter($h['slots'], fn($s) => $s['id'] !== ($b['slotId'] ?? '')));
                }
                unset($h);
                return $all;
            });
        } elseif ($b['status'] === 'confirmed') {
            update_json_file(COURSES_FILE, function ($all) use ($b) {
                foreach ($all as &$c) {
                    if ($c['id'] === ($b['courseId'] ?? '')) {
                        $c['seatsBooked'] = max(0, (int)$c['seatsBooked'] - 1);
                    }
                }
                unset($c);
                return $all;
            });
        }
    }

    if (function_exists('upstash_is_configured') && upstash_is_configured()) {
        upstash_del('lct:customer:' . $clean);
        upstash_srem('lct:customers_set', $clean);
        $phones = upstash_get('lct:customers_phones', []);
        if (is_array($phones)) {
            $phones = array_values(array_filter($phones, fn($p) => $p !== $clean));
            upstash_set('lct:customers_phones', $phones);
        }
        upstash_del('lct:all_customers');
        upstash_del('lct:all_bookings');
    }

    $path = customer_file_path($clean);
    return file_exists($path) ? @unlink($path) : true;
}


function update_hall_booking_time($bookingId, $newDate, $newStart, $newEnd) {
    $phone = find_booking_owner($bookingId);
    if (!$phone) return false;

    // Update hall slot entry
    update_json_file(customer_file_path($phone), function ($data) use ($bookingId, $newDate, $newStart, $newEnd) {
        foreach ($data['bookings'] as &$b) {
            if ($b['id'] === $bookingId) {
                $b['slotDate']  = $newDate;
                $b['slotStart'] = $newStart;
                $b['slotEnd']   = $newEnd;
                $b['timeUpdatedAt'] = date('c');
            }
        }
        unset($b);
        return $data;
    });

    // Update halls.json slot
    $customer = load_customer($phone);
    $hallId = null; $slotId = null;
    foreach (($customer['bookings'] ?? []) as $b) {
        if ($b['id'] === $bookingId) { $hallId = $b['hallId'] ?? null; $slotId = $b['slotId'] ?? null; break; }
    }
    if ($hallId && $slotId) {
        update_json_file(HALLS_FILE, function ($all) use ($hallId, $slotId, $newDate, $newStart, $newEnd) {
            foreach ($all as &$h) {
                if ($h['id'] !== $hallId) continue;
                foreach ($h['slots'] as &$s) {
                    if ($s['id'] === $slotId) {
                        $s['date']  = $newDate;
                        $s['start'] = $newStart;
                        $s['end']   = $newEnd;
                    }
                }
                unset($s);
            }
            unset($h);
            return $all;
        });
    }
    return true;
}

/* ===================== ID photo upload (with Cloudinary) ===================== */

function handle_id_photo_upload($fieldName, $refName) {
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'error' => 'missing'];
    }
    $file = $_FILES[$fieldName];
    if ($file['error'] !== UPLOAD_ERR_OK) return ['ok' => false, 'error' => 'upload_error'];
    if ($file['size'] > 8 * 1024 * 1024) return ['ok' => false, 'error' => 'too_large'];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
    if (!isset($extMap[$mime])) return ['ok' => false, 'error' => 'bad_type'];

    $ext      = $extMap[$mime];
    $filename = $refName . '_' . substr(md5(uniqid('', true)), 0, 8) . '.' . $ext;
    $dest     = UPLOADS_DIR . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) return ['ok' => false, 'error' => 'move_failed'];

    // Attempt Cloudinary upload
    require_once __DIR__ . '/cloudinary.php';
    $publicId = 'lct_id_' . $refName . '_' . substr(md5(uniqid('', true)), 0, 8);
    $cloudUrl = upload_to_cloudinary($dest, $publicId);

    if ($cloudUrl) {
        // File deleted from server by upload_to_cloudinary()
        return ['ok' => true, 'path' => $cloudUrl, 'isCloudinary' => true];
    }

    // Fallback: keep local copy
    return ['ok' => true, 'path' => 'data/uploads/id_photos/' . $filename, 'isCloudinary' => false];
}

/* ===================== Analytics & Financial Reports ===================== */

function calculate_analytics_summary() {
    $courses = load_courses();
    $halls   = load_halls();
    $bookings= all_bookings();

    $courseMap = [];
    foreach ($courses as $c) $courseMap[$c['id']] = $c;
    $hallMap = [];
    foreach ($halls as $h) $hallMap[$h['id']] = $h;

    $totalRevenue    = 0;
    $courseRevenue   = 0;
    $hallRevenue     = 0;
    $pendingRevenue  = 0;
    $confirmedCount  = 0;
    $pendingCount    = 0;

    $courseStats = [];
    foreach ($courses as $c) {
        $courseStats[$c['id']] = [
            'id'          => $c['id'],
            'name'        => $c['name'],
            'price'       => (float)($c['price'] ?? 0),
            'bookings'    => 0,
            'confirmed'   => 0,
            'totalRevenue'=> 0,
        ];
    }

    $monthlyStats = [];
    // Initialize past 6 months
    for ($i = 5; $i >= 0; $i--) {
        $mKey = date('Y-m', strtotime("-$i months"));
        $monthlyStats[$mKey] = ['month' => $mKey, 'bookings' => 0, 'revenue' => 0];
    }

    foreach ($bookings as $b) {
        $isConfirmed = ($b['status'] ?? '') === 'confirmed';
        $isPending   = ($b['status'] ?? '') === 'pending';
        $type        = $b['type'] ?? 'course';
        $createdMonth= substr($b['createdAt'] ?? date('Y-m'), 0, 7);

        if ($isConfirmed) $confirmedCount++;
        if ($isPending)   $pendingCount++;

        $itemRev = 0;
        if ($type === 'course') {
            $cId = $b['courseId'] ?? '';
            $price = isset($courseMap[$cId]) ? (float)($courseMap[$cId]['price'] ?? 0) : 0;
            $itemRev = $price;
            if (isset($courseStats[$cId])) {
                $courseStats[$cId]['bookings']++;
                if ($isConfirmed) {
                    $courseStats[$cId]['confirmed']++;
                    $courseStats[$cId]['totalRevenue'] += $price;
                }
            }
            if ($isConfirmed) {
                $totalRevenue  += $price;
                $courseRevenue += $price;
            } elseif ($isPending) {
                $pendingRevenue += $price;
            }
        } else {
            // Hall booking
            $hId = $b['hallId'] ?? '';
            $rate = isset($hallMap[$hId]) ? (float)($hallMap[$hId]['hourlyRate'] ?? 0) : 0;
            $hours = 2; // Default duration assumption if not specified
            if (!empty($b['slotStart']) && !empty($b['slotEnd'])) {
                $t1 = strtotime('2000-01-01 ' . $b['slotStart']);
                $t2 = strtotime('2000-01-01 ' . $b['slotEnd']);
                if ($t2 > $t1) $hours = round(($t2 - $t1) / 3600, 1);
            }
            $itemRev = $rate * $hours;
            if ($isConfirmed) {
                $totalRevenue += $itemRev;
                $hallRevenue  += $itemRev;
            } elseif ($isPending) {
                $pendingRevenue += $itemRev;
            }
        }

        if (isset($monthlyStats[$createdMonth])) {
            $monthlyStats[$createdMonth]['bookings']++;
            if ($isConfirmed) {
                $monthlyStats[$createdMonth]['revenue'] += $itemRev;
            }
        }
    }

    // Sort top requested courses by bookings count
    usort($courseStats, fn($a, $b) => $b['bookings'] <=> $a['bookings']);

    return [
        'totalRevenue'   => $totalRevenue,
        'courseRevenue'  => $courseRevenue,
        'hallRevenue'    => $hallRevenue,
        'pendingRevenue' => $pendingRevenue,
        'confirmedCount' => $confirmedCount,
        'pendingCount'   => $pendingCount,
        'totalBookings'  => count($bookings),
        'topCourses'     => array_slice($courseStats, 0, 5),
        'monthlyStats'   => array_values($monthlyStats),
    ];
}

function get_upcoming_reminders() {
    $bookings = all_bookings();
    $today    = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $halls    = load_halls();
    $hallMap  = [];
    foreach ($halls as $h) $hallMap[$h['id']] = $h;

    $reminders = [
        'hallToday'        => [],
        'hallTomorrow'     => [],
        'pendingAction'    => [],
        'paymentReminders' => [],
    ];

    $courses = load_courses();
    $courseMap = [];
    foreach ($courses as $c) $courseMap[$c['id']] = $c;

    foreach ($bookings as $b) {
        if (($b['status'] ?? '') === 'pending') {
            $reminders['pendingAction'][] = $b;
        }

        if (($b['type'] ?? '') === 'hall' && !empty($b['slotDate'])) {
            if ($b['slotDate'] === $today) {
                $b['hallName'] = $hallMap[$b['hallId']]['name'] ?? [];
                $reminders['hallToday'][] = $b;
            } elseif ($b['slotDate'] === $tomorrow) {
                $b['hallName'] = $hallMap[$b['hallId']]['name'] ?? [];
                $reminders['hallTomorrow'][] = $b;
            }
        }

        // Payment due date reminders
        $type = $b['type'] ?? 'course';
        $itemPrice = 0;
        if ($type === 'course') {
            $itemPrice = (float)($courseMap[$b['courseId'] ?? '']['price'] ?? 0);
        } elseif ($type === 'hall') {
            $rate = (float)($hallMap[$b['hallId'] ?? '']['hourlyRate'] ?? 0);
            $hours = 2;
            if (!empty($b['slotStart']) && !empty($b['slotEnd'])) {
                $t1 = strtotime('2000-01-01 ' . $b['slotStart']);
                $t2 = strtotime('2000-01-01 ' . $b['slotEnd']);
                if ($t2 > $t1) $hours = round(($t2 - $t1) / 3600, 1);
            }
            $itemPrice = $rate * $hours;
        }

        $paid = isset($b['amountPaid']) ? (float)$b['amountPaid'] : ($b['status'] === 'confirmed' ? $itemPrice : 0);
        $remaining = max(0, $itemPrice - $paid);
        $dueDate = $b['paymentDueDate'] ?? '';

        if ($remaining > 0 && !empty($dueDate)) {
            // Alert if due date is today or passed or within 3 days
            if ($dueDate <= date('Y-m-d', strtotime('+3 days'))) {
                $b['remainingAmount'] = $remaining;
                $b['itemPrice']       = $itemPrice;
                $b['amountPaid']      = $paid;
                $reminders['paymentReminders'][] = $b;
            }
        }
    }

    return $reminders;
}

function update_booking_payment($bookingId, $amountPaid, $dueDate, $paymentNotes) {
    $phone = find_booking_owner($bookingId);
    if (!$phone) return false;

    return update_json_file(customer_file_path($phone), function ($data) use ($bookingId, $amountPaid, $dueDate, $paymentNotes) {
        foreach ($data['bookings'] as &$b) {
            if ($b['id'] === $bookingId) {
                $b['amountPaid']     = (float)$amountPaid;
                $b['paymentDueDate'] = trim($dueDate);
                $b['paymentNotes']   = trim($paymentNotes);
                $b['paymentUpdatedAt'] = date('c');
            }
        }
        unset($b);
        return $data;
    });
}


/* ===================== Phone Verification (OTP) ===================== */

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

function generate_otp_code($phone) {
    $clean = sanitize_phone($phone);
    $code  = (string)mt_rand(100000, 999999);
    $_SESSION['otp_' . $clean] = [
        'code'    => $code,
        'expires' => time() + 300, // Valid for 5 minutes
    ];
    return $code;
}

function verify_otp_code($phone, $inputCode) {
    $clean = sanitize_phone($phone);
    if (empty($_SESSION['otp_' . $clean])) return false;

    $stored = $_SESSION['otp_' . $clean];
    if (time() > $stored['expires']) {
        unset($_SESSION['otp_' . $clean]);
        return false;
    }

    if (trim((string)$inputCode) === (string)$stored['code']) {
        $_SESSION['otp_verified_' . $clean] = true;
        unset($_SESSION['otp_' . $clean]);
        return true;
    }

    return false;
}

function is_phone_verified($phone) {
    $clean = sanitize_phone($phone);
    return !empty($_SESSION['otp_verified_' . $clean]);
}

/* ===================== Course Batch & Direct Enrollment ===================== */

function enroll_student_in_course($phone, $fullName, $nationalId = '', $courseId = '', $branchId = '', $paidAmount = 0) {
    $ref = gen_booking_ref();
    $courses = load_courses();
    $course  = find_course($courses, $courseId);
    $price   = (float)($course['price'] ?? 0);

    $booking = [
        'id'               => gen_uid('bk'),
        'ref'              => $ref,
        'type'             => 'course',
        'courseId'         => $courseId,
        'status'           => 'confirmed',
        'branchId'         => $branchId,
        'amountPaid'       => (float)$paidAmount,
        'notes'            => 'تمت الإضافة والقبول يدوياً من لوحة التحكم',
        'createdAt'        => date('c'),
    ];
    $profile = [
        'phone'      => $phone,
        'fullName'   => $fullName,
        'email'      => '',
        'nationalId' => $nationalId,
    ];
    add_booking_for_customer($phone, $profile, $booking);

    // Increment seatsBooked
    update_json_file(COURSES_FILE, function ($all) use ($courseId) {
        foreach ($all as &$c) {
            if ($c['id'] === $courseId) {
                $c['seatsBooked'] = (int)($c['seatsBooked'] ?? 0) + 1;
            }
        }
        unset($c);
        return $all;
    });

    return $booking;
}


