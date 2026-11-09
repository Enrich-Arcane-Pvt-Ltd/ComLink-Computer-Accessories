import json

with open('scratch/db_products.json', 'r', encoding='utf-8') as f:
    db = json.load(f)

existing_codes = set(p['code'].upper() for p in db)
print(f"Existing DB codes: {len(existing_codes)}")

proposed_codes = [
    'KNG-DTX-128G', 'KST-M700-650VA', 'HIK-PRIME-128G', 'HIK-PRIME-64G',
    'HIK-NEO-V30-64G', 'HIK-NEO-V30-128G', 'HIK-NEO-64G', 'BB-24751-24TN',
    'HIK-NEO-128G', 'HIK-WRE12C-EXT', 'VT-450W-ATX', 'CAN-LBP6030-PRN',
    'OTH-BT163-REC', 'HIK-SILK-128G-SATA', 'OSC-GOLD-256G-SATA', 'HIK-SILK-256G-SATA',
    'HIK-SILK-256G-NVME', 'VER-VI3000-512G', 'CAN-G2010-PRN', 'HIK-SILK-128G-NVME',
    'MG-R16-8G', 'MG-MSD-64G', 'SAM-27-TV-MON', 'MG-K009-64G',
    'UH-CR2032-5PK', 'UH-CR1220-5PK', 'PAN-P2500W-PRN', 'OKOP-KP-560',
    'BB-24120HD-IPS', 'BB-22751-22', 'MON-M2710-100HZ', 'BB-19750-19',
    'OTH-HDMI-EXT-60M', 'OTH-USB2-HUB-4P', 'HIK-DS-3WR4G3N-4G', 'HTP-SA22-12V',
    'KNG-CSP-128G-MSD', 'HP-BP-156-BLK', 'DEL-ES-BP15', 'LEN-TB-BP15',
    'GIG-H610M-S2-DDR4', 'GIG-H310M-S2-V2', 'GIG-H110M-DS2V-GLD', 'OTH-LAP-BAG-BLK',
    'GIG-H81M-DS2', 'GIG-H410M-S2-V3', 'SMV-19-LED-MON', 'SMV-22-LED-MON',
    'HP-15-FD0689TU', 'VTK-BAT-12V75AH', 'WRN-SSD-128G-NVME', 'WRN-SSD-256G-NVME',
    'WRN-SSD-512G-NVME', 'WRN-SSD-1TB-NVME', 'WRN-SSD-128G-SATA', 'WRN-SSD-256G-SATA',
    'WRN-SSD-512G-SATA', 'WRN-SSD-1TB-SATA'
]

conflicts = [c for c in proposed_codes if c.upper() in existing_codes]
print(f"Conflicts: {conflicts} (Total unique proposed: {len(set(proposed_codes))})")

