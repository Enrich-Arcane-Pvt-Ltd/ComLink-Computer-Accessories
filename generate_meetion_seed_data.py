import json
import os
import re

dir_path = 'ProductData/Products-MEETION-NEW-PRICE-LIST-JULY-2026'
folders = [f for f in os.listdir(dir_path) if os.path.isdir(os.path.join(dir_path, f))]
folders.sort()

# Read the full PDF text
with open('scratch_meetion_pdf_text.txt', 'r', encoding='utf-8') as f:
    text = f.read()

# Exact price and data overrides for items with multi-line or specific names
overrides = {
    'MEETION C505 GAMING MOUSE KEYBOARD AND HEADSET COMBO': {
        'name': 'MEETION C505 4-in-1 Gaming Mouse, Keyboard, Headset & Mouse Pad Combo',
        'cash_price': 9570.00,
        'credit_price': 9955.00,
        'subcat': 'gaming-kits',
        'model': 'C505'
    },
    'MEETION C490 4 IN 1 PC GAMING KITS': {
        'name': 'MEETION C490 4-in-1 PC Gaming Kit (Keyboard, Mouse, Headset, Mouse Pad)',
        'cash_price': 6150.00,
        'credit_price': 6396.00,
        'subcat': 'gaming-kits',
        'model': 'C490'
    },
    'MEETION C510 BACKLIT GAMING COMBO 2 IN 1': {
        'name': 'MEETION C510 2-in-1 Backlit Gaming Keyboard & Mouse Combo',
        'cash_price': 5335.00,
        'credit_price': 5550.00,
        'subcat': 'gaming-kits',
        'model': 'C510'
    },
    'MEETION CHR31 GAMING CHAIR': {
        'name': 'MEETION CHR31 Ergonomic Reclining Gaming Chair with Scalable Footrest',
        'cash_price': 47500.00,
        'credit_price': 49400.00,
        'subcat': 'chairs',
        'model': 'CHR31'
    },
    'MEETION CHR32 GAMING CHAIR': {
        'name': 'MEETION CHR32 Heavy-Duty Ergonomic Gaming Chair with Scalable Footrest',
        'cash_price': 49900.00,
        'credit_price': 51896.00,
        'subcat': 'chairs',
        'model': 'CHR32'
    },
    'MEETION CHR25 2D ARMREST MASSAGE GAMING E SPORT CHAIR WITH FOOTREST': {
        'name': 'MEETION CHR25 2D Armrest Massage Gaming E-Sport Chair with Footrest',
        'cash_price': 49500.00,
        'credit_price': 51480.00,
        'subcat': 'chairs',
        'model': 'CHR25'
    },
    'MEETION DIRECTOR A2 WIRELESS DUAL-MODE ERGONOMIC KEYBOARD': {
        'name': 'MEETION Director A2 Wireless Dual-Mode Ergonomic Keyboard and Mouse Combo',
        'cash_price': 7095.00,
        'credit_price': 7380.00,
        'subcat': 'combo-packs',
        'model': 'DIRECTOR-A2'
    },
    'MEETION MK005BT RGB BACKLIT MECHANICAL GAMING KEYBOARD': {
        'name': 'MEETION MK005BT RGB Backlit Bluetooth & Wired Mechanical Gaming Keyboard',
        'cash_price': 8955.00,
        'credit_price': 9315.00,
        'subcat': 'keyboards',
        'model': 'MK005BT'
    },
    'MEETION MK04 TKL RGB BACKLIT MULTIMEDIA BLUE SWITCH': {
        'name': 'MEETION MK04 TKL RGB Backlit Multimedia Blue Switch Mechanical Keyboard',
        'cash_price': 9350.00,
        'credit_price': 9724.00,
        'subcat': 'keyboards',
        'model': 'MK04'
    },
    'MEETION Mini5000 WIRELESS DUAL MODE KEYBOARD AND MOSUE': {
        'name': 'MEETION Mini5000 Wireless Dual-Mode Keyboard and Mouse Combo Black',
        'cash_price': 4150.00,
        'credit_price': 4316.00,
        'subcat': 'combo-packs',
        'model': 'MINI5000'
    },
    'MEETION Mini4000 WIRELESS KEYBOARD AND MOSUE COMBO BLACK': {
        'name': 'MEETION Mini4000 Wireless Compact Keyboard and Mouse Combo Black',
        'cash_price': 3320.00,
        'credit_price': 3455.00,
        'subcat': 'combo-packs',
        'model': 'MINI4000'
    },
    'MEETION C2000 WIRELESS  BLUETOOTH KEYBOARD AND MOUSE  COMBO': {
        'name': 'MEETION C2000 Wireless & Bluetooth Dual-Mode Keyboard and Mouse Combo',
        'cash_price': 5300.00,
        'credit_price': 5512.00,
        'subcat': 'combo-packs',
        'model': 'C2000'
    },
    'MEETION C100 WIRED KEYBOARD AND MOUSE COMBO': {
        'name': 'MEETION C100 Standard Wired Keyboard and Optical Mouse Combo',
        'cash_price': 1575.00,
        'credit_price': 1638.00,
        'subcat': 'combo-packs',
        'model': 'C100'
    },
    'MEETION C235 FULL SIZE SILENT WIRED KEYBOARD AND MOUSE COMBO': {
        'name': 'MEETION C235 Full Size Silent Wired Keyboard and Mouse Combo',
        'cash_price': 2025.00,
        'credit_price': 2106.00,
        'subcat': 'combo-packs',
        'model': 'C235'
    },
    'MEETION BTC001 FOLDABLE KEYBOARD AND MOUSE COMBO': {
        'name': 'MEETION BTC001 Ultra-Slim Foldable Bluetooth Keyboard and Mouse Combo',
        'cash_price': 5225.00,
        'credit_price': 5434.00,
        'subcat': 'combo-packs',
        'model': 'BTC001'
    },
    'MEETION C4120 WIRELESS KEYBOARD AND MOUSE COMBO': {
        'name': 'MEETION C4120 2.4G Wireless Keyboard and Ergonomic Mouse Combo',
        'cash_price': 3425.00,
        'credit_price': 3562.00,
        'subcat': 'combo-packs',
        'model': 'C4120'
    },
    'MEETION CW295 SILENT WIRELESS KEYBOARD AND MOUSE COMBO': {
        'name': 'MEETION CW295 Silent Wireless Keyboard and Mouse Combo',
        'cash_price': 3960.00,
        'credit_price': 4120.00,
        'subcat': 'combo-packs',
        'model': 'CW295'
    },
    'MEETION C4130 WIRELESS ERGONOMIC KEYBOARD MOUSE COMBO': {
        'name': 'MEETION C4130 Wireless Ergonomic Curved Keyboard and Mouse Combo',
        'cash_price': 4290.00,
        'credit_price': 4460.00,
        'subcat': 'combo-packs',
        'model': 'C4130'
    },
    'MEETION CW235 WIRELESS KEYBOARD AND MOUSE COMBO': {
        'name': 'MEETION CW235 Full-Size Wireless Keyboard and Optical Mouse Combo',
        'cash_price': 3050.00,
        'credit_price': 3172.00,
        'subcat': 'combo-packs',
        'model': 'CW235'
    },
}

all_products = []

for folder in folders:
    fl = folder.lower()
    
    # Check overrides first
    if folder in overrides:
        ov = overrides[folder]
        name = ov['name']
        cash_price = ov['cash_price']
        credit_price = ov['credit_price']
        subcat = ov['subcat']
        model = ov['model']
        cat = 'products' if subcat == 'chairs' else 'peripherals'
    else:
        # Determine model
        # Find tokens
        tokens = [t for t in re.split(r'[^A-Za-z0-9]', folder) if len(t) >= 2 and t.upper() != 'MEETION']
        # Find price in text
        best_match_line = None
        best_score = 0
        for line in text.split('\n'):
            line_clean = line.strip()
            if not line_clean or line_clean.startswith('==='):
                continue
            score = sum(1 for t in tokens if t.lower() in line_clean.lower())
            if score > best_score:
                best_score = score
                best_match_line = line_clean
                
        prices = re.findall(r'[\d,]+\.\d{2}', best_match_line or '')
        cash_price = 0.0
        credit_price = 0.0
        if len(prices) >= 2:
            cash_price = float(prices[-2].replace(',', ''))
            credit_price = float(prices[-1].replace(',', ''))
        elif len(prices) == 1:
            cash_price = float(prices[0].replace(',', ''))
            credit_price = cash_price

        # Extract model
        # Look for alphanumeric codes like M360, R545, CP2020, MK009, etc.
        model_match = re.search(r'\b([A-Z]{1,4}[0-9]{2,5}[A-Z0-9_-]*)\b', folder)
        model = model_match.group(1) if model_match else tokens[0]

        # Determine Category & Subcategory
        if 'chair' in fl or 'chr' in fl:
            cat = 'products'
            subcat = 'chairs'
        elif 'gaming kit' in fl or 'c490' in fl or 'c510' in fl or 'c505' in fl:
            cat = 'peripherals'
            subcat = 'gaming-kits'
        elif 'combo' in fl:
            cat = 'peripherals'
            subcat = 'combo-packs'
        elif 'cooling pad' in fl or 'cp2' in fl or 'cp3' in fl or 'cp4' in fl or 'cp5' in fl:
            cat = 'peripherals'
            subcat = 'cooling-pads'
        elif 'mouse pad' in fl or 'p010' in fl or 'p100' in fl or 'pd005' in fl or 'pd015' in fl or 'pd120' in fl:
            cat = 'peripherals'
            subcat = 'mouse-pad'
        elif 'webcam' in fl or 'cam601' in fl:
            cat = 'peripherals'
            subcat = 'webcam'
        elif 'speaker' in fl or 'sp20' in fl or 'sp21' in fl:
            cat = 'peripherals'
            subcat = 'speakers'
        elif 'headset' in fl or 'hp0' in fl or 'bth003' in fl:
            cat = 'peripherals'
            subcat = 'headsets'
        elif 'keyboard' in fl or 'keyborad' in fl or 'btk' in fl or 'mk' in fl or 'k100' in fl or 'k200' in fl or 'k400' in fl or 'k410' in fl or 'k9' in fl or 'wk330' in fl or 'ikey' in fl or 'kb015' in fl:
            cat = 'peripherals'
            subcat = 'keyboards'
        elif 'mouse' in fl or 'btm' in fl or 'gm' in fl or 'm360' in fl or 'm371' in fl or 'm915' in fl or 'm930' in fl or 'm975' in fl or 'r390' in fl or 'r545' in fl or 'r546' in fl or 'r547' in fl or 'r560' in fl or 'r571' in fl or 'r600' in fl or 'minigo' in fl:
            cat = 'peripherals'
            subcat = 'mouse'
        else:
            cat = 'peripherals'
            subcat = 'mouse'
            
        # Clean title name
        # Capitalize words nicely
        name_clean = folder.replace('_', ' ')
        words = name_clean.split()
        title_words = []
        for w in words:
            if w.upper() in ['MEETION', 'RGB', 'USB', 'BT', 'TKL', 'LED', 'DPI', 'HD', 'FHD', 'HIFI', 'PRO', 'MINI', '2.4G', '2D', '4-IN-1', '2-IN-1', 'C490', 'C510', 'C505', 'HP020', 'HP021', 'HP022', 'HP030', 'HP002U', 'HP003U', 'SP2010', 'SP2011', 'SP2110', 'CAM601', 'CHR04', 'CHR05', 'CHR14', 'CHR15', 'CHR22', 'CHR25', 'CHR31', 'CHR32', 'CP2020', 'CP3030', 'CP4040', 'CP5050']:
                title_words.append(w.upper())
            elif re.match(r'^[A-Za-z0-9]+$', w):
                title_words.append(w.capitalize())
            else:
                title_words.append(w)
        name = " ".join(title_words)
        if not name.startswith('MEETION'):
            name = 'MEETION ' + name

    # Generate a unique product code: MEET-{MODEL}-{CAT}-{SUBCAT}
    code_sub = subcat.replace('-', '')[:3].upper()
    code_cat = cat[:3].upper()
    model_code = re.sub(r'[^A-Z0-9]', '', model.upper())[:7]
    product_code = f"MEET-{model_code}-{code_cat}-{code_sub}"

    all_products.append({
        'folder': folder,
        'model': model,
        'product_code': product_code,
        'name': name,
        'cat': cat,
        'subcat': subcat,
        'cash_price': cash_price,
        'credit_price': credit_price,
    })

# Ensure uniqueness of product codes
seen_codes = {}
for p in all_products:
    c = p['product_code']
    if c in seen_codes:
        seen_codes[c] += 1
        p['product_code'] = f"{c}-{seen_codes[c]:02d}"
    else:
        seen_codes[c] = 1

print(f"Generated {len(all_products)} products.")
with open('scratch_all_meetion_products.json', 'w', encoding='utf-8') as f:
    json.dump(all_products, f, indent=2)

print("Saved to scratch_all_meetion_products.json")

