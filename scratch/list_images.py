import os

img_dir = r"ProductData\Post product Images"
files = os.listdir(img_dir)
print(f"Total files in '{img_dir}': {len(files)}")
for f in sorted(files):
    print(f)

