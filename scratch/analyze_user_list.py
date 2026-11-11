import json

# List from user prompt
user_list = {
    "Value Series TVI CAMERA": [
        "DS-2CE16DOT-EXLPF 2.8MM",
        "DS-2CE16DOT-EXLPF 3.6MM",
        "DS-2CE76D0T-EXLPF 2.8MM",
        "DS-2CE76D0T-EXLPF 3.6MM",
        "DS-2CE16DOT-LPFS 2.8MM",
        "DS-2CE16DOT-LPFS 3.6MM",
        "DS-2CE76D0T-LPFS 2.8MM",
        "DS-2CE76D0T-LPFS 3.6MM",
        "DS-2CE17D0T-EXLF 2.8MM",
        "DS-2CE17D0T-EXLF 3.6MM",
        "DS-2CE17D0T-LFS 3.6MM",
        "DS-2CE17D0T-LFS 6MM",
        "DS-2CE78D0T-LFS 2.8MM",
        "DS-2CE78D0T-LFS 3.6MM"
    ],
    "DUAL LIGHT LIVE GUARD CAMERA": [
        "DS-2CE16D0T-LPTS 2.8MM",
        "DS-2CE16D0T-LPTS 3.6MM",
        "DS-2CE78D0T-LPTS 2.8MM",
        "DS-2CE78D0T-LPTS 3.6MM",
        "DS-2CE17D0T-LTS 2.8MM",
        "DS-2CE17D0T-LTS 3.6MM"
    ],
    "SILVER COLOR VIEW CAMERA": [
        "DS-2CE10DF0T-PFS 2.8MM",
        "DS-2CE10DF0T-PFS 3.6MM",
        "DS-2CE70DF0T-PFS 2.8MM",
        "DS-2CE70DF0T-PFS 3.6MM",
        "DS-2CE70DF0T-LPFS 2.8MM",
        "DS-2CE70DF0T-LPFS 3.6MM",
        "DS-2CE12DF0T-FS 3.6MM",
        "DS-2CE12DF0T-FS 6MM",
        "DS-2CE12DF0T-LFS 3.6MM",
        "DS-2CE12DF0T-LFS 6MM",
        "DS-2CE10DF0T-LPTS 2.8MM",
        "DS-2CE10DF0T-LPTS 3.6MM",
        "DS-2CE72DF0T-LPTS 2.8MM",
        "DS-2CE72DF0T-LPTS 3.6MM",
        "DS-2CE12DF0T-LTS 3.6MM",
        "DS-2CE12DF0T-LTS 6MM"
    ],
    "GOLD COLOR VIEW CAMERA": [
        "DS-2CE10DF3T-PF 2.8MM",
        "DS-2CE10DF3T-PF 3.6MM",
        "DS-2CE70DF3T-PF 2.8MM",
        "DS-2CE70DF3T-PF 3.6MM",
        "DS-2CE10DF3T-LPFS 2.8MM",
        "DS-2CE10DF3T-LPFS 3.6MM",
        "DS-2CE70DF3T-LPFS 2.8MM",
        "DS-2CE70DF3T-LPFS 3.6MM",
        "DS-2CE12DF3T-F 3.6MM",
        "DS-2CE12DF3T-F 6MM",
        "DS-2CE12DF3T-LFS 3.6MM",
        "DS-2CE12DF3T-LFS 6MM",
        "DS-2CE72DF3T-LFS 2.8MM",
        "DS-2CE72DF3T-LFS 3.6MM",
        "DS-2CE50DF3T-VPLFS 3.6MM"
    ],
    "MULTY COLOR CAMERA": [
        "DS-2CE72DF8T-F 2.8MM",
        "DS-2CE72DF8T-F 3.6MM",
        "DS-2CE19DF8T-AZE 2.8-12MM",
        "DS-2CE50DF3T-VPLSZE 2.8-12MM",
        "DS-2CE19DF3T-LSZE  2.8-12MM"
    ],
    "HD CAMERA VALUE SERIES 3K": [
        "DS-2CE16K0T-EXLPF 2.8MM",
        "DS-2CE16K0T-EXLPF 3.6MM",
        "DS-2CE18K0T-LFS 3.6MM",
        "DS-2CE18K0T-LFS 6MM"
    ],
    "VALUE SERIES 3K COLORVU": [
        "DS-2CE10KF0T-LPFS 2.8MM",
        "DS-2CE10KF0T-LPFS 3.6MM",
        "DS-2CE70KF0T-LPFS 2.8MM",
        "DS-2CE70KF0T-LPFS 3.6MM",
        "DS-2CE12KF0T-LFS 3.6MM",
        "DS-2CE12KF0T-LFS 6MM",
        "DS-2CE10KF3T-L 3.6MM",
        "DS-2CE10KF3T-L 6MM",
        "DS-2CE12KF3T-LS 3.6MM",
        "DS-2CE12KF3T-LS 6MM",
        "DS-2CE72KF3T-L 2.8MM",
        "DS-2CE72KF3T-L 3.6MM",
        "DS-2CE72KF3T-PIRXO 2.8MM",
        "DS-2CE12KF3T-PIRXO 3.6MM"
    ],
    "3K STITCHING CAMERA": [
        "DS-2CE72KF3TP-DLS 180°",
        "DS-2CE12KF3TP-DLS 180°"
    ],
    "DVRs": [
        "DS-7104HGHI-M1/T",
        "DS-7108HGHI-M1/T",
        "DS-7116HGHI-M1/T",
        "DS-7232HGHI-M2",
        "DS-7104HQHI-K1",
        "IDS-7104HQHI-M1/T",
        "IDS-7108HQHI-M1/T",
        "IDS-7116HQHI-M1/T",
        "iDS-7204HQHI-M1/XT",
        "iDS-7208HQHI-M1/T",
        "iDS-7216HQHI-M1/T",
        "IDS-7208HQHI-M2/XT",
        "iDS-7216HQHI-M2/XT",
        "IDS-7232HQHI-M2/XT",
        "iDS-7204HUHI-M1/T",
        "iDS-7208HUHI-M1/T",
        "iDS-7216HUHI-M2/XT",
        "IDS-7332HQHI-M4/S",
        "IDS-7332HUHI-M4/S"
    ]
}

# Count total requested
total = sum(len(v) for v in user_list.values())
print(f"Total items in user list: {total}")
for cat, items in user_list.items():
    print(f"  - {cat}: {len(items)} items")

