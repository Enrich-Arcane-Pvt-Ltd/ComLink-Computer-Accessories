import json

# Let's inspect the existing 25 products from DB
with open('database/seeders/hikvision_products_data.json', encoding='utf-8') as f:
    existing = json.load(f)

existing_codes = {p['product_code']: p for p in existing}
print(f"Existing product codes ({len(existing_codes)}):")
for code, p in existing_codes.items():
    print(f"  {code}: {p['name']} (Sub: {p['sub_category_slug']})")

