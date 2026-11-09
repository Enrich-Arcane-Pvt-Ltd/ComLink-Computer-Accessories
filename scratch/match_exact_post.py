import openpyxl
import json
import re

wb = openpyxl.load_workbook(r"c:\Users\Enrich PC\Downloads\smartpc_products_list.xlsx")
ws = wb['Products']
excel_rows = list(ws.iter_rows(values_only=True))[1:]

with open('scratch/post_db_prods.json', 'r', encoding='utf-8') as f:
    db_prods = json.load(f)

# Build a smarter matcher
matched_db = set()
matched_pairs = []

for idx, r in enumerate(excel_rows, 1):
    num, page, name, brand, price, credit_price, notes = r[:7]
    best_p = None
    best_score = 0
    tokens = set([t.lower() for t in re.split(r'[^a-zA-Z0-9]', name) if len(t) >= 3])
    if brand:
        tokens.add(brand.lower())
        
    for p in db_prods:
        p_tokens = set([t.lower() for t in re.split(r'[^a-zA-Z0-9]', p['name']) if len(t) >= 3])
        score = len(tokens.intersection(p_tokens))
        if score > best_score:
            best_score = score
            best_p = p
            
    if best_score >= 2:
        matched_pairs.append((idx, name, brand, price, best_p, best_score))
        matched_db.add(best_p['id'])
    else:
        matched_pairs.append((idx, name, brand, price, None, 0))

print(f"Total Excel rows: {len(excel_rows)}")
print(f"Total matched to DB products: {len([m for m in matched_pairs if m[4]])}")
print(f"Distinct DB products matched: {len(matched_db)} / {len(db_prods)}")

unmatched_excel = [m for m in matched_pairs if not m[4]]
print(f"\nUnmatched Excel rows: {len(unmatched_excel)}")
for m in unmatched_excel:
    print(m[:4])

unmatched_db = [p for p in db_prods if p['id'] not in matched_db]
print(f"\nUnmatched DB products: {len(unmatched_db)}")
for p in unmatched_db:
    print(p['id'], p['product_code'], p['name'])

