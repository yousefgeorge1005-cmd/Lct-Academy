import json
import glob
import os

def esc(v):
    if v is None:
        return 'NULL'
    if isinstance(v, (int, float)):
        return str(v)
    if isinstance(v, bool):
        return '1' if v else '0'
    s = str(v).replace('\\', '\\\\').replace("'", "''")
    return f"'{s}'"

def to_dt(s):
    if not s:
        return 'NOW()'
    s = s.replace('T', ' ')
    if '+' in s:
        s = s.split('+')[0]
    elif '-' in s and s.count('-') > 2:
        s = s.rsplit('-', 1)[0]
    return f"'{s.strip()}'"

base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
data_dir = os.path.join(base_dir, 'data')

sql_lines = []
sql_lines.append('-- =============================================================')
sql_lines.append('-- LCT Academy — بيانات حية كاملة (الفروع، القاعات، الكورسات، العملاء، الحجوزات)')
sql_lines.append('-- =============================================================')
sql_lines.append('SET NAMES utf8mb4;')
sql_lines.append('SET FOREIGN_KEY_CHECKS = 0;')

# Branches
with open(os.path.join(data_dir, 'settings.json'), 'r', encoding='utf-8') as f:
    settings = json.load(f)

sql_lines.append('\n-- 1. الفروع (Branches)')
sql_lines.append('TRUNCATE TABLE `branches`;')
for b in settings.get('branches', []):
    bid = esc(b.get('id'))
    name_ar = esc(b.get('name', {}).get('ar'))
    name_en = esc(b.get('name', {}).get('en'))
    addr_ar = esc(b.get('address', {}).get('ar'))
    addr_en = esc(b.get('address', {}).get('en'))
    phone = esc(b.get('phone'))
    email = esc(b.get('email'))
    hrs_ar = esc(b.get('hours', {}).get('ar'))
    hrs_en = esc(b.get('hours', {}).get('en'))
    sql_lines.append(f'INSERT INTO `branches` (`id`, `name_ar`, `name_en`, `address_ar`, `address_en`, `phone`, `email`, `hours_ar`, `hours_en`) VALUES ({bid}, {name_ar}, {name_en}, {addr_ar}, {addr_en}, {phone}, {email}, {hrs_ar}, {hrs_en});')

# Halls
with open(os.path.join(data_dir, 'halls.json'), 'r', encoding='utf-8') as f:
    halls = json.load(f)

sql_lines.append('\n-- 2. القاعات (Halls)')
sql_lines.append('TRUNCATE TABLE `halls`;')
for h in halls:
    hid = esc(h.get('id'))
    bid = esc(h.get('branchId'))
    name_ar = esc(h.get('name', {}).get('ar') if isinstance(h.get('name'), dict) else h.get('name'))
    name_en = esc(h.get('name', {}).get('en') if isinstance(h.get('name'), dict) else h.get('name'))
    type_ar = esc(h.get('type', {}).get('ar') if isinstance(h.get('type'), dict) else h.get('type'))
    type_en = esc(h.get('type', {}).get('en') if isinstance(h.get('type'), dict) else h.get('type'))
    cap = int(h.get('capacity', 0) or 0)
    price = float(h.get('pricePerHour', 0) or 0)
    sb = 1 if h.get('hasSmartboard') else 0
    proj = 1 if h.get('hasProjector') else 0
    ac = 1 if h.get('hasAc') else 0
    sql_lines.append(f'INSERT INTO `halls` (`id`, `branch_id`, `name_ar`, `name_en`, `type_ar`, `type_en`, `capacity`, `price_per_hour`, `has_smartboard`, `has_projector`, `has_ac`) VALUES ({hid}, {bid}, {name_ar}, {name_en}, {type_ar}, {type_en}, {cap}, {price}, {sb}, {proj}, {ac});')

# Courses
with open(os.path.join(data_dir, 'courses.json'), 'r', encoding='utf-8') as f:
    courses = json.load(f)

sql_lines.append('\n-- 3. الكورسات وعناصر المنهج (Courses & Curriculum)')
sql_lines.append('TRUNCATE TABLE `courses`;')
sql_lines.append('TRUNCATE TABLE `course_curriculum_items`;')
for c in courses:
    cid = esc(c.get('id'))
    code = esc(c.get('code'))
    cat = esc(c.get('category'))
    icon = esc(c.get('icon'))
    st = esc(c.get('status', 'active'))
    name_ar = esc(c.get('name', {}).get('ar') if isinstance(c.get('name'), dict) else c.get('name'))
    name_en = esc(c.get('name', {}).get('en') if isinstance(c.get('name'), dict) else c.get('name'))
    tag_ar = esc(c.get('tagline', {}).get('ar') if isinstance(c.get('tagline'), dict) else c.get('tagline'))
    tag_en = esc(c.get('tagline', {}).get('en') if isinstance(c.get('tagline'), dict) else c.get('tagline'))
    desc_ar = esc(c.get('desc', {}).get('ar') if isinstance(c.get('desc'), dict) else c.get('desc'))
    desc_en = esc(c.get('desc', {}).get('en') if isinstance(c.get('desc'), dict) else c.get('desc'))
    lvl_ar = esc(c.get('level', {}).get('ar') if isinstance(c.get('level'), dict) else c.get('level'))
    lvl_en = esc(c.get('level', {}).get('en') if isinstance(c.get('level'), dict) else c.get('level'))
    dur_ar = esc(c.get('duration', {}).get('ar') if isinstance(c.get('duration'), dict) else c.get('duration'))
    dur_en = esc(c.get('duration', {}).get('en') if isinstance(c.get('duration'), dict) else c.get('duration'))
    price = float(c.get('price', 0) or 0)
    inst_ar = esc(c.get('instructor', {}).get('ar') if isinstance(c.get('instructor'), dict) else c.get('instructor'))
    inst_en = esc(c.get('instructor', {}).get('en') if isinstance(c.get('instructor'), dict) else c.get('instructor'))
    sch_ar = esc(c.get('schedule', {}).get('ar') if isinstance(c.get('schedule'), dict) else c.get('schedule'))
    sch_en = esc(c.get('schedule', {}).get('en') if isinstance(c.get('schedule'), dict) else c.get('schedule'))
    seats_tot = int(c.get('seatsTotal', 0) or 0)
    seats_bk = int(c.get('seatsBooked', 0) or 0)
    pre_ar = esc(c.get('prerequisites', {}).get('ar') if isinstance(c.get('prerequisites'), dict) else c.get('prerequisites'))
    pre_en = esc(c.get('prerequisites', {}).get('en') if isinstance(c.get('prerequisites'), dict) else c.get('prerequisites'))
    sql_lines.append(f'INSERT INTO `courses` (`id`, `code`, `category`, `icon`, `status`, `name_ar`, `name_en`, `tagline_ar`, `tagline_en`, `description_ar`, `description_en`, `level_ar`, `level_en`, `duration_ar`, `duration_en`, `price`, `instructor_ar`, `instructor_en`, `schedule_ar`, `schedule_en`, `seats_total`, `seats_booked`, `prerequisites_ar`, `prerequisites_en`) VALUES ({cid}, {code}, {cat}, {icon}, {st}, {name_ar}, {name_en}, {tag_ar}, {tag_en}, {desc_ar}, {desc_en}, {lvl_ar}, {lvl_en}, {dur_ar}, {dur_en}, {price}, {inst_ar}, {inst_en}, {sch_ar}, {sch_en}, {seats_tot}, {seats_bk}, {pre_ar}, {pre_en});')

    curriculum = c.get('curriculum', [])
    if isinstance(curriculum, list):
        for pos, item in enumerate(curriculum, start=1):
            if isinstance(item, dict):
                iar = esc(item.get('ar'))
                ien = esc(item.get('en'))
            else:
                iar = esc(item)
                ien = esc(item)
            sql_lines.append(f'INSERT INTO `course_curriculum_items` (`course_id`, `position`, `item_ar`, `item_en`) VALUES ({cid}, {pos}, {iar}, {ien});')

# Customers & Bookings
customers_dir = os.path.join(data_dir, 'customers')
files = sorted(glob.glob(os.path.join(customers_dir, '*.json')))
sql_lines.append(f'\n-- 4. العملاء ({len(files)} عميل) والحجوزات')
sql_lines.append('TRUNCATE TABLE `customers`;')
sql_lines.append('TRUNCATE TABLE `bookings`;')

bookings_count = 0
for fpath in files:
    with open(fpath, 'r', encoding='utf-8') as fp:
        cdata = json.load(fp)
    phone = esc(cdata.get('phone'))
    full_name = esc(cdata.get('fullName'))
    email = esc(cdata.get('email'))
    nat_id = esc(cdata.get('nationalId'))
    created = to_dt(cdata.get('createdAt'))
    sql_lines.append(f'INSERT INTO `customers` (`phone`, `full_name`, `email`, `national_id`, `created_at`) VALUES ({phone}, {full_name}, {email}, {nat_id}, {created});')

    for b in cdata.get('bookings', []):
        bookings_count += 1
        bid = esc(b.get('id'))
        bref = esc(b.get('ref'))
        btype = esc(b.get('type', 'course'))
        bstatus = esc(b.get('status', 'pending'))
        bcid = esc(b.get('courseId'))
        bbid = esc(b.get('branchId'))
        bhid = esc(b.get('hallId'))
        bdate = esc(b.get('bookingDate'))
        bstart = esc(b.get('startTime'))
        bend = esc(b.get('endTime'))
        bcount = int(b.get('personCount', 1) or 1)
        bprice = float(b.get('totalPrice', 0) or 0)
        bnotes = esc(b.get('notes'))
        bpay_photo = esc(b.get('paymentPhotoPath'))
        bid_photo = esc(b.get('idPhotoPath'))
        bcreated = to_dt(b.get('createdAt'))

        sql_lines.append(f'INSERT INTO `bookings` (`id`, `ref`, `customer_phone`, `type`, `status`, `course_id`, `branch_id`, `hall_id`, `booking_date`, `start_time`, `end_time`, `person_count`, `total_price`, `notes`, `payment_photo_url`, `id_photo_url`, `created_at`) VALUES ({bid}, {bref}, {phone}, {btype}, {bstatus}, {bcid}, {bbid}, {bhid}, {bdate}, {bstart}, {bend}, {bcount}, {bprice}, {bnotes}, {bpay_photo}, {bid_photo}, {bcreated});')

# Admin Users
with open(os.path.join(data_dir, 'admin.json'), 'r', encoding='utf-8') as f:
    admin_data = json.load(f)

sql_lines.append('\n-- 5. حسابات الإدارة (Admin Users)')
sql_lines.append('TRUNCATE TABLE `admin_users`;')
for u in admin_data.get('users', []):
    urole = esc(u.get('role'))
    uhash = esc(u.get('password_hash'))
    sql_lines.append(f'INSERT INTO `admin_users` (`role`, `password_hash`) VALUES ({urole}, {uhash});')

sql_lines.append('\nSET FOREIGN_KEY_CHECKS = 1;')

output_path = os.path.join(base_dir, 'database', 'lct_live_data_backup.sql')
with open(output_path, 'w', encoding='utf-8') as out:
    out.write('\n'.join(sql_lines) + '\n')

print(f'Done! Successfully exported {len(files)} customers and {bookings_count} bookings to {output_path}')
