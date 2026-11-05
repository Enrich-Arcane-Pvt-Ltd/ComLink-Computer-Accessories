import json

data_file = 'database/seeders/aoas_weibo_products_data.json'
with open(data_file, 'r', encoding='utf-8') as f:
    items = json.load(f)

for item in items:
    if item.get('model') == 'FC-535':
        item['folder'] = 'AOAS FC-535 WIRED  COMPBO PACK'
        item['name'] = 'AOAS FC-535 Wired Desktop Keyboard and Mouse Combo Pack'
        item['description'] = 'The AOAS FC-535 Wired Desktop Keyboard and Mouse Combo Pack is a reliable, high-durability desktop set packed in a retail color box. Built with a full-sized 104-key spill-resistant keyboard and a precision optical mouse with 1200 DPI resolution, it delivers dependable plug-and-play USB performance for home and corporate workstations.'
        item['main_specs'] = {
            "package_includes": "Full-Size Wired Keyboard + Ergonomic Optical Mouse Set",
            "connectivity": "Wired USB Plug & Play (1.5m High-Flex Cable)",
            "keyboard_type": "Standard 104-Key Full-Size Membrane Keyboard",
            "mouse_dpi": "1200 DPI Precision Optical Sensor",
            "silent_clicking": "Low-Noise Tactile Membrane Keystrokes",
            "wireless_range": "1.5m Wired USB Connection"
        }
        item['tech_specs'] = {
            "model_series": "AOAS FC-535 Wired Combo",
            "category_segment": "Desktop Keyboard & Optical Mouse Set",
            "condition": "Brand New (Original Color Box Packaging)",
            "interface_connection": "High-Speed USB 2.0 / 3.0 Plug & Play",
            "architecture": "Spill-Resistant Reinforced ABS Housing",
            "thermal_efficiency": "Low Power Cool Operation (0°C to 45°C)",
            "power_consumption": "Standard USB 5V Bus-Powered (<100mA)",
            "operating_systems": "Windows 11 / 10 / 8 / 7 / XP, Linux, macOS",
            "certification": "CE, FCC, RoHS Certified",
            "dimensions_weight": "Keyboard: 440 × 140 × 26 mm (~460g); Mouse: 115 × 62 × 38 mm (~80g)",
            "included_in_box": "1x AOAS FC-535 Wired Keyboard, 1x AOAS Wired Optical Mouse, User Manual"
        }

    elif item.get('model') == 'AS-110':
        item['folder'] = 'AOAS AS-110 MINI  BLUETOOTH COMBO PACK'
        item['name'] = 'AOAS AS-110 Mini Bluetooth Rechargeable Combo Pack'
        item['has_variants'] = True
        item['description'] = 'The AOAS AS-110 Mini Bluetooth Rechargeable Combo Pack is an ultra-portable, stylish wireless set engineered for tablets, laptops, phones, and PCs. Features an ultra-slim compact keyboard and a silent ergonomic optical mouse with built-in rechargeable lithium batteries. Available in four beautiful pastel color finishes: Pure White, Pastel Pink, Mint Green, and Pastel Yellow.'
        item['main_specs'] = {
            "package_includes": "Rechargeable Mini Bluetooth Keyboard + Slim Optical Mouse",
            "connectivity": "Dual-Mode Bluetooth 5.0 + 2.4G Wireless Dongle",
            "keyboard_type": "Ultra-Slim 78-Key Compact Scissor/Chiclet Keyboard",
            "mouse_dpi": "800 - 1200 - 1600 DPI Adjustable Tracking",
            "silent_clicking": "Whisper-Quiet Silent Switch Keys & Mouse Buttons",
            "wireless_range": "Up to 10 Meters (33ft) Stable Range"
        }
        item['tech_specs'] = {
            "model_series": "AOAS AS-110 Mini Series",
            "category_segment": "Portable Bluetooth & 2.4G Rechargeable Combo Set",
            "condition": "Brand New (Original Factory Sealed)",
            "interface_connection": "Bluetooth 5.0 / 3.0 + 2.4GHz USB Nano Receiver",
            "architecture": "Slimline Lightweight ABS with Soft-Touch Pastel Finish",
            "thermal_efficiency": "Intelligent Auto-Sleep Power Saving Mode",
            "power_consumption": "Built-in Rechargeable Li-ion Battery (USB Charging Cable Included)",
            "operating_systems": "Windows 11/10/8, macOS, iOS/iPadOS, Android, ChromeOS",
            "certification": "CE, FCC, RoHS Certified",
            "dimensions_weight": "Keyboard: 247 × 150 × 6 mm (~190g); Mouse: 112 × 57 × 25 mm (~65g)",
            "included_in_box": "1x AOAS AS-110 Mini Keyboard, 1x Silent Optical Mouse, 1x USB Nano Receiver, 1x USB Charging Cable, User Manual"
        }
        item['variants'] = [
            {
                "color_name": "Pure White",
                "variant_name": "AOAS AS-110 Mini Bluetooth Combo - Pure White",
                "image_file": "AOAS AS-110 MINI BLUETOOTH COMBO (5).png",
                "sku": "AS-110-WHT",
                "stock": 10
            },
            {
                "color_name": "Pastel Pink",
                "variant_name": "AOAS AS-110 Mini Bluetooth Combo - Pastel Pink",
                "image_file": "AOAS AS-110 MINI BLUETOOTH COMBO (3).png",
                "sku": "AS-110-PNK",
                "stock": 10
            },
            {
                "color_name": "Mint Green",
                "variant_name": "AOAS AS-110 Mini Bluetooth Combo - Mint Green",
                "image_file": "AOAS AS-110 MINI BLUETOOTH COMBO (2).png",
                "sku": "AS-110-GRN",
                "stock": 10
            },
            {
                "color_name": "Pastel Yellow",
                "variant_name": "AOAS AS-110 Mini Bluetooth Combo - Pastel Yellow",
                "image_file": "AOAS AS-110 MINI BLUETOOTH COMBO (4).png",
                "sku": "AS-110-YLW",
                "stock": 10
            }
        ]

with open(data_file, 'w', encoding='utf-8') as f:
    json.dump(items, f, indent=2, ensure_ascii=False)

print("Updated aoas_weibo_products_data.json successfully!")

