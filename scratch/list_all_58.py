import openpyxl

wb = openpyxl.load_workbook(r"c:\Users\Enrich PC\Downloads\smartpc_products_list.xlsx")
ws = wb['Products']
rows = list(ws.iter_rows(values_only=True))[1:]

print(f"Total rows: {len(rows)}")
for idx, r in enumerate(rows, 1):
    num, page, name, brand, price, credit_price, notes = r[:7]
    print(f"{idx:2d}. [{brand}] {name} | Price: {price} | Credit: {credit_price} | Notes: {notes}")

