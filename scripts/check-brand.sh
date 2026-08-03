#!/bin/bash
curl -sS --max-time 12 "https://bilohash.com/myclient/?lang=en" -o /tmp/mcen.html
python3 - <<'PY'
t=open('/tmp/mcen.html','r',encoding='utf-8',errors='replace').read()
print('Sites', t.count('My Sites'))
print('Clients', t.count('My Clients'))
import re
m=re.search(r'My <em>[^<]+</em>', t)
print('logo', m.group(0) if m else 'none')
PY
