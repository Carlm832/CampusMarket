import requests
import re

session = requests.Session()
url = "https://www.campusmarketplace.site/pages/register.php"

# 1. GET to get cookies and CSRF token
print("Sending GET request...")
r_get = session.get(url)

token_match = re.search(r'name="csrf_token"\s+value="([^"]+)"', r_get.text)
if not token_match:
    print("No CSRF token found in the form! HTML:")
    print(r_get.text[:1000])
    exit(1)

csrf_token = token_match.group(1)

# 2. POST to submit form
data = {
    'csrf_token': csrf_token,
    'username': 'carlton_local',
    'email': '20223556@std.neu.edu.tr',
    'password': 'Carl.2002',
    'password_confirm': 'Carl.2002',
    'terms': '1'
}

print("Sending POST request...")
r_post = session.post(url, data=data)
print(f"POST Status: {r_post.status_code}")

print(f"Final URL: {r_post.url}")
error_match = re.findall(r'<div[^>]*class=["\'][^"\']*error-message[^"\']*["\'][^>]*>(.*?)</div>', r_post.text, re.DOTALL)
for e in error_match:
    print(f"UI ERROR: {e.strip()}")

with open("debug_post.html", "w", encoding="utf-8") as f:
    f.write(r_post.text)
print("Saved full response to debug_post.html")
