import openpyxl

wb = openpyxl.load_workbook(r"c:\Users\Enrich PC\Downloads\smartpc_products_list.xlsx")
for sname in wb.sheetnames:
    ws = wb[sname]
    print(f"Sheet '{sname}' has {ws.max_row} rows")

ws = wb[wb.sheetnames[0]]
for idx, r in enumerate(ws.iter_rows(values_only=True)):
    if idx == 0:
        print("Header:", r)
    else:
        print(f"Row {idx+1:2d}: {r}")

