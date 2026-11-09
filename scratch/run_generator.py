import json
import os

from build_post_data_all import all_products
import append_items_11_to_30
import append_items_21_to_40
import append_items_31_to_45
import append_items_41_to_58

print(f"Total products generated: {len(all_products)}")

# Verify all numbers 1 to 58 are present
nums = [p['num'] for p in all_products]
print(f"Unique product numbers: {len(set(nums))} (Min: {min(nums)}, Max: {max(nums)})")

target_path = r"database/seeders/post_products_data.json"
with open(target_path, 'w', encoding='utf-8') as f:
    json.dump(all_products, f, indent=2, ensure_ascii=False)

print(f"Successfully written {len(all_products)} products to {target_path} (File size: {os.path.getsize(target_path)} bytes)")
