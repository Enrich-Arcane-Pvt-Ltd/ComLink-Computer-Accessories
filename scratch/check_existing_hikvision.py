import json

data = json.load(open('database/seeders/hikvision_products_data.json', encoding='utf-8'))
print(f"Total previous hikvision products: {len(data)}")
for p in data:
    print(f"- {p['product_code']} | {p['name']} | Sub: {p['sub_category_slug']} | Price: {p['sale_price']}")

