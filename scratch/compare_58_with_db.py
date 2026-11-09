import openpyxl
import subprocess
import json

wb = openpyxl.load_workbook(r"c:\Users\Enrich PC\Downloads\smartpc_products_list.xlsx")
ws = wb['Products']
excel_rows = list(ws.iter_rows(values_only=True))[1:]

# Get all DB products from ID 537
cmd = r'C:\php8335\php.exe -r "require \"vendor/autoload.php\"; \$app = require_once \"bootstrap/app.php\"; \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); echo json_encode(App\Models\Product::where(\"id\", \">=\", 537)->get([\"id\", \"product_code\", \"name\", \"images\", \"price\", \"sale_price\"]));"'

res = subprocess.run(cmd, shell=True, capture_output=True, text=True)
db_prods = json.loads(res.stdout)
print(f"Total Excel rows: {len(excel_rows)}")
print(f"Total DB products from 537: {len(db_prods)}")

# Check each excel row against db_prods
db_names = [p['name'].lower() for p in db_prods]

for idx, r in enumerate(excel_rows, 1):
    name = r[2]
    # Check match
    found = False
    for p in db_prods:
        # compare
        clean_r = "".join(filter(str.isalnum, name)).lower()
        clean_p = "".join(filter(str.isalnum, p['name'])).lower()
        if clean_r in clean_p or clean_p in clean_r:
            found = p
            break
    if not found:
        print(f"NOT FOUND in DB: #{idx} | {name} ({r[3]}) | Price: {r[4]}")

