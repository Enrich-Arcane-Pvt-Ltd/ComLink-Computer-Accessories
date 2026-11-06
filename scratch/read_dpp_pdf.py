import pypdf

reader = pypdf.PdfReader('ProductData/HIKVISION-DPP.pdf')
print(f"Total pages: {len(reader.pages)}")

for i, page in enumerate(reader.pages):
    print(f"\n=================== PAGE {i+1} ===================")
    text = page.extract_text()
    print(text)

