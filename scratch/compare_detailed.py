import json
import re

# Existing 25 products
with open('database/seeders/hikvision_products_data.json', encoding='utf-8') as f:
    existing = json.load(f)

existing_by_sub = {}
for p in existing:
    sub = p['sub_category_slug']
    code = p['product_code'].upper().strip()
    name = p['name'].upper().strip()
    existing_by_sub.setdefault(sub, []).append({
        'code': code,
        'name': name,
        'orig': p
    })

print(f"Existing subcategories in seed data: {list(existing_by_sub.keys())}")
for sub, prods in existing_by_sub.items():
    print(f"  {sub}: {len(prods)} products -> {[p['code'] for p in prods]}")

