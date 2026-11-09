import json
import re
import os
import openpyxl

wb = openpyxl.load_workbook(r"c:\Users\Enrich PC\Downloads\smartpc_products_list.xlsx")
ws = wb['Products']
excel_rows = list(ws.iter_rows(values_only=True))[1:]

print(f"Total rows to process: {len(excel_rows)}")

