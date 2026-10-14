import ftplib
import json
import os
import io
import urllib.request
import sys

sys.stdout.reconfigure(encoding='utf-8')

UPSTASH_URL = 'https://relieved-magpie-171147.upstash.io'
UPSTASH_TOKEN = 'gQAAAAAAApyLAAIgcDFiZWM0MDAxZmEyMmQ0MGUzYmI0NjQ3OTA3ZGRmNzU2NQ'
UPSTASH_HEADERS = {
    'Authorization': f'Bearer {UPSTASH_TOKEN}',
    'Content-Type': 'application/json'
}

def upstash_pipeline(commands):
    payload = json.dumps(commands, ensure_ascii=False).encode('utf-8')
    req = urllib.request.Request(f'{UPSTASH_URL}/pipeline', data=payload, headers=UPSTASH_HEADERS)
    with urllib.request.urlopen(req, timeout=30) as resp:
        return json.loads(resp.read().decode())

local_root = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
local_data = os.path.join(local_root, 'data')
local_cust = os.path.join(local_data, 'customers')
os.makedirs(local_cust, exist_ok=True)

print('1. Connecting to InfinityFree FTP...')
ftp = ftplib.FTP('ftpupload.net', timeout=30)
ftp.login('if0_42608008', 'SJS5FqmTITdppz')
ftp.set_pasv(True)
ftp.encoding = 'utf-8'

print('2. Syncing core data files...')
ftp.cwd('/htdocs/data')

core_files = ['courses.json', 'halls.json', 'settings.json', 'admin.json', 'blocked_numbers.json', 'cloudinary_credentials.json', 'translations.json']
core_pipeline = []
for cf in core_files:
    try:
        buf = io.BytesIO()
        ftp.retrbinary(f'RETR {cf}', buf.write)
        buf.seek(0)
        content_bytes = buf.read()
        local_cf_path = os.path.join(local_data, cf)
        with open(local_cf_path, 'wb') as lf:
            lf.write(content_bytes)
        
        parsed = json.loads(content_bytes.decode('utf-8'))
        redis_key = f'lct:{cf.replace(".json", "")}'
        core_pipeline.append(['SET', redis_key, json.dumps(parsed, ensure_ascii=False)])
        print(f'   Synced {cf} -> {redis_key}')
    except Exception as e:
        print(f'   Warning syncing {cf}: {e}')

if core_pipeline:
    upstash_pipeline(core_pipeline)

print('3. Fetching all customer files from InfinityFree...')
ftp.cwd('/htdocs/data/customers')
file_list = []
ftp.retrlines('NLST', file_list.append)

customer_files = [f for f in file_list if f.endswith('.json')]
print(f'   Found {len(customer_files)} customer JSON files on InfinityFree.')

all_phones = []
total_bookings = 0
cust_pipeline = []

for idx, cf in enumerate(customer_files, start=1):
    try:
        buf = io.BytesIO()
        ftp.retrbinary(f'RETR {cf}', buf.write)
        buf.seek(0)
        cdata_raw = buf.read()
        
        local_path = os.path.join(local_cust, cf)
        with open(local_path, 'wb') as lf:
            lf.write(cdata_raw)
            
        cdata = json.loads(cdata_raw.decode('utf-8'))
        phone = cdata.get('phone') or cf.replace('.json', '')
        all_phones.append(phone)
        total_bookings += len(cdata.get('bookings', []))
        
        cust_pipeline.append(['SET', f'lct:customer:{phone}', json.dumps(cdata, ensure_ascii=False)])
        cust_pipeline.append(['SADD', 'lct:customers_set', phone])
    except Exception as e:
        print(f'   Error syncing {cf}: {e}')

ftp.quit()

print(f'4. Pushing {len(customer_files)} customers to Upstash Redis...')
cust_pipeline.append(['SET', 'lct:customers_phones', json.dumps(all_phones)])

# Send in batches of 100 commands
batch_size = 100
for i in range(0, len(cust_pipeline), batch_size):
    batch = cust_pipeline[i:i + batch_size]
    upstash_pipeline(batch)

print(f'\nSuccess! Synced {len(customer_files)} customers and {total_bookings} bookings from InfinityFree into Upstash Redis & local project!')
