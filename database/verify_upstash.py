import urllib.request
import json
import sys

sys.stdout.reconfigure(encoding='utf-8')

URL = 'https://relieved-magpie-171147.upstash.io'
TOKEN = 'gQAAAAAAApyLAAIgcDFiZWM0MDAxZmEyMmQ0MGUzYmI0NjQ3OTA3ZGRmNzU2NQ'
headers = {
    'Authorization': f'Bearer {TOKEN}',
    'Content-Type': 'application/json'
}

def cmd(args):
    payload = json.dumps(args).encode('utf-8')
    req = urllib.request.Request(URL, data=payload, headers=headers)
    with urllib.request.urlopen(req) as resp:
        return json.loads(resp.read().decode())

courses = json.loads(cmd(['GET', 'lct:courses'])['result'])
halls = json.loads(cmd(['GET', 'lct:halls'])['result'])
settings = json.loads(cmd(['GET', 'lct:settings'])['result'])
phones = cmd(['SMEMBERS', 'lct:customers_set'])['result']

print('Upstash verification:')
print(f' - Courses in Redis: {len(courses)}')
print(f' - Halls in Redis: {len(halls)}')
print(f' - Branches in Redis: {len(settings.get("branches", []))}')
print(f' - Customers in Redis: {len(phones)}')

if phones:
    sample_c = json.loads(cmd(['GET', f'lct:customer:{phones[0]}'])['result'])
    print(f' - Sample customer ({phones[0]}): {sample_c.get("fullName")}, Bookings: {len(sample_c.get("bookings", []))}')
