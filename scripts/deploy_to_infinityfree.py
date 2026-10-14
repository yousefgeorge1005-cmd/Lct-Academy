import ftplib
import os
import sys

sys.stdout.reconfigure(encoding='utf-8')

FTP_HOST = 'ftpupload.net'
FTP_USER = 'if0_42608008'
FTP_PASS = 'SJS5FqmTITdppz'

local_root = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

# Files to sync/upload to InfinityFree to enable Upstash Redis & the latest backend fixes
files_to_deploy = [
    'includes/upstash.php',
    'includes/config.php',
    'includes/functions.php',
    'includes/cloudinary.php',
    'admin/login.php',
    'admin/logout.php',
    '.env',
]

def deploy():
    print('1. Connecting to InfinityFree FTP...')
    ftp = ftplib.FTP(FTP_HOST, timeout=30)
    ftp.login(FTP_USER, FTP_PASS)
    ftp.set_pasv(True)
    ftp.encoding = 'utf-8'

    print('2. Uploading updated files to /htdocs...')
    for rel in files_to_deploy:
        local_path = os.path.join(local_root, rel.replace('/', os.sep))
        remote_path = f'/htdocs/{rel}'
        
        if not os.path.exists(local_path):
            print(f'   [SKIP] Local file not found: {rel}')
            continue
            
        remote_dir = os.path.dirname(remote_path)
        # Ensure remote directory exists
        try:
            ftp.cwd(remote_dir)
        except Exception:
            # Create if needed
            ftp.mkd(remote_dir)
            ftp.cwd(remote_dir)

        fname = os.path.basename(remote_path)
        print(f'   Uploading {rel} -> {remote_path}...')
        with open(local_path, 'rb') as fp:
            ftp.storbinary(f'STOR {fname}', fp)
        print(f'   [OK] {rel} deployed successfully.')

    ftp.quit()
    print('\nAll updated files deployed to InfinityFree successfully!')

if __name__ == '__main__':
    deploy()
