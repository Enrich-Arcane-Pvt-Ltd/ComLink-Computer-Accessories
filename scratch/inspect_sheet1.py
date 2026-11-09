import openpyxl

wb = openpyxl.load_workbook(r"c:\Users\Enrich PC\Downloads\smartpc_products_list.xlsx")
ws = wb['Products']

rows = list(ws.iter_rows(values_only=True))
headers = rows[0]
print(f"Total rows in Sheet 'Products': {len(rows)-1}")

for r in rows[1:]:
    num, page, name, brand, price, credit_price, notes = r[:7]
    print(f"#{num:2d} | Page {str(page):3s} | Brand: {str(brand):12s} | Price: {str(price):8s} | Name: {name} | Notes: {notes}")

