import json
import re

with open('scratch_all_meetion_products.json', encoding='utf-8') as f:
    products = json.load(f)

enriched = []

for p in products:
    name = p['name']
    model = p['model']
    subcat = p['subcat']
    cat = p['cat']
    folder = p['folder']
    cash = p['cash_price']
    credit = p['credit_price']
    
    # Calculate wholesale / cost price (approx 85% of cash price)
    cost = round(cash * 0.85, 2)
    # Retail price is Credit price, Sale offer is Cash price
    price = credit
    sale_price = cash

    # Warranty
    warranty_dur = 1
    warranty_unit = 'Years'
    if subcat in ['mouse-pad']:
        warranty_dur = 6
        warranty_unit = 'Months'

    # Build rich specifications based on category/subcategory and model keywords
    fl = folder.lower()
    
    if subcat == 'mouse':
        is_gaming = any(k in fl for k in ['gaming', 'gm', 'm371', 'm915', 'm930', 'm975'])
        is_wireless = any(k in fl for k in ['wireless', 'btm', 'r390', 'r545', 'r546', 'r547', 'r560', 'r571', 'r600', 'minigo', 'bluetooth'])
        is_silent = 'silent' in fl
        is_rechargeable = 'rechargable' in fl or 'rechargeable' in fl
        is_vertical = 'vertical' in fl or 'r390' in fl
        
        dpi = "1200 / 2400 / 3200 / 4800 / 7200 DPI" if is_gaming else ("800 / 1200 / 1600 DPI" if is_wireless else "1000 DPI Optical Engine")
        conn = "2.4G Wireless & Bluetooth Dual Mode" if 'dual mode' in fl else ("2.4GHz Wireless with USB Nano Receiver" if is_wireless else "Gold-Plated Wired USB (1.8m Braided Cable)" if is_gaming else "Wired USB (1.5m Cable)")
        lighting = "RGB Chroma Dynamic Flow Backlight (16.8M Colors)" if is_gaming else "Subtle Power & DPI Indicator LED"
        buttons = "7 Programmable Gaming Buttons + Scroll Wheel" if is_gaming else ("6 Buttons (Left, Right, DPI, Forward, Back, Wheel)" if is_vertical or 'r390' in fl else "4 Buttons (Left, Right, DPI, Scroll)")
        sensor = "High-Precision PixArt / Sunplus Optical Gaming Sensor" if is_gaming else "Energy-Efficient Optical Tracking Sensor"
        
        desc = (
            f"The {name} is engineered for peak precision and ergonomic comfort. "
            f"Featuring a {sensor.lower()} delivering smooth responsive tracking across all surfaces, "
            f"{'silent-click switches to eliminate distracting click noise, ' if is_silent else 'durable gaming-grade microswitches rated for up to 10 million clicks, '}"
            f"and {'an ergonomic 57-degree vertical natural hand-shake posture to relieve wrist strain.' if is_vertical else 'a contoured symmetric silhouette that reduces hand fatigue during long gaming and office sessions.'} "
            f"Supports instant plug-and-play setup with universal operating system compatibility."
        )
        
        main_specs = {
            'resolution_dpi': dpi,
            'connectivity': conn,
            'sensor_type': sensor,
            'number_of_buttons': buttons,
            'lighting_backlight': lighting,
            'switch_durability': "Up to 10 Million Clicks Micro-Switches" if is_gaming else "Up to 5 Million Silent Clicks",
        }
        
        tech_specs = {
            'model_series': f"MEETION {model} Precision Series",
            'category_segment': "Gaming Optical Mouse" if is_gaming else "Ergonomic Office Mouse",
            'condition': "Brand New (Original Factory Sealed)",
            'interface_connection': conn,
            'architecture': "Ergonomic Sculpted Grip with Anti-Slip Matte Coating",
            'thermal_efficiency': "Low Power Heat-Free Controller (Operating: 0°C to 45°C)",
            'power_consumption': "Rechargeable 500mAh Lithium Battery" if is_rechargeable else ("1x AA Alkaline Battery (Up to 12 Months)" if is_wireless else "Standard USB 5V / 100mA"),
            'operating_systems': "Windows 11 / 10 / 8 / 7, macOS, Linux, Chrome OS",
            'certification': "CE, FCC, RoHS Certified",
            'dimensions_weight': "128 × 68 × 40 mm; 95g" if is_gaming else ("110 × 72 × 75 mm; 110g" if is_vertical else "105 × 60 × 38 mm; 70g"),
            'included_in_box': f"1x {name}, USB Nano Receiver, User Manual, Warranty Guide",
        }

    elif subcat == 'keyboards':
        is_mech = any(k in fl for k in ['mechanical', 'mk0', 'mk1', 'mk5', 'mk6', 'hot-swap'])
        is_bt = any(k in fl for k in ['bluetooth', 'wireless', 'wk330', 'ikey', 'btk', 'dual-mode', 'triple-mode'])
        is_gaming = any(k in fl for k in ['gaming', 'backlit', 'k9', 'rgb', 'kb015']) or is_mech
        is_tkl = 'tkl' in fl or '60%' in fl or '65%' in fl or 'mini' in fl
        
        switches = "Outemu / Dustproof Blue Mechanical Switches" if 'blue switch' in fl else ("Hot-Swappable Custom Mechanical Switches" if 'hot-swap' in fl else ("High-Durability Mechanical Switches" if is_mech else "Quiet Chocolate Scissor-Key Switches" if 'chocolate' in fl or 'ultra thin' in fl else "Silent Membrane Dome Switches"))
        layout = "60% Ultra-Compact 61 Keys" if '60%' in fl else ("65% Compact 68 Keys" if '65%' in fl else ("87 Keys Tenkeyless (TKL)" if is_tkl else "Full Size 104 Keys Standard Layout with Number Pad"))
        lighting = "Full Spectrum RGB Backlighting with 14 Dynamic Lighting Modes" if 'rgb' in fl else ("Multi-Color Rainbow Backlit with Breathing Effects" if is_gaming else "Elegant White LED / Non-Backlit")
        conn = "Triple-Mode: 2.4G Wireless + Bluetooth 5.0 + Type-C Wired" if 'triple-mode' in fl else ("Bluetooth 5.0 & 2.4G Wireless Dual Mode" if is_bt else "High-Speed Detachable Type-C to USB Wired" if is_mech else "USB 2.0 Plug & Play (1.5m Cable)")
        
        desc = (
            f"The {name} combines robust construction with tactile responsiveness. "
            f"Equipped with {switches.lower()} providing crisp keystrokes and rapid actuation, "
            f"{'customizable per-key RGB lighting profiles, ' if is_gaming else 'whisper-quiet scissor architecture ideal for library or office environments, '}"
            f"{'a magnetic ergonomic wrist rest for long-term wrist comfort, ' if 'wrist' in fl else ''}"
            f"and durable UV-coated keycaps that resist fading and grease over years of intensive typing."
        )
        
        main_specs = {
            'keyboard_type': "Mechanical Keyboard" if is_mech else ("Chiclet / Scissor Keyboard" if 'chocolate' in fl or 'ultra thin' in fl else "Membrane Gaming Keyboard"),
            'switch_type': switches,
            'layout_keys': layout,
            'connectivity': conn,
            'backlight_rgb': lighting,
            'anti_ghosting': "100% Full N-Key Rollover Anti-Ghosting" if is_mech else "19-Key Anti-Ghosting Matrix",
        }
        
        tech_specs = {
            'model_series': f"MEETION {model} Series",
            'category_segment': "Mechanical Gaming Keyboard" if is_mech else "Ergonomic Office Keyboard",
            'condition': "Brand New (Original Factory Sealed)",
            'interface_connection': conn,
            'architecture': "Aircraft-Grade Aluminum Alloy Top Plate" if is_mech else "Spill-Resistant Reinforced ABS Housing",
            'thermal_efficiency': "Low Power High-Speed MCU (Operating: 0°C to 45°C)",
            'power_consumption': "Rechargeable 2,000mAh Battery" if is_bt else "Standard USB 5V / 250mA",
            'operating_systems': "Windows 11 / 10 / 8 / 7, macOS (Native Mac Keycap Legend on iKey), Linux, Android",
            'certification': "CE, FCC, RoHS Certified",
            'dimensions_weight': "440 × 135 × 35 mm; 850g" if not is_tkl else "355 × 125 × 35 mm; 650g",
            'included_in_box': f"1x {name}, Keycap Puller, Switch Puller, Type-C Cable, User Guide",
        }

    elif subcat == 'headsets':
        is_71 = '7.1' in fl
        is_wireless = 'bluetooth' in fl or 'wireless' in fl or 'bth' in fl
        is_telephony = 'telephony' in fl or 'hp002' in fl or 'hp003' in fl
        
        audio = "Virtual 7.1 Surround Sound 3D Audio" if is_71 else ("High-Definition Crystal Stereo Audio" if is_telephony else "Deep-Bass Stereo Gaming Audio")
        conn = "Bluetooth 5.0 Wireless + 3.5mm Aux" if is_wireless else ("USB Digital Audio Plug & Play with In-line Controls" if is_telephony or is_71 else "3.5mm Audio Jack + USB (for LED Power)")
        drivers = "30mm Neodymium Drivers" if is_telephony else "50mm High-Flux Neodymium Acoustic Drivers"
        mic = "Adjustable Noise-Cancelling Boom Microphone" if is_telephony else "Flexible Omnidirectional Noise-Reduction Mic"
        
        desc = (
            f"The {name} delivers rich immersive acoustics with exceptional spatial clarity. "
            f"Featuring tuned {drivers.lower()} that capture every subtle in-game footstep and crystal-clear voice frequency, "
            f"paired with an ultra-lightweight adjustable steel-reinforced headband and skin-friendly breathable memory foam ear cushions for fatigue-free all-day wear."
        )
        
        main_specs = {
            'audio_output': audio,
            'connectivity': conn,
            'microphone': mic,
            'driver_diameter': drivers,
            'lighting_backlight': "Circulating RGB Glow Logo" if not is_telephony else "In-Line Call / Mute Status LED",
            'frequency_response': "20Hz - 20,000Hz (32Ω Impedance)",
        }
        
        tech_specs = {
            'model_series': f"MEETION {model} Acoustic Series",
            'category_segment': "Telephony Call Center Headset" if is_telephony else "Gaming Over-Ear Headset",
            'condition': "Brand New (Original Factory Sealed)",
            'interface_connection': conn,
            'architecture': "Suspended Lightweight Steel Headband with Memory Foam Earcups",
            'thermal_efficiency': "Breathable Protein Leather Ear Cushions",
            'power_consumption': "USB 5V Powered / Rechargeable Battery (Up to 18 Hours)" if is_wireless else "Passive 3.5mm / USB Powered",
            'operating_systems': "Windows 11 / 10 / 8, macOS, PS5, PS4, Xbox Series X/S, Nintendo Switch, Mobile",
            'certification': "CE, FCC, RoHS Certified",
            'dimensions_weight': "200 × 180 × 90 mm; 280g" if not is_telephony else "160 × 140 × 50 mm; 135g",
            'included_in_box': f"1x {name}, 3.5mm Y-Splitter Adapter, User Manual, Warranty Card",
        }

    elif subcat == 'gaming-kits':
        desc = (
            f"The {name} is the complete all-in-one gaming battle-station kit. "
            f"Combines a tactile responsive gaming keyboard, precision optical gaming mouse, surround sound gaming headset with clear voice mic, and an anti-fray micro-textured gaming mouse pad. "
            f"Synchronized illumination and matching aesthetics make it the perfect gaming upgrade out of the box."
        )
        main_specs = {
            'kit_includes': "Keyboard, Mouse, Gaming Headset & Mouse Pad" if '4 in 1' in fl or '4-in-1' in fl or 'c505' in fl or 'c490' in fl else "Backlit Gaming Keyboard & High-Precision Gaming Mouse",
            'lighting_backlight': "Rainbow Multi-Zone Backlit Illumination",
            'mouse_dpi': "800 / 1600 / 2400 / 3200 DPI Adjustable Optical Mouse",
            'keyboard_keys': "104-Key Full Size Layout with Multimedia Shortcuts",
            'headset_audio': "40mm Stereo Drivers with Omnidirectional Mic" if '4 in 1' in fl or 'c505' in fl or 'c490' in fl else "N/A",
            'connectivity': "Wired USB Plug & Play (Braided Durable Cables)",
        }
        tech_specs = {
            'model_series': f"MEETION {model} Gaming Arsenal Kit",
            'category_segment': "All-in-One PC Gaming Battle Station Kit",
            'condition': "Brand New (Original Factory Sealed)",
            'interface_connection': "High-Speed USB 2.0 (Gold-Plated Connectors)",
            'architecture': "Reinforced ABS Gaming Hardware with Synchronized RGB Styling",
            'thermal_efficiency': "Operating Temperature: 0°C to 45°C",
            'power_consumption': "USB Bus Powered (5V DC)",
            'operating_systems': "Windows 11 / 10 / 8 / 7, Linux, macOS",
            'certification': "CE, FCC, RoHS Certified",
            'dimensions_weight': "Full Retail Kit Packaging (~1.4 kg)",
            'included_in_box': "1x Gaming Keyboard, 1x Optical Gaming Mouse, 1x Stereo Headset (if 4-in-1), 1x Mouse Pad, User Guide",
        }

    elif subcat == 'combo-packs':
        is_ergo = 'ergonomic' in fl or 'director' in fl or 'c4130' in fl
        is_wireless = 'wireless' in fl or 'cw' in fl or 'c4120' in fl or 'c4130' in fl or 'mini' in fl or 'btc' in fl or 'c2000' in fl or 'director' in fl
        is_silent = 'silent' in fl or 'cw295' in fl or 'c235' in fl
        
        desc = (
            f"The {name} is a perfectly matched keyboard and mouse set designed for seamless everyday productivity. "
            f"Features {'ergonomically curved split key architecture and contoured mouse to promote neutral posture, ' if is_ergo else 'whisper-quiet scissor / membrane keys with silent-click mouse switches, '}"
            f"{'2.4GHz wireless and Bluetooth dual-mode pairing, ' if 'dual' in fl or 'bluetooth' in fl else 'stable 2.4GHz lag-free wireless connection up to 10 meters, ' if is_wireless else 'reliable plug-and-play wired USB connectivity, '}"
            f"and intelligent power saving auto-sleep mode that extends battery life up to 12 months."
        )
        main_specs = {
            'package_includes': "Full Desktop Keyboard & Optical Mouse Set",
            'connectivity': "2.4G Wireless + Bluetooth 5.0 Dual Mode" if 'dual' in fl or 'bluetooth' in fl or 'c2000' in fl or 'director' in fl else ("2.4GHz Wireless with USB Nano Receiver" if is_wireless else "Wired USB Plug & Play"),
            'keyboard_type': "Ergonomic Curved Layout" if is_ergo else ("Ultra-Slim Scissor Keys" if 'mini' in fl or 'btc' in fl else "Full Size Silent Desktop Keyboard"),
            'mouse_dpi': "1000 / 1200 / 1600 DPI Adjustable Optical Tracking",
            'silent_clicking': "Yes, Silent Keystrokes & Quiet Mouse Buttons" if is_silent else "Low-Noise Office Grade Keystrokes",
            'wireless_range': "Up to 10 Meters (33ft) Reliable Range" if is_wireless else "1.5m Durable Cable",
        }
        tech_specs = {
            'model_series': f"MEETION {model} Office Combo Series",
            'category_segment': "Wireless / Wired Keyboard & Mouse Combo",
            'condition': "Brand New (Original Factory Sealed)",
            'interface_connection': "USB Nano Receiver / Bluetooth 5.0 / Wired USB",
            'architecture': "Spill-Resistant Keyboard Enclosure with Anti-Wear Keycap Printing",
            'thermal_efficiency': "Ultra-Low Power Microcontroller with Auto-Sleep",
            'power_consumption': "AAA/AA Batteries (Up to 12 Months Battery Life)" if is_wireless else "USB Bus Powered 5V",
            'operating_systems': "Windows 11 / 10 / 8 / 7, macOS, Linux, Chrome OS, Android",
            'certification': "CE, FCC, RoHS Certified",
            'dimensions_weight': "Keyboard: 435 × 125 × 25 mm; Mouse: 105 × 60 × 35 mm (~650g combined)",
            'included_in_box': "1x Desktop Keyboard, 1x Optical Mouse, USB Nano Receiver, AA/AAA Batteries, User Manual",
        }

    elif subcat == 'cooling-pads':
        fans = "5x High-Speed Cooling Fans (1x 140mm + 4x 70mm)" if 'cp5050' in fl else ("4x 120mm Silent LED Fans" if 'cp4040' in fl else ("2x 140mm Powerful Fans" if 'cp3030' in fl else "2x 120mm Silent Blue LED Fans"))
        desc = (
            f"The {name} protects your high-performance laptop from thermal throttling during intense gaming and heavy creative workloads. "
            f"Equipped with {fans.lower()} spinning up to 1400 RPM to force rapid airflow through a full metal mesh surface, "
            f"vibrant RGB edge backlighting with dynamic lighting modes, dual USB pass-through ports, and multi-level adjustable ergonomic elevation brackets."
        )
        main_specs = {
            'compatible_laptop_size': "Supports Laptops from 12\" up to 17.3\" Size",
            'fan_configuration': fans,
            'fan_speed_rpm': "1000 - 1400 ± 10% RPM (Adjustable Speed Roller)",
            'lighting_backlight': "Dynamic RGB Ambient Flow Surround Lighting",
            'usb_ports': "Dual USB 2.0 Pass-Through Ports",
            'elevation_angles': "Multi-Level Ergonomic Height Adjustment (5-7 Positions)",
        }
        tech_specs = {
            'model_series': f"MEETION {model} Frost Cooling Series",
            'category_segment': "Laptop RGB Gaming Cooling Pad",
            'condition': "Brand New (Original Factory Sealed)",
            'interface_connection': "Dual USB 2.0 Input/Output (Braided USB Power Cable)",
            'architecture': "Heavy-Duty Aerodynamic Metal Mesh with Non-Slip Baffle",
            'thermal_efficiency': "Lowers Laptop Internal CPU/GPU Temperatures by up to 15°C",
            'power_consumption': "DC 5V / 0.85A (USB Powered)",
            'operating_systems': "Universal Compatibility with all Laptops (ASUS, MSI, Acer, Dell, HP, Apple)",
            'certification': "CE, FCC, RoHS Certified",
            'dimensions_weight': "410 × 290 × 35 mm; 850g",
            'included_in_box': f"1x {name}, Braided USB-A to USB-A Power Cable, User Manual",
        }

    elif subcat == 'mouse-pad':
        is_rgb = 'rgb' in fl or 'p010' in fl
        is_large = 'large' in fl or 'p100' in fl or 'pd120' in fl
        size = "900 × 400 × 4.0 mm (Extended Desk Mat)" if 'pd120' in fl else ("800 × 300 × 3.0 mm (Large Gaming Surface)" if 'p100' in fl else ("360 × 260 × 4.0 mm" if 'p010' in fl else "250 × 210 × 2.0 mm (Standard Desk Size)"))
        
        desc = (
            f"The {name} provides an ultra-smooth gliding surface optimized for optical and laser gaming sensors. "
            f"Features high-density micro-woven fabric for pixel-precise mouse tracking, {'dynamic RGB perimeter fiber-optic edge lighting with one-touch mode switching, ' if is_rgb else 'precision stitched anti-fraying borders that prevent peeling, '}"
            f"and a heavy-grip non-slip natural rubber base that stays locked firmly on your desk."
        )
        main_specs = {
            'dimensions_size': size,
            'surface_material': "Micro-Textured High-Density Smooth Cloth Surface",
            'base_material': "Non-Slip Anti-Skid Textured Natural Rubber Base",
            'edge_treatment': "Fiber-Optic RGB Backlit Edges" if is_rgb else "Reinforced Stitched Anti-Fray Edges",
            'lighting': "14 Customizable RGB Lighting Modes (7 Static + 7 Dynamic)" if is_rgb else "Clean Minimalist Gaming Graphics",
            'compatibility': "Optimized for Optical & Laser Sensors (All DPI Levels)",
        }
        tech_specs = {
            'model_series': f"MEETION {model} Gaming Surface Series",
            'category_segment': "RGB Gaming Mouse Pad" if is_rgb else "Large Office / Gaming Mouse Mat",
            'condition': "Brand New (Original Factory Sealed)",
            'interface_connection': "Micro-USB / Type-C Braided Cable (1.8m)" if is_rgb else "Non-Powered Passive Mouse Pad",
            'architecture': "Multi-Layer Composite: Microfiber Cloth + Natural Rubber Cushion",
            'thermal_efficiency': "Spill-Resistant Water-Repellent Hydrophobic Coating",
            'power_consumption': "DC 5V / 100mA (for RGB light only)" if is_rgb else "0W (Passive)",
            'operating_systems': "Plug-and-play button for RGB mode switching, no driver required",
            'certification': "CE, FCC, RoHS Certified",
            'dimensions_weight': f"{size}; 350g",
            'included_in_box': f"1x {name}, Braided USB Cable (if RGB), Retail Gift Box",
        }

    elif subcat == 'speakers':
        is_subwoofer = '2.1' in fl or 'sp2110' in fl
        is_bt = 'bluetooth' in fl or 'sp2110' in fl
        power = "11W RMS (5W Subwoofer + 2x 3W Satellites)" if is_subwoofer else "6W RMS (2x 3W Stereo Drivers)"
        
        desc = (
            f"The {name} delivers punchy dynamic sound and crystal-clear dialogue for gaming, movies, and music. "
            f"Equipped with {power} dual acoustic drivers, {'a dedicated wooden wooden subwoofer cabinet for resonant bass, ' if is_subwoofer else 'dual full-range stereo sound chambers, '}"
            f"{'Bluetooth 5.0 wireless streaming and 3.5mm AUX connectivity, ' if is_bt else 'standard 3.5mm AUX headphone jack and USB power, '}"
            f"and circulating RGB ambient lighting effects."
        )
        main_specs = {
            'speaker_configuration': "2.1 Channel Stereo with Dedicated Subwoofer" if is_subwoofer else "2.0 Channel Stereo Multimedia Speakers",
            'total_output_power': power,
            'connectivity': "Bluetooth 5.0 Wireless + 3.5mm AUX Audio" if is_bt else "3.5mm AUX Audio Jack + USB Power",
            'lighting_backlight': "Dynamic RGB Ambient Breathing Glow",
            'frequency_response': "45Hz - 20,000Hz (Hi-Fi Acoustic Curve)",
            'volume_control': "Master Volume Knob with Front Power Switch",
        }
        tech_specs = {
            'model_series': f"MEETION {model} Sound Series",
            'category_segment': "Desktop Multimedia & Gaming Speakers",
            'condition': "Brand New (Original Factory Sealed)",
            'interface_connection': "3.5mm Audio In, USB 5V Power Port, Bluetooth 5.0",
            'architecture': "Acoustically Tuned Bass Reflex Enclosures",
            'thermal_efficiency': "High-Efficiency Class-D Digital Amplifier IC",
            'power_consumption': "USB 5V / 1A Powered",
            'operating_systems': "PC, Mac, Laptop, TV, Smartphone, Tablet, Gaming Consoles",
            'certification': "CE, FCC, RoHS Certified",
            'dimensions_weight': "Subwoofer: 180 × 160 × 120 mm, Satellites: 110 × 80 × 75 mm; 1.2 kg" if is_subwoofer else "140 × 85 × 75 mm (each); 650g",
            'included_in_box': f"1x {name} Set, Integrated 3.5mm + USB Cable, Quick Guide",
        }

    elif subcat == 'webcam':
        desc = (
            f"The {name} delivers crystal-clear 1080P Full HD video at smooth 30 frames per second. "
            f"Equipped with an advanced high-sensitivity CMOS sensor, automatic low-light correction, "
            f"built-in dual noise-cancelling omnidirectional microphones that pick up natural voice within 5 meters, "
            f"and a universal 360-degree rotating clamp that attaches securely to monitors, laptops, or tripods."
        )
        main_specs = {
            'video_resolution': "1080P Full HD (1920 × 1080 @ 30fps)",
            'field_of_view': "85° Wide-Angle Distortion-Free Lens",
            'microphone': "Built-in Dual Omnidirectional Noise-Cancelling Mic",
            'focus_type': "Automatic Light Balance & Precision Fixed/Auto Focus",
            'connectivity': "USB 2.0 High-Speed Plug & Play (Driver-Free)",
            'privacy_features': "Physical Lens Privacy Shutter & LED Activity Indicator",
        }
        tech_specs = {
            'model_series': "MEETION CAM601 Pro Series",
            'category_segment': "1080P Full HD Streaming & Video Conference Webcam",
            'condition': "Brand New (Original Factory Sealed)",
            'interface_connection': "USB 2.0 (1.5m Cable)",
            'architecture': "High-Definition Multi-Layer Coated Optical Glass Lens",
            'thermal_efficiency': "Low Power Heat-Dissipating Camera Module",
            'power_consumption': "USB 5V / 200mA",
            'operating_systems': "Windows 11 / 10 / 8 / 7, macOS, ChromeOS, Zoom, Teams, Skype, OBS",
            'certification': "CE, FCC, RoHS Certified",
            'dimensions_weight': "95 × 55 × 60 mm; 130g",
            'included_in_box': f"1x {name}, Integrated Privacy Cover, Universal Mount Clamp, User Manual",
        }

    elif subcat == 'chairs':
        is_massage = 'massage' in fl or 'chr25' in fl
        is_footrest = 'footrest' in fl or 'chr22' in fl or 'chr25' in fl or 'chr31' in fl or 'chr32' in fl
        is_mesh = 'mesh' in fl or 'chr05' in fl
        
        desc = (
            f"The {name} is built for gamers, streamers, and executives who demand supreme ergonomic posture support. "
            f"Featuring {'high-elasticity breathable Korean mesh for cool continuous airflow, ' if is_mesh else 'premium racing-grade high-density PU leather with internal high-resilience cold-cured foam, '}"
            f"a full 90° to 180° smooth reclining mechanism with locking tilt angles, "
            f"{'2D adjustable armrests, ' if '2d' in fl else 'ergonomically padded armrests, '}"
            f"{'a retractable cushioned footrest for instant relaxing and power naps, ' if is_footrest else ''}"
            f"{'built-in lumbar massage vibration motors to relieve lower-back tension, ' if is_massage else 'memory foam headrest pillow and lumbar cushion, '}"
            f"and an SGS Class-4 hydraulic gas cylinder mounted on a heavy-duty 5-star nylon/steel base supporting up to 150-180 kg."
        )
        main_specs = {
            'chair_type': "Professional Ergonomic Racing Gaming Chair" if not is_mesh else "Ergonomic High-Back Breathable Mesh Gaming Chair",
            'upholstery_material': "High-Tensile Breathable Mesh" if is_mesh else "Premium Breathable Racing PU Leather + Cold-Molded Foam",
            'reclining_mechanism': "90° - 180° Stepless Angle Reclining Function",
            'armrest_type': "2D Height & Angle Adjustable Armrests" if '2d' in fl else "Ergonomically Synchronized Padded Armrests",
            'footrest': "Scalable Retractable Padded Footrest" if is_footrest else "Standard Sleek Gaming Profile",
            'gas_lift_cylinder': "SGS Certified Class-4 Hydraulic Safety Piston",
        }
        tech_specs = {
            'model_series': f"MEETION {model} Throne Series",
            'category_segment': "Professional E-Sport & Executive Gaming Chair",
            'condition': "Brand New (Original Factory Sealed)",
            'interface_connection': "Non-Electric / USB 5V Cable for Massage Lumbar Cushion" if is_massage else "Mechanical Ergonomic Assembly",
            'architecture': "1.5mm Integrated Steel Frame + 19-Support Heavy-Duty Chassis",
            'thermal_efficiency': "Breathable Micro-Perforated Leatherette with Anti-Sweat Heat Dissipation",
            'power_consumption': "USB 5V for Lumbar Massage Motor" if is_massage else "0W (Mechanical)",
            'operating_systems': "Universal Ergonomic Seating for Desk Work, PC Gaming, Console",
            'certification': "SGS, BIFMA 5.1, CE, RoHS Safety Certified",
            'dimensions_weight': "Backrest: 82cm × 56cm; Seat: 54cm × 50cm; Height: 114-124cm; Net Weight: 19.5 kg",
            'included_in_box': f"1x {name} Components, Assembly Screws & Wrench Tool, Headrest Pillow, Lumbar Cushion, Illustrated Assembly Guide",
        }

    # Ensure unique SKU model
    sku = model
    if 'black+orange' in fl:
        sku = f"{model}-BO"
    elif 'black+red' in fl:
        sku = f"{model}-BR"
    elif 'sinhala' in fl:
        sku = f"{model}-ST"
    elif 'mk009 pro' in fl:
        sku = "MK009-PRO"

    enriched.append({
        'folder': folder,
        'model': sku,
        'product_code': p['product_code'],
        'name': name,
        'cat': cat,
        'subcat': subcat,
        'price': price,
        'sale_price': sale_price,
        'cost_price': cost,
        'warranty_duration': warranty_dur,
        'warranty_unit': warranty_unit,
        'description': desc,
        'main_specs': main_specs,
        'tech_specs': tech_specs,
    })

print(f"Enriched {len(enriched)} products successfully.")
with open('scratch_enriched_meetion.json', 'w', encoding='utf-8') as f:
    json.dump(enriched, f, indent=2)
print("Saved to scratch_enriched_meetion.json")
