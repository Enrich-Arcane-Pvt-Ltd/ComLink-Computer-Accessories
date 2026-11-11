import os
import sys

# Ensure UTF-8 output
sys.stdout.reconfigure(encoding='utf-8')

base_dir = 'ProductData/HIKVISION'
items = sorted(os.listdir(base_dir))
print(f"Total entries in {base_dir}: {len(items)}")

for item in items:
    p = os.path.join(base_dir, item)
    if os.path.isdir(p):
        subitems = sorted(os.listdir(p))
        # check if subitems are directories or files
        is_sub_dir = any(os.path.isdir(os.path.join(p, s)) for s in subitems)
        if is_sub_dir:
            print(f"\n[CAT DIR] {item} ({len(subitems)} subfolders):")
            for sub in subitems:
                sub_p = os.path.join(p, sub)
                if os.path.isdir(sub_p):
                    files = [f for f in os.listdir(sub_p) if not f.endswith('.ini')]
                    print(f"    - [SUBDIR] '{sub}': {len(files)} files")
                else:
                    print(f"    - [FILE] '{sub}'")
        else:
            files = [f for f in subitems if not f.endswith('.ini')]
            print(f"\n[LEAF DIR] {item} ({len(files)} files)")
