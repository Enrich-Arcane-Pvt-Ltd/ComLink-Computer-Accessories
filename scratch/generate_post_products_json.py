import json
import os
import openpyxl

wb = openpyxl.load_workbook(r"c:\Users\Enrich PC\Downloads\smartpc_products_list.xlsx")
ws = wb['Products']
excel_rows = list(ws.iter_rows(values_only=True))[1:]

# Exact mapping of images from ProductData\Post product Images
# Checked and confirmed 100% existing in previous step:
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

# Verify image existence
img_dir = r"ProductData\Post product Images"
for k, fname in image_filenames.items():
    p = os.path.join(img_dir, fname)
    if not os.path.exists(p):
        raise FileNotFoundError(f"Missing image {fname} for row #{k}")

print("All 58 images verified on disk.")
