import openpyxl
import json
import re

wb = openpyxl.load_workbook(r"c:\Users\Enrich PC\Downloads\smartpc_products_list.xlsx")
ws = wb['Products']
excel_rows = list(ws.iter_rows(values_only=True))[1:]

with open('scratch/post_db_prods.json', 'r', encoding='utf-8') as f:
    db_prods = json.load(f)

print(f"Total Excel rows: {len(excel_rows)}")
print(f"Total DB products: {len(db_prods)}")

unmatched_excel = []
matched = []

for idx, r in enumerate(excel_rows, 1):
    name = r[2]
    clean_r = re.sub(r'[^a-zA-Z0-9]', '', name).lower()
    
    found = None
    for p in db_prods:
        clean_p = re.sub(r'[^a-zA-Z0-9]', '', p['name']).lower()
        if clean_r in clean_p or clean_p in clean_r:
            found = p
            break
            
    if found:
        matched.append((idx, name, found))
    else:
        unmatched_excel.append((idx, name, r))

print(f"\nMatched: {len(matched)}")
print(f"Unmatched in Excel: {len(unmatched_excel)}")
for idx, name, r in unmatched_excel:
    print(f"  Missing #{idx}: {name} ({r[3]}) | Page {r[1]} | Price {r[4]}")

