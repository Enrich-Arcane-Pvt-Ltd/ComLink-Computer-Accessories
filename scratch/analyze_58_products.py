import openpyxl
import os
import re

wb = openpyxl.load_workbook(r"c:\Users\Enrich PC\Downloads\smartpc_products_list.xlsx")
ws = wb['Products']

img_dir = r"ProductData\Post product Images"
available_images = os.listdir(img_dir)

rows = list(ws.iter_rows(values_only=True))[1:]
print(f"Loaded {len(rows)} products from Sheet 1")

# Test image matching
def find_image(prod_name, brand):
    # Normalize
    clean_name = re.sub(r'[^a-zA-Z0-9]', '', prod_name).lower()
    
    # 1. Direct contains check
    best_match = None
    best_score = 0
    for img in available_images:
        clean_img = re.sub(r'[^a-zA-Z0-9]', '', img).lower()
        # count common tokens
        tokens = [t for t in re.split(r'[^a-zA-Z0-9]', prod_name) if len(t) >= 3]
        matches = sum(1 for t in tokens if t.lower() in clean_img)
        if matches > best_score:
            best_score = matches
            best_match = img
            
    return best_match, best_score

for idx, r in enumerate(rows):
    num, page, name, brand, price, credit_price, notes = r[:7]
    img, score = find_image(name, brand)
    print(f"#{idx+1:2d} | Page {str(page):3s} | Brand: {str(brand):12s} | Price: {str(price):8s} | Name: {name[:35]:35s} | Img: {img} (score {score})")

