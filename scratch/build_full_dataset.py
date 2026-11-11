import json
import os
import openpyxl

wb = openpyxl.load_workbook(r"c:\Users\Enrich PC\Downloads\smartpc_products_list.xlsx")
ws = wb['Products']
excel_rows = list(ws.iter_rows(values_only=True))[1:]

image_filenames = {
    1: 'Data_Traveler_128GB_USB_Flash_Drive-removebg-preview.png',
    2: 'M700 M700U 650VA Line Interactive UPS.png',
    3: 'Prime_128GB_USB_Flash_Drive-removebg-preview.png',
    4: 'Prime_64GB_USB_Flash_Drive-removebg-preview.png',
    5: 'Neo_Lite_64GB_Micro_SD_Card__V30_-removebg-preview.png',
    6: 'Neo_Lite_128GB_Micro_SD_Card__V30_-removebg-preview.png',
    7: 'Neo_Lite_64GB_Micro_SD_Card-removebg-preview.png',
    8: 'BB-24751_24_TN_Office_Monitor-removebg-preview.png',
    9: 'Neo_Lite_128GB_Micro_SD_Card-removebg-preview.png',
    10: 'WRE12C_AC1200_Dual_Band_Wi-Fi_Extender-removebg-preview.png',
    11: '450W Power Supply.png',
    12: 'imageCLASS_LBP6030_Laser_Printer-removebg-preview.png',
    13: 'BT163 Wireless Music Receiver (Bluetooth).png',
    14: '128GB_SATA_SSD_Silk__S_-removebg-preview.png',
    15: '2.5_SSD_Gold_Series_256GB__OS003_-removebg-preview.png',
    16: '256GB_SATA_SSD_Silk__S_-removebg-preview.png',
    17: '256GB_NVMe_SSD_Silk__P_-removebg-preview.png',
    18: 'M.2_2280_Vi3000_512GB_NVMe_SSD-removebg-preview.png',
    19: 'PIXMA_G2010_Printer-removebg-preview.png',
    20: '128GB_NVMe_SSD_Silk__P_-removebg-preview.png',
    21: 'R16_8GB_USB_Flash_Drive-removebg-preview.png',
    22: '64GB_Micro_SD_Card-removebg-preview.png',
    23: 'Samsung_27_TV_Monitor-removebg-preview.png',
    24: 'K009_64GB_Pen_Drive-removebg-preview.png',
    25: 'CR2032_Lithium_Battery__5_pcs_-removebg-preview.png',
    26: 'CR2032_Lithium_Battery__5_pcs_-removebg-preview.png',
    27: 'P2500W_Monochrome_Laser_Printer-removebg-preview.png',
    28: 'KP-560_Disco_Series_Wireless_Soundbar-removebg-preview.png',
    29: 'BB24120HD_24_IPS_Gaming_Monitor-removebg-preview.png',
    30: 'BB22751_22_Office_Monitor-removebg-preview.png',
    31: 'M2710_27_100Hz_Monitor-removebg-preview.png',
    32: 'BB19750_19_Office_Monitor-removebg-preview.png',
    33: 'HDMI_60M_Extender-removebg-preview.png',
    34: '4_Port_USB_2.0_Hub-removebg-preview.png',
    35: '4G_N300_Wi-Fi_LTE_Router__DS-3WR4G3N_-removebg-preview.png',
    36: 'SA22_Outdoor_Rainproof_Power_Adapter-removebg-preview.png',
    37: '128GB_Micro_SD_Card__Canvas_Select_Plus__kINGSTON-removebg-preview.png',
    38: 'Laptop_Backpack_15.6-removebg-preview.png',
    39: 'Essential_Backpack_15-removebg-preview.png',
    40: 'ThinkBook Laptop Backpack.png',
    41: 'H610M S2 DDR4 Motherboard.png',
    42: 'H310M_S2_2.0_Motherboard-removebg-preview.png',
    43: 'H110M-DS2V_Gold_Motherboard-removebg-preview.png',
    44: 'Laptop_Bag__Black_-removebg-preview.png',
    45: 'H81M_DS2_Motherboard-removebg-preview.png',
    46: 'H410M_S2_V3_Motherboard-removebg-preview.png',
    47: '19_LED_Monitor-removebg-preview.png',
    48: '22_LED_Monitor-removebg-preview.png',
    49: '15-FD0689TU_Laptop__i5_1334U__8GB__512GB_NVMe_-removebg-preview.png',
    50: '12V_7.5Ah_UPS_Battery-removebg-preview.png',
    51: 'SSD 128GB NVMe.png',
    52: 'SSD_256GB_NVMe-removebg-preview.png',
    53: 'SSD_512GB_NVMe-removebg-preview.png',
    54: 'SSD_1TB_NVMe-removebg-preview.png',
    55: 'SSD_128GB_SATA-removebg-preview.png',
    56: 'SSD_256GB_SATA-removebg-preview .png',
    57: 'SSD_512GB_SATA-removebg-preview.png',
    58: 'SSD_1TB_SATA-removebg-preview.png',
}

products = []

# Helper to compute pricing
def calc_pricing(p_val, credit_val=None):
    if p_val is None or float(p_val) <= 0:
        return 0.0, 0.0, 0.0
    sale = float(p_val)
    # credit price or standard 10% markup for list price
    orig = float(credit_val) if credit_val else round(sale * 1.10, 2)
    cost = round(sale * 0.82, 2)
    return orig, sale, cost

# Let's define the comprehensive product specifications for all 58 items
items_spec = [
    # 1
    {
        "num": 1,
        "name": "Kingston DataTraveler Exodia 128GB USB 3.2 Gen 1 Flash Drive",
        "product_code": "KIN-DTX-128G",
        "slug": "kingston-datatraveler-exodia-128gb-usb-flash-drive",
        "brand_slug": "kingston",
        "category_slug": "products",
        "sub_category_slug": "flash-drives",
        "sub_category_name": "Flash Drives",
        "warranty_duration": 5,
        "warranty_unit": "Years",
        "stock": 40,
        "description": "The Kingston DataTraveler Exodia delivers USB 3.2 Gen 1 performance for quick and convenient file transfers of music, video, documents, and multimedia presentations.\n\nKey Highlights:\n- USB 3.2 Gen 1 High Speed Data Transfer\n- 128GB High Capacity Storage for everyday digital files\n- Practical protective cap with large colored loop easily attaches to key rings\n- Backward compatible with existing USB 2.0 ports and multi-platform OS support",
        "main_specs": {
            "Storage Capacity": "128 GB",
            "Interface": "USB 3.2 Gen 1 (Type-A)",
            "Form Factor": "Cap-Protected Flash Drive with Keyring Loop",
            "Data Transfer Rate": "Up to 5 Gbps Bus Speed",
            "Compatibility": "Windows 11/10, macOS, Linux, Chrome OS",
            "Color": "Black with White Ring Accents"
        },
        "tech_specs": {
            "general": {
                "Model / Series": "Kingston DataTraveler Exodia (DTX/128GB)",
                "Category Segment": "Flash Drives",
                "Condition": "Brand New"
            },
            "performance": {
                "Interface / Connection": "USB 3.2 Gen 1 Type-A (Backwards compatible with USB 2.0)",
                "Architecture": "High-Reliability 3D NAND Flash Memory",
                "Thermal Efficiency": "Operating Temperature: 0°C to 60°C",
                "Power Consumption": "Low-power USB bus powered (5V standard)"
            },
            "compatibility": {
                "Operating Systems": "Windows 11, Windows 10, macOS (v. 10.15.x +), Linux (v. 4.4.x +), Chrome OS",
                "Certification": "CE, FCC, RoHS, UKCA",
                "Dimensions & Weight": "67.3mm x 21.04mm x 10.14mm (Weight: ~11g)",
                "Included in Box": "1x Kingston DataTraveler Exodia 128GB USB Flash Drive"
            }
        }
    },
    # 2
    {
        "num": 2,
        "name": "KSTAR M700 / M700U 650VA Line Interactive Desktop UPS",
        "product_code": "KST-M700-650VA",
        "slug": "kstar-m700-650va-line-interactive-ups",
        "brand_slug": "kstar",
        "category_slug": "products",
        "sub_category_slug": "ups",
        "sub_category_name": "UPS",
        "warranty_duration": 1,
        "warranty_unit": "Years",
        "stock": 25,
        "description": "The KSTAR M700 / M700U 650VA Line Interactive UPS provides comprehensive power protection and battery backup for home computers, office workstations, Wi-Fi routers, and POS systems against blackouts, brownouts, and power surges.\n\nKey Highlights:\n- 650VA / 360W Surge & Battery Backup Capacity\n- Built-in Boost & Buck AVR (Automatic Voltage Regulation) stabilizes fluctuating mains voltage\n- Auto-restart function while AC recovery is restoring\n- Cold start function allows powering on devices without AC utility power\n- Fast intelligent battery charging with overcharge protection",
        "main_specs": {
            "Power Capacity": "650 VA / 360 W",
            "Nominal Input Voltage": "220 / 230 / 240 VAC (140 - 290 VAC Range)",
            "Output Voltage": "230 VAC ± 10% (Simulated Sine Wave)",
            "Battery Specification": "12V 7.0Ah / 7.2Ah Sealed Lead-Acid (SLA)",
            "Typical Backup Time": "10 - 20 Minutes (Standard PC Load)",
            "Output Sockets": "Universal Output Sockets with Surge Protection"
        },
        "tech_specs": {
            "general": {
                "Model / Series": "KSTAR MicroPower M700 / M700U 650VA",
                "Category Segment": "Uninterruptible Power Supply (UPS)",
                "Condition": "Brand New"
            },
            "performance": {
                "Interface / Connection": "Standard IEC / Universal Output Sockets + USB Monitoring Port (M700U)",
                "Architecture": "Line Interactive with Microprocessor Control and Fast AVR Transfer",
                "Thermal Efficiency": "Silent fanless natural convection cooling, operating temp 0-40°C",
                "Power Consumption": "Over 95% efficiency in normal line mode"
            },
            "compatibility": {
                "Operating Systems": "Compatible with all Desktop PCs, CCTV DVR/NVR, Monitors, and Wi-Fi Routers",
                "Certification": "CE, IEC/EN 62040-1, RoHS Compliant",
                "Dimensions & Weight": "279mm x 101mm x 142mm (Weight: ~4.2 kg)",
                "Included in Box": "1x KSTAR 650VA Line Interactive UPS, 1x Power Cord, User Manual"
            }
        }
    },
    # 3
    {
        "num": 3,
        "name": "Hiksemi Prime 128GB High Speed USB 3.0 Flash Drive",
        "product_code": "HIK-PRIME-128G",
        "slug": "hiksemi-prime-128gb-usb-30-flash-drive",
        "brand_slug": "hiksemi",
        "category_slug": "products",
        "sub_category_slug": "flash-drives",
        "sub_category_name": "Flash Drives",
        "warranty_duration": 3,
        "warranty_unit": "Years",
        "stock": 35,
        "description": "The Hiksemi Prime 128GB USB 3.0 Flash Drive offers sleek, durable metal casing paired with high-speed USB 3.0 transmission rates for quick and secure file backups.\n\nKey Highlights:\n- High-Speed USB 3.0 Read Speeds up to 130 MB/s\n- Ultra-compact, shock-resistant and heat-dissipating metallic alloy body\n- 128GB storage for 4K videos, heavy graphic projects, and software backups\n- Plug-and-play simplicity across Windows, Mac, smart TVs, and car multimedia systems",
        "main_specs": {
            "Storage Capacity": "128 GB",
            "Interface": "USB 3.0 Type-A (Compatible with USB 2.0)",
            "Max Read Speed": "Up to 130 MB/s",
            "Casing Material": "Zinc Alloy Metallic Finish",
            "Form Factor": "Capless Compact Unibody with Lanyard Hole",
            "Compatibility": "Windows, macOS, Linux, Android TV, In-Car Audio"
        },
        "tech_specs": {
            "general": {
                "Model / Series": "Hiksemi Prime Series USB 3.0 (HS-USB-M200P)",
                "Category Segment": "Flash Drives",
                "Condition": "Brand New"
            },
            "performance": {
                "Interface / Connection": "USB 3.0 SuperSpeed 5Gbps interface",
                "Architecture": "High-Density 3D TLC Flash NAND Controller",
                "Thermal Efficiency": "Metal unibody dissipates heat rapidly during heavy file transfers",
                "Power Consumption": "Bus powered low power consumption (<1.5W)"
            },
            "compatibility": {
                "Operating Systems": "Windows 11/10/8/7, macOS 10.x+, Linux Kernel 2.6+, ChromeOS",
                "Certification": "CE, FCC, RoHS Compliant",
                "Dimensions & Weight": "38mm x 12mm x 4.5mm (Weight: ~9g)",
                "Included in Box": "1x Hiksemi Prime 128GB USB 3.0 Flash Drive"
            }
        }
    },
    # 4
    {
        "num": 4,
        "name": "Hiksemi Prime 64GB High Speed USB 3.0 Flash Drive",
        "product_code": "HIK-PRIME-64G",
        "slug": "hiksemi-prime-64gb-usb-30-flash-drive",
        "brand_slug": "hiksemi",
        "category_slug": "products",
        "sub_category_slug": "flash-drives",
        "sub_category_name": "Flash Drives",
        "warranty_duration": 3,
        "warranty_unit": "Years",
        "stock": 40,
        "description": "The Hiksemi Prime 64GB USB 3.0 Flash Drive provides dependable, lightning-fast file storage in a sleek metallic alloy unibody design built for everyday pocket mobility.\n\nKey Highlights:\n- 64GB storage capacity for office docs, photos, and HD multimedia\n- USB 3.0 high-speed data transfer speeds up to 100 MB/s\n- Durable shockproof and dustproof metal unibody\n- Compact capless design with integrated keyring loop",
        "main_specs": {
            "Storage Capacity": "64 GB",
            "Interface": "USB 3.0 Type-A",
            "Max Read Speed": "Up to 100 MB/s",
            "Casing Material": "Zinc Alloy Metallic Finish",
            "Form Factor": "Capless Unibody",
            "Compatibility": "Universal PC, Mac, Laptop, and TV Support"
        },
        "tech_specs": {
            "general": {
                "Model / Series": "Hiksemi Prime Series USB 3.0 (HS-USB-M200P-64G)",
                "Category Segment": "Flash Drives",
                "Condition": "Brand New"
            },
            "performance": {
                "Interface / Connection": "USB 3.0 Type-A Connector",
                "Architecture": "3D TLC NAND Flash Memory",
                "Thermal Efficiency": "Operating Temperature: 0°C to 60°C",
                "Power Consumption": "Eco-efficient USB bus powered"
            },
            "compatibility": {
                "Operating Systems": "Windows 11/10/8/7, macOS, Linux, Chrome OS",
                "Certification": "CE, FCC, RoHS",
                "Dimensions & Weight": "38mm x 12mm x 4.5mm (Weight: ~9g)",
                "Included in Box": "1x Hiksemi Prime 64GB USB 3.0 Flash Drive"
            }
        }
    },
    # 5
    {
        "num": 5,
        "name": "Hiksemi Neo Lite 64GB Micro SDXC Memory Card (V30 / 4K UHD)",
        "product_code": "HIK-NEO-V30-64G",
        "slug": "hiksemi-neo-lite-64gb-micro-sd-card-v30",
        "brand_slug": "hiksemi",
        "category_slug": "products",
        "sub_category_slug": "memory-cards",
        "sub_category_name": "Memory Cards",
        "warranty_duration": 3,
        "warranty_unit": "Years",
        "stock": 50,
        "description": "The Hiksemi Neo Lite 64GB V30 Micro SD Card is engineered for high-performance 4K Ultra HD video recording, drones, action cameras, gaming consoles, and smart surveillance cameras.\n\nKey Highlights:\n- Video Speed Class V30 & UHS Speed Class 3 (U3) for smooth 4K UHD recording\n- Read speeds up to 95 MB/s and write speeds up to 45 MB/s\n- Class 10 & A1 Application Performance rated for faster smartphone app execution\n- Waterproof, shockproof, X-ray proof, and temperature-resistant durability",
        "main_specs": {
            "Storage Capacity": "64 GB",
            "Speed Class": "Class 10, UHS-I U3, V30, A1",
            "Max Read Speed": "Up to 95 MB/s",
            "Max Write Speed": "Up to 45 MB/s",
            "Video Recording Support": "Full HD & 4K UHD Continuous Recording",
            "Durability": "Waterproof, Magnet-proof, Temperature-proof, X-Ray Proof"
        },
        "tech_specs": {
            "general": {
                "Model / Series": "Hiksemi Neo Lite V30 Series (HS-TF-L2-64G)",
                "Category Segment": "Memory Cards",
                "Condition": "Brand New"
            },
            "performance": {
                "Interface / Connection": "UHS-I Interface (MicroSDXC)",
                "Architecture": "High-Endurance 3D NAND Flash Memory",
                "Thermal Efficiency": "Operational range: 0°C to 70°C; Storage range: -25°C to 85°C",
                "Power Consumption": "Ultra-low power drain for extended camera and mobile battery life"
            },
            "compatibility": {
                "Operating Systems": "Compatible with Android Smartphones, CCTV IP Cameras, Dashcams, Nintendo Switch",
                "Certification": "CE, FCC, RoHS, UHS-I Standard",
                "Dimensions & Weight": "15mm x 11mm x 1mm (Weight: ~0.5g)",
                "Included in Box": "1x Hiksemi Neo Lite 64GB V30 Micro SDXC Card"
            }
        }
    },
    # 6
    {
        "num": 6,
        "name": "Hiksemi Neo Lite 128GB Micro SDXC Memory Card (V30 / 4K UHD)",
        "product_code": "HIK-NEO-V30-128G",
        "slug": "hiksemi-neo-lite-128gb-micro-sd-card-v30",
        "brand_slug": "hiksemi",
        "category_slug": "products",
        "sub_category_slug": "memory-cards",
        "sub_category_name": "Memory Cards",
        "warranty_duration": 3,
        "warranty_unit": "Years",
        "stock": 50,
        "description": "The Hiksemi Neo Lite 128GB V30 Micro SDXC Memory Card is engineered for continuous 24/7 CCTV surveillance recording, 4K action cameras, drones, and heavy smartphone multimedia storage.\n\nKey Highlights:\n- 128GB Massive Capacity for hours of 4K video and thousands of high-res photos\n- Video Speed Class V30, U3, and Class 10 certified\n- High read speed up to 95 MB/s and write speed up to 55 MB/s\n- Application Performance Class A1 for ultra-responsive mobile gaming and app usage",
        "main_specs": {
            "Storage Capacity": "128 GB",
            "Speed Class": "Class 10, UHS-I U3, V30, A1",
            "Max Read Speed": "Up to 95 MB/s",
            "Max Write Speed": "Up to 55 MB/s",
            "Video Recording Support": "4K Ultra HD & 1080P Continuous Surveillance",
            "Durability": "Shockproof, Waterproof, Extreme Temperature Resistance"
        },
        "tech_specs": {
            "general": {
                "Model / Series": "Hiksemi Neo Lite V30 Series (HS-TF-L2-128G)",
                "Category Segment": "Memory Cards",
                "Condition": "Brand New"
            },
            "performance": {
                "Interface / Connection": "UHS-I Ultra High Speed MicroSDXC",
                "Architecture": "Optimized 3D TLC Endurance Flash NAND",
                "Thermal Efficiency": "Designed for heavy continuous surveillance writing up to 70°C",
                "Power Consumption": "Ultra-low power sleep and write modes"
            },
            "compatibility": {
                "Operating Systems": "Compatible with CCTV IP Cameras, Ezviz/Hikvision Wireless Cams, Mobile Phones, Tablets",
                "Certification": "CE, FCC, RoHS, SD Association Compliant",
                "Dimensions & Weight": "15mm x 11mm x 1mm (Weight: ~0.5g)",
                "Included in Box": "1x Hiksemi Neo Lite 128GB V30 Micro SDXC Memory Card"
            }
        }
    },
    # 7
    {
        "num": 7,
        "name": "Hiksemi Neo Lite 64GB Micro SDXC Class 10 Memory Card",
        "product_code": "HIK-NEO-64G",
        "slug": "hiksemi-neo-lite-64gb-micro-sd-card",
        "brand_slug": "hiksemi",
        "category_slug": "products",
        "sub_category_slug": "memory-cards",
        "sub_category_name": "Memory Cards",
        "warranty_duration": 3,
        "warranty_unit": "Years",
        "stock": 40,
        "description": "The Hiksemi Neo Lite 64GB Class 10 Micro SDXC Card provides reliable storage expansion for CCTV home security cameras, Android smartphones, dash cams, and MP3 audio players.\n\nKey Highlights:\n- 64GB Reliable Flash Storage\n- Class 10 & UHS-I U1 standard ensures steady Full HD 1080p recording\n- Read speeds up to 92 MB/s for fast file viewing and transfers\n- Built-in wear leveling algorithm ensures long service life",
        "main_specs": {
            "Storage Capacity": "64 GB",
            "Speed Class": "Class 10, UHS-I U1",
            "Max Read Speed": "Up to 92 MB/s",
            "Recording Quality": "Full HD 1080p Video Recording",
            "Form Factor": "MicroSDXC",
            "Compatibility": "Smartphones, Surveillance Cams, Tablets, Dashcams"
        },
        "tech_specs": {
            "general": {
                "Model / Series": "Hiksemi Neo Lite Standard Series (HS-TF-C1-64G)",
                "Category Segment": "Memory Cards",
                "Condition": "Brand New"
            },
            "performance": {
                "Interface / Connection": "MicroSDXC UHS-I standard pinout",
                "Architecture": "3D NAND Flash Memory",
                "Thermal Efficiency": "Operating temperature 0°C to 70°C",
                "Power Consumption": "Low power consumption"
            },
            "compatibility": {
                "Operating Systems": "Universal Android, Windows, Mac, Linux compatibility",
                "Certification": "CE, FCC, RoHS",
                "Dimensions & Weight": "15mm x 11mm x 1mm (Weight: ~0.5g)",
                "Included in Box": "1x Hiksemi Neo Lite 64GB Class 10 Micro SDXC Card"
            }
        }
    },
    # 8
    {
        "num": 8,
        "name": "Blackbox BB-24751 24\" Full HD 75Hz Office Desktop Monitor",
        "product_code": "BB-24751-24TN",
        "slug": "blackbox-bb-24751-24-tn-office-monitor",
        "brand_slug": "blackbox",
        "category_slug": "products",
        "sub_category_slug": "monitors",
        "sub_category_name": "Monitors",
        "warranty_duration": 3,
        "warranty_unit": "Years",
        "stock": 20,
        "description": "The Blackbox BB-24751 is a crisp 24-inch Full HD desktop monitor designed for daily office productivity, educational tasks, CCTV surveillance monitoring, and multimedia entertainment.\n\nKey Highlights:\n- 24-Inch Full HD (1920 x 1080) Widescreen Display\n- Smooth 75Hz Refresh Rate reduces screen flicker and eye strain\n- Ultra-slim bezel design creates modern multi-monitor desk setups\n- Dual Input: HDMI and VGA ports for wide PC and CCTV DVR compatibility\n- Low Blue Light and Anti-Glare technology protects your vision during long work hours",
        "main_specs": {
            "Screen Size": "24 Inch Diagonal",
            "Resolution": "1920 x 1080 (Full HD 1080p)",
            "Refresh Rate": "75 Hz",
            "Response Time": "5 ms (GtG)",
            "Video Inputs": "1x HDMI, 1x VGA",
            "Mounting Support": "VESA 75 x 75 mm Mount Compatible"
        },
        "tech_specs": {
            "general": {
                "Model / Series": "Blackbox BB-24751",
                "Category Segment": "Desktop Monitors",
                "Condition": "Brand New"
            },
            "performance": {
                "Interface / Connection": "1x HDMI 1.4, 1x VGA (D-Sub), 1x DC Power In",
                "Architecture": "Fast Response TN / LED Backlit Panel",
                "Thermal Efficiency": "Low-heat passive ventilation enclosure",
                "Power Consumption": "<24W Typical, <0.5W Standby Mode"
            },
            "compatibility": {
                "Operating Systems": "Plug and play with Windows 11/10/8, macOS, CCTV DVR / NVR Systems",
                "Certification": "CE, FCC, RoHS, Energy Star",
                "Dimensions & Weight": "540mm x 410mm x 180mm with stand (Weight: ~3.2 kg)",
                "Included in Box": "1x Blackbox BB-24751 24\" Monitor, 1x Power Adapter, 1x HDMI Cable, Desk Stand Base"
            }
        }
    },
    # 9
    {
        "num": 9,
        "name": "Hiksemi Neo Lite 128GB Micro SDXC Class 10 Memory Card",
        "product_code": "HIK-NEO-128G",
        "slug": "hiksemi-neo-lite-128gb-micro-sd-card",
        "brand_slug": "hiksemi",
        "category_slug": "products",
        "sub_category_slug": "memory-cards",
        "sub_category_name": "Memory Cards",
        "warranty_duration": 3,
        "warranty_unit": "Years",
        "stock": 40,
        "description": "The Hiksemi Neo Lite 128GB Class 10 Micro SDXC Memory Card provides high-capacity, dependable storage expansion for CCTV surveillance cameras, mobile devices, tablets, and media players.\n\nKey Highlights:\n- 128GB High Capacity storage holds over 10 days of continuous security footage\n- Class 10 & UHS-I high-speed standard\n- Up to 92 MB/s read transfer speed\n- Rigorously tested for water, heat, magnetic, and X-ray resistance",
        "main_specs": {
            "Storage Capacity": "128 GB",
            "Speed Class": "Class 10, UHS-I U1",
            "Max Read Speed": "Up to 92 MB/s",
            "Video Compatibility": "Full HD 1080p Continuous Recording",
            "Form Factor": "MicroSDXC",
            "Durability": "Waterproof, Shockproof, Temperature-resistant"
        },
        "tech_specs": {
            "general": {
                "Model / Series": "Hiksemi Neo Lite Standard (HS-TF-C1-128G)",
                "Category Segment": "Memory Cards",
                "Condition": "Brand New"
            },
            "performance": {
                "Interface / Connection": "MicroSDXC UHS-I Bus Interface",
                "Architecture": "Long-life 3D NAND Flash memory cells",
                "Thermal Efficiency": "Operating temperature 0°C to 70°C",
                "Power Consumption": "Ultra-low power draw"
            },
            "compatibility": {
                "Operating Systems": "Compatible with all MicroSDXC host devices",
                "Certification": "CE, FCC, RoHS",
                "Dimensions & Weight": "15mm x 11mm x 1mm (Weight: ~0.5g)",
                "Included in Box": "1x Hiksemi Neo Lite 128GB Class 10 Micro SDXC Card"
            }
        }
    },
    # 10
    {
        "num": 10,
        "name": "Hikvision WRE12C AC1200 Dual Band Wi-Fi Range Extender",
        "product_code": "HIK-WRE12C-EXT",
        "slug": "hikvision-wre12c-ac1200-dual-band-wifi-extender",
        "brand_slug": "hikvision",
        "category_slug": "products",
        "sub_category_slug": "range-extenders",
        "sub_category_name": "Range Extenders",
        "warranty_duration": 2,
        "warranty_unit": "Years",
        "stock": 25,
        "description": "The Hikvision WRE12C AC1200 Dual Band Wi-Fi Range Extender eliminates dead zones and boosts wireless signal coverage throughout your home, office, or retail space.\n\nKey Highlights:\n- Dual-Band AC1200 Speed: 867 Mbps on 5GHz + 300 Mbps on 2.4GHz\n- Dual External High-Gain Antennas ensure stable wall-penetrating signals\n- Smart Signal Indicator Light helps find the perfect location for optimal coverage\n- Quick One-Touch WPS Setup for instant connection to any existing Wi-Fi router\n- Fast Ethernet Port to connect wired smart TVs, desktop PCs, or game consoles",
        "main_specs": {
            "Wireless Speed": "AC1200 (867 Mbps on 5 GHz + 300 Mbps on 2.4 GHz)",
            "Frequency Bands": "Dual Band (2.4 GHz & 5 GHz Concurrent)",
            "Antenna Type": "2x External Omnidirectional High-Gain Antennas",
            "Ethernet Ports": "1x 10/100 Mbps RJ45 LAN Port",
            "Wireless Standards": "IEEE 802.11ac/n/a 5GHz, IEEE 802.11b/g/n 2.4GHz",
            "Security": "WPA-PSK / WPA2-PSK Wireless Encryption"
        },
        "tech_specs": {
            "general": {
                "Model / Series": "Hikvision WRE12C (DS-3WRE12C)",
                "Category Segment": "Wi-Fi Range Extenders",
                "Condition": "Brand New"
            },
            "performance": {
                "Interface / Connection": "Direct Wall Plug (UK 3-Pin / Standard) + 1x 10/100M RJ45 Port",
                "Architecture": "High-Efficiency Dual-Core Wi-Fi Signal Repeater Chipset",
                "Thermal Efficiency": "Ventilated wall-mount chassis with rapid heat dissipation",
                "Power Consumption": "<6.5W Max Operating Power"
            },
            "compatibility": {
                "Operating Systems": "Works with any standard 802.11a/b/g/n/ac Wi-Fi router or access point",
                "Certification": "CE, FCC, RoHS Compliant",
                "Dimensions & Weight": "112mm x 75mm x 66mm (Weight: ~150g)",
                "Included in Box": "1x Hikvision WRE12C AC1200 Wi-Fi Range Extender, Quick Setup Guide"
            }
        }
    }
]

print(f"Loaded {len(items_spec)} preliminary items. Continuing builder...")

