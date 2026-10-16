/**
 * Utility for resolving category-specific Main Specifications
 * according to ComLink hardware categorization rules.
 */

export interface MainSpecItem {
    label: string;
    value: string;
}

export interface MainSpecsResult {
    categoryTitle: string;
    items: MainSpecItem[];
}

interface ProductLike {
    name: string;
    brand?: string;
    category?: string;
    categorySlug?: string;
    subCategory?: string;
    subCategorySlug?: string;
    condition?: string;
    type?: string;
    warranty?: { duration?: string; type?: string } | any;
    specifications?: Array<{
        group: string;
        items: Array<{ label: string; value: string }>;
    }>;
    main_specifications?: Record<string, any>;
    technical_specifications?: Record<string, any>;
}

/**
 * Normalizes text to extract hardware metrics safely
 */
function matchRegex(text: string, regex: RegExp, fallback: string): string {
    const match = text.match(regex);
    return match ? match[1] || match[0] : fallback;
}

/**
 * Returns the Main Specifications for a product based on its category/subcategory.
 */
export function getMainSpecifications(product: ProductLike): MainSpecsResult {
    const name = product.name || "";
    const brand = product.brand || "ComLink";
    const condition = product.condition || "Brand New (Original Factory Sealed)";

    // Identify target slug
    const subSlug = (product.subCategorySlug || "").toLowerCase();
    const catSlug = (product.categorySlug || "").toLowerCase();
    const subName = (product.subCategory || "").toLowerCase();
    const catName = (product.category || "").toLowerCase();

    // Helper to check existing specifications table if already provided by backend/admin
    const findExisting = (labelName: string): string | null => {
        // 1. Check direct main_specifications object from admin
        if (product.main_specifications && typeof product.main_specifications === "object") {
            const normalized = labelName.toLowerCase().replace(/[\s\(\)\/\-_]+/g, "");
            for (const [k, v] of Object.entries(product.main_specifications)) {
                const normK = k.toLowerCase().replace(/[\s\(\)\/\-_]+/g, "");
                if ((normK === normalized || normK.includes(normalized) || normalized.includes(normK)) && v) {
                    return String(v);
                }
            }
        }
        // 2. Check technical_specifications
        if (product.technical_specifications && typeof product.technical_specifications === "object") {
            const normalized = labelName.toLowerCase().replace(/[\s\(\)\/\-_]+/g, "");
            for (const [k, v] of Object.entries(product.technical_specifications)) {
                const normK = k.toLowerCase().replace(/[\s\(\)\/\-_]+/g, "");
                if ((normK === normalized || normK.includes(normalized) || normalized.includes(normK)) && v) {
                    return String(v);
                }
            }
        }
        // 3. Check existing specifications table
        if (product.specifications) {
            for (const grp of product.specifications) {
                for (const itm of grp.items) {
                    if (itm.label.toLowerCase() === labelName.toLowerCase() && itm.value) {
                        return itm.value;
                    }
                }
            }
        }
        return null;
    };

    // 1. LAPTOPS & GAMING LAPTOPS
    if (
        subSlug === "gaming-laptops" ||
        catSlug === "laptops" ||
        subName.includes("laptop") ||
        catName.includes("laptop")
    ) {
        // Display size
        const displayMatch = matchRegex(
            name,
            /(\d{2}(?:\.\d)?(?:\s?["”]|[\s-]inch))/i,
            '16.0" QHD+ (2560x1600) 240Hz OLED / IPS',
        );

        // Graphic cards
        let graphics = "NVIDIA GeForce RTX 4070 8GB GDDR6";
        if (/RTX\s?4090/i.test(name)) graphics = "NVIDIA GeForce RTX 4090 16GB GDDR6";
        else if (/RTX\s?4080/i.test(name)) graphics = "NVIDIA GeForce RTX 4080 12GB GDDR6";
        else if (/RTX\s?4070/i.test(name)) graphics = "NVIDIA GeForce RTX 4070 8GB GDDR6";
        else if (/RTX\s?4060/i.test(name)) graphics = "NVIDIA GeForce RTX 4060 8GB GDDR6";
        else if (/RTX\s?4050/i.test(name)) graphics = "NVIDIA GeForce RTX 4050 6GB GDDR6";
        else if (/RTX\s?3050/i.test(name)) graphics = "NVIDIA GeForce RTX 3050 4GB GDDR6";
        else if (/Iris\s?Xe/i.test(name)) graphics = "Intel Iris Xe Integrated Graphics";
        else if (/Radeon/i.test(name)) graphics = "AMD Radeon 780M High-Efficiency Graphics";

        // Memory
        let mem = "32GB DDR5 5600MHz High-Speed";
        if (/64GB/i.test(name)) mem = "64GB DDR5 Dual-Channel (2x32GB)";
        else if (/32GB/i.test(name)) mem = "32GB DDR5 5600MHz (2x16GB)";
        else if (/16GB/i.test(name)) mem = "16GB DDR5 5200MHz";
        else if (/8GB/i.test(name)) mem = "8GB DDR5 4800MHz";

        // Processor
        let proc = "Intel Core Ultra 9 185H / AMD Ryzen 9 AI Processor";
        if (/Ultra\s?9/i.test(name)) proc = "Intel Core Ultra 9 185H (16 Cores, up to 5.1GHz)";
        else if (/Ultra\s?7/i.test(name)) proc = "Intel Core Ultra 7 155H (16 Cores, up to 4.8GHz)";
        else if (/i9[\s-]14/i.test(name) || /i9/i.test(name)) proc = "Intel Core i9 14th Gen HX Processor";
        else if (/i7[\s-]14/i.test(name) || /i7/i.test(name)) proc = "Intel Core i7 14th Gen HX Processor";
        else if (/i5/i.test(name)) proc = "Intel Core i5 13th/14th Gen Processor";
        else if (/Ryzen\s?9/i.test(name)) proc = "AMD Ryzen 9 8945HS / 7945HX Extreme";
        else if (/Ryzen\s?7/i.test(name)) proc = "AMD Ryzen 7 8845HS / 7745HX Octa-Core";

        // Storage
        let storage = "1TB PCIe Gen 4.0 NVMe M.2 SSD";
        if (/2TB/i.test(name)) storage = "2TB High-Speed NVMe PCIe 4.0 SSD";
        else if (/512GB/i.test(name)) storage = "512GB NVMe M.2 PCIe 4.0 SSD";

        // Type
        const type = /slim|ultra/i.test(name)
            ? "Thin & Light Performance Laptop"
            : "High-Performance Gaming Laptop";

        return {
            categoryTitle: "Laptop",
            items: [
                { label: "Condition", value: condition },
                { label: "Brand", value: brand },
                { label: "Display size", value: findExisting("Display size") || displayMatch },
                { label: "Graphic cards", value: findExisting("Graphic cards") || graphics },
                { label: "Memory", value: findExisting("Memory") || mem },
                { label: "Processor", value: findExisting("Processor") || proc },
                { label: "Storage", value: findExisting("Storage") || storage },
                { label: "Type", value: findExisting("Type") || type },
            ],
        };
    }

    // 2. PC COMPONENTS - PROCESSORS
    if (subSlug === "processors" || subName.includes("processor")) {
        let generation = "AMD Ryzen 7000 Series (Zen 4 Architecture)";
        if (/9950|9900|9700|9600|9000/i.test(name)) generation = "AMD Ryzen 9000 Series (Zen 5 Architecture)";
        else if (/7950|7900|7800|7700|7600|7000/i.test(name)) generation = "AMD Ryzen 7000 Series (Zen 4 Architecture)";
        else if (/5950|5900|5800|5700|5600|5500|5000/i.test(name)) generation = "AMD Ryzen 5000 Series (Zen 3 Architecture)";
        else if (/14900|14700|14600|14400|14th/i.test(name)) generation = "Intel 14th Gen Raptor Lake Refresh";
        else if (/13900|13700|13600|13th/i.test(name)) generation = "Intel 13th Gen Raptor Lake Architecture";
        else if (/12900|12700|12600|12th/i.test(name)) generation = "Intel 12th Gen Alder Lake Architecture";

        let cores = "8 Cores";
        const coreMatch = name.match(/(\d+)[\s-]Core/i);
        if (coreMatch) {
            cores = `${coreMatch[1]} Cores`;
        } else if (/14900|13900/i.test(name)) cores = "24 Cores (8P + 16E)";
        else if (/14700/i.test(name)) cores = "20 Cores (8P + 12E)";
        else if (/14600|13600/i.test(name)) cores = "14 Cores (6P + 8E)";
        else if (/7950|5950/i.test(name)) cores = "16 Cores";
        else if (/7900|5900/i.test(name)) cores = "12 Cores";
        else if (/7800|7700|5800|5700/i.test(name)) cores = "8 Cores";
        else if (/7600|5600/i.test(name)) cores = "6 Cores";

        let threads = "16 Threads";
        const threadMatch = name.match(/(\d+)[\s-]Thread/i);
        if (threadMatch) {
            threads = `${threadMatch[1]} Threads`;
        } else if (/14900|13900/i.test(name)) threads = "32 Threads";
        else if (/14700/i.test(name)) threads = "28 Threads";
        else if (/14600|13600/i.test(name)) threads = "20 Threads";
        else if (/7950|5950/i.test(name)) threads = "32 Threads";
        else if (/7900|5900/i.test(name)) threads = "24 Threads";
        else if (/7800|7700|5800|5700/i.test(name)) threads = "16 Threads";
        else if (/7600|5600/i.test(name)) threads = "12 Threads";

        return {
            categoryTitle: "Processor",
            items: [
                { label: "Condition", value: condition },
                { label: "Brand", value: brand },
                { label: "Generation", value: findExisting("Generation") || generation },
                { label: "CORES", value: findExisting("CORES") || cores },
                { label: "THREADS", value: findExisting("THREADS") || threads },
            ],
        };
    }

    // 3. PC COMPONENTS - THERMAL PASTE
    if (subSlug === "thermal-paste" || subName.includes("thermal paste")) {
        return {
            categoryTitle: "Thermal Paste",
            items: [{ label: "Brand", value: brand }],
        };
    }

    // 4. PC COMPONENTS - MOTHERBOARDS
    if (subSlug === "motherboards" || subName.includes("motherboard")) {
        let cpuSocket = "AMD Socket AM5";
        let chipset = "AMD B650";
        let platform = "AMD Platform";

        if (/AM5/i.test(name) || /B650|X670|X870|A620/i.test(name)) {
            cpuSocket = "AMD Socket AM5 (PCIe 5.0 / DDR5 Ready)";
            platform = "AMD Platform";
            if (/X870/i.test(name)) chipset = "AMD X870";
            else if (/X670E|X670/i.test(name)) chipset = "AMD X670E";
            else if (/A620/i.test(name)) chipset = "AMD A620";
            else chipset = "AMD B650";
        } else if (/AM4/i.test(name) || /B550|X570/i.test(name)) {
            cpuSocket = "AMD Socket AM4";
            platform = "AMD Platform";
            chipset = /X570/i.test(name) ? "AMD X570" : "AMD B550";
        } else if (/Z890|1851/i.test(name)) {
            cpuSocket = "Intel LGA 1851";
            platform = "Intel Platform";
            chipset = "Intel Z890";
        } else {
            cpuSocket = "Intel LGA 1700 (12th, 13th & 14th Gen)";
            platform = "Intel Platform";
            if (/Z790/i.test(name)) chipset = "Intel Z790";
            else if (/B760/i.test(name)) chipset = "Intel B760";
            else chipset = "Intel B760 / Z790";
        }

        const perfLevel = /ROG|STRIX|GODLIKE|TAICHI|MAXIMUS|CARBON/i.test(name)
            ? "High-End Enthusiast"
            : /TUF|TOMAHAWK|AORUS|STEEL LEGEND/i.test(name)
              ? "Mainstream Gaming"
              : "Mainstream Performance";

        return {
            categoryTitle: "Motherboard",
            items: [
                { label: "Condition", value: condition },
                { label: "Brand", value: brand },
                { label: "Performance Level", value: findExisting("Performance Level") || perfLevel },
                { label: "CPU Soket", value: findExisting("CPU Soket") || findExisting("CPU Socket") || cpuSocket },
                { label: "Chipset Model", value: findExisting("Chipset Model") || chipset },
                { label: "MB Platform", value: findExisting("MB Platform") || platform },
            ],
        };
    }

    // 5. PC COMPONENTS - COOLERS
    if (subSlug === "coolers" || subName.includes("cooler")) {
        const isAio = /AIO|Liquid|Water|360|240|280/i.test(name);
        const radMatch = matchRegex(
            name,
            /(120|240|280|360|420)\s?mm/i,
            isAio ? "360mm High-Density Radiator" : "Dual Tower Heatsink",
        );
        const fanSize = /140/i.test(name) ? "140mm High-Airflow PWM" : "120mm High-Static Pressure PWM";
        const coolingType = isAio ? "AIO Liquid Cooler" : "High-Performance Air Cooler";

        return {
            categoryTitle: "Cooler",
            items: [
                { label: "Condition", value: condition },
                { label: "Fans Size", value: findExisting("Fans Size") || fanSize },
                { label: "Radiator Size", value: findExisting("Radiator Size") || radMatch },
                { label: "Cooling Type", value: findExisting("Cooling Type") || coolingType },
                { label: "Brand", value: brand },
            ],
        };
    }

    // 6. PC COMPONENTS - MEMORY CARD (RAM)
    if (subSlug === "memory" || subName.includes("memory") || subName.includes("ram")) {
        const capMatch = matchRegex(
            name,
            /(\d+GB(?:\s?\(\d+x\d+GB\))?)/i,
            "32GB (2x16GB) Dual Channel Kit",
        );
        const speedMatch = matchRegex(
            name,
            /(\d{4}\s?MHz)/i,
            /DDR4/i.test(name) ? "3600MHz" : "6000MHz",
        );
        const isSodimm = /SODIMM|Laptop/i.test(name);
        const formFactor = isSodimm ? "260-Pin Laptop SO-DIMM" : "288-Pin Desktop UDIMM";
        const memGen = /DDR4/i.test(name) ? "DDR4" : "DDR5";

        return {
            categoryTitle: "Memory (RAM)",
            items: [
                { label: "Condition", value: condition },
                { label: "Brand", value: brand },
                { label: "RAM Capacity", value: findExisting("RAM Capacity") || capMatch },
                { label: "Memory Speed", value: findExisting("Memory Speed") || speedMatch },
                { label: "Form Factor", value: findExisting("Form Factor") || formFactor },
                { label: "Memory Gen", value: findExisting("Memory Gen") || memGen },
            ],
        };
    }

    // 7. PC COMPONENTS - SSD
    if (subSlug === "ssd" || subName.includes("ssd")) {
        let generation = "PCIe Gen 4.0 x4 (NVMe 2.0)";
        if (/Gen\s?5/i.test(name)) generation = "PCIe Gen 5.0 x4 (Ultra-High Speed)";
        else if (/Gen\s?3/i.test(name)) generation = "PCIe Gen 3.0 x4";

        const capMatch = matchRegex(
            name,
            /(\d+(?:GB|TB))/i,
            "1TB High-Performance NVMe",
        );
        const isSata = /2\.5|SATA/i.test(name);
        const ssdType = isSata ? "2.5\" SATA III 6Gb/s" : "M.2 2280 NVMe SSD";

        return {
            categoryTitle: "Solid State Drive (SSD)",
            items: [
                { label: "Condition", value: condition },
                { label: "Generation", value: findExisting("Generation") || generation },
                { label: "Storage Capacity", value: findExisting("Storage Capacity") || capMatch },
                { label: "Brand", value: brand },
                { label: "SSD Type", value: findExisting("SSD Type") || ssdType },
            ],
        };
    }

    // 8. PC COMPONENTS - HARD DISK
    if (subSlug === "hard-disk" || subName.includes("hard disk") || subName.includes("hdd")) {
        let colorType = "WD Blue / Seagate Desktop";
        if (/Black/i.test(name)) colorType = "WD Black (Gaming & Performance)";
        else if (/Red/i.test(name)) colorType = "WD Red Plus (NAS Reliable)";
        else if (/Purple/i.test(name)) colorType = "WD Purple (Surveillance)";
        else if (/IronWolf/i.test(name)) colorType = "Seagate IronWolf (NAS)";
        else if (/Barracuda/i.test(name)) colorType = "Seagate Barracuda Multi-Tier";

        const capMatch = matchRegex(
            name,
            /(\d+(?:TB|GB))/i,
            "2TB High-Capacity",
        );
        const isPortable = /External|Portable|2\.5/i.test(name);
        const storageType = isPortable
            ? "External Portable 2.5\" USB 3.2"
            : "Internal 3.5\" SATA 6Gb/s (7200 RPM)";

        return {
            categoryTitle: "Hard Disk Drive (HDD)",
            items: [
                { label: "Condition", value: condition },
                { label: "Brand", value: brand },
                { label: "HDD Color Type", value: findExisting("HDD Color Type") || colorType },
                { label: "Storage Capacity", value: findExisting("Storage Capacity") || capMatch },
                { label: "Storage Type", value: findExisting("Storage Type") || storageType },
            ],
        };
    }

    // 9. PC COMPONENTS - GRAPHIC CARDS
    if (subSlug === "graphic-cards" || subName.includes("graphic card") || subName.includes("gpu")) {
        let gpuSeries = "NVIDIA GeForce RTX 40 Series";
        let vram = "16GB GDDR6X 256-bit";
        let psuReq = "750W or higher";

        if (/4090/i.test(name)) {
            gpuSeries = "NVIDIA GeForce RTX 40 Series (Ada Lovelace)";
            vram = "24GB GDDR6X 384-bit";
            psuReq = "850W - 1000W Required";
        } else if (/4080/i.test(name)) {
            gpuSeries = "NVIDIA GeForce RTX 40 Series (Ada Lovelace)";
            vram = "16GB GDDR6X 256-bit";
            psuReq = "750W - 850W Required";
        } else if (/4070/i.test(name)) {
            gpuSeries = "NVIDIA GeForce RTX 40 Series (Ada Lovelace)";
            vram = /Ti/i.test(name) ? "16GB GDDR6X" : "12GB GDDR6X";
            psuReq = "650W - 750W Required";
        } else if (/4060/i.test(name)) {
            gpuSeries = "NVIDIA GeForce RTX 40 Series (Ada Lovelace)";
            vram = /16GB/i.test(name) ? "16GB GDDR6" : "8GB GDDR6";
            psuReq = "550W - 650W Required";
        } else if (/7900|7800|7700|7600/i.test(name)) {
            gpuSeries = "AMD Radeon RX 7000 Series (RDNA 3 Architecture)";
            vram = /7900/i.test(name) ? "20GB / 24GB GDDR6" : "16GB GDDR6";
            psuReq = "750W - 850W Required";
        }

        return {
            categoryTitle: "Graphics Card (GPU)",
            items: [
                { label: "Condition", value: condition },
                { label: "Brand", value: brand },
                { label: "GPU Series", value: findExisting("GPU Series") || gpuSeries },
                { label: "Memory(VRAM)", value: findExisting("Memory(VRAM)") || findExisting("Memory (VRAM)") || vram },
                { label: "Accessory Type", value: findExisting("Accessory Type") || "Desktop GPU (Triple Fan Airflow)" },
                { label: "Power Supply Requirement", value: findExisting("Power Supply Requirement") || psuReq },
            ],
        };
    }

    // 10. PC COMPONENTS - POWER SUPPLY
    if (subSlug === "power-supply" || subName.includes("power supply") || subName.includes("psu")) {
        const powerOutput = matchRegex(
            name,
            /(\d{3,4}\s?W)/i,
            "850W True Continuous Power",
        );
        let modular = "Fully Modular (100% Detachable Cables)";
        if (/Semi/i.test(name)) modular = "Semi-Modular";
        else if (/Non/i.test(name)) modular = "Non-Modular";

        let powerRating = "80 PLUS Gold Certified (Up to 90% Efficiency)";
        if (/Platinum/i.test(name)) powerRating = "80 PLUS Platinum Certified";
        else if (/Titanium/i.test(name)) powerRating = "80 PLUS Titanium Certified";
        else if (/Bronze/i.test(name)) powerRating = "80 PLUS Bronze Certified";

        return {
            categoryTitle: "Power Supply Unit (PSU)",
            items: [
                { label: "Condition", value: condition },
                { label: "Power Output", value: findExisting("Power Output") || powerOutput },
                { label: "Brand", value: brand },
                { label: "Modular", value: findExisting("Modular") || modular },
                { label: "Power Rating", value: findExisting("Power Rating") || powerRating },
            ],
        };
    }

    // 11. PC COMPONENTS - PC CASES
    if (subSlug === "pc-cases" || subName.includes("case") || subName.includes("chassis")) {
        const accType = /ARGB|RGB/i.test(name)
            ? "Panoramic Dual-Chamber ARGB Chassis"
            : "Mid-Tower High-Airflow Chassis";
        const coolerSize = "Supports up to 360mm / 420mm Radiators";
        const mbSize = "E-ATX, ATX, Micro-ATX, Mini-ITX Support";

        return {
            categoryTitle: "PC Case / Chassis",
            items: [
                { label: "Condition", value: condition },
                { label: "Accessory Type", value: findExisting("Accessory Type") || accType },
                { label: "Brand", value: brand },
                { label: "Cooler Size", value: findExisting("Cooler Size") || coolerSize },
                { label: "MB Size", value: findExisting("MB Size") || mbSize },
            ],
        };
    }

    // 12. PERIPHERALS (Headsets, Keyboards, Mouse, Mouse Pad, Speakers, Webcam, Converters, Adapters, Video & Creator)
    const peripheralCategories = [
        "headsets",
        "keyboards",
        "mouse",
        "mouse-pad",
        "speakers",
        "webcam",
        "converters",
        "adapters",
        "video-creator-equipments",
        "peripherals",
    ];
    if (
        peripheralCategories.includes(subSlug) ||
        catSlug === "peripherals" ||
        catName.includes("peripheral")
    ) {
        return {
            categoryTitle: product.subCategory || "Peripheral",
            items: [
                { label: "Condition", value: condition },
                { label: "Brand", value: brand },
            ],
        };
    }

    // 13. MONITORS & DISPLAYS
    if (
        catSlug === "monitors-displays" ||
        subSlug === "gaming-monitors" ||
        catName.includes("monitor") ||
        name.includes("Monitor")
    ) {
        let panel = "Fast IPS (1ms GtG Response, HDR10)";
        if (/OLED/i.test(name)) panel = "Quantum Dot OLED (0.03ms Response, True Black)";
        else if (/VA/i.test(name)) panel = "Curved VA High-Contrast Panel";

        const sizeMatch = matchRegex(
            name,
            /(\d{2}(?:\s?["”]|[\s-]inch))/i,
            '27 Inch QHD (2560x1440) Ultra-Clear',
        );

        return {
            categoryTitle: "Monitor & Display",
            items: [
                { label: "Condition", value: condition },
                { label: "Brand", value: brand },
                { label: "Panel", value: findExisting("Panel") || panel },
                { label: "Screen Size", value: findExisting("Screen Size") || sizeMatch },
            ],
        };
    }

    // 14. GAMING (Consoles, Controllers)
    if (subSlug === "consoles" || subName.includes("console")) {
        return {
            categoryTitle: "Gaming Console",
            items: [
                { label: "Condition", value: condition },
                { label: "Brand", value: brand },
            ],
        };
    }
    if (subSlug === "controllers" || subName.includes("controller")) {
        return {
            categoryTitle: "Gaming Controller",
            items: [
                { label: "Condition", value: condition },
                { label: "Brand", value: brand },
            ],
        };
    }

    // 15. PRODUCTS - COMPUTERS
    if (
        subSlug === "computers" ||
        subName.includes("computer") ||
        /gaming pc|desktop|workstation/i.test(name)
    ) {
        const compType = /Workstation/i.test(name)
            ? "Pro Creator / Workstation PC"
            : /Mini/i.test(name)
              ? "Ultra-Compact Mini PC"
              : "Custom High-Performance Gaming Rig";

        const proc = /i9|Ryzen\s?9/i.test(name)
            ? "Intel Core i9 / AMD Ryzen 9 Flagship Processor"
            : "Intel Core i7 / AMD Ryzen 7 High-Speed Processor";

        const gfx = /4080|4090/i.test(name)
            ? "NVIDIA GeForce RTX 4080 / 4090 High-End"
            : "NVIDIA GeForce RTX 4070 Super 12GB GDDR6X";

        return {
            categoryTitle: "Computer / Desktop PC",
            items: [
                { label: "Condition", value: condition },
                { label: "Brand", value: brand },
                { label: "Computer Type", value: findExisting("Computer Type") || compType },
                { label: "Processor", value: findExisting("Processor") || proc },
                { label: "Graphics Card", value: findExisting("Graphics Card") || gfx },
                { label: "Memory (RAM)", value: findExisting("Memory (RAM)") || "32GB High-Speed DDR5 Dual-Channel" },
                { label: "Storage", value: findExisting("Storage") || "1TB PCIe Gen 4.0 NVMe M.2 SSD" },
                { label: "Warranty", value: product.warranty?.duration || "3 Years Official ComLink Comprehensive Warranty" },
            ],
        };
    }

    // 16. PRODUCTS - TONERS AND CARTRIDGES
    if (
        subSlug === "toners-cartridges" ||
        subName.includes("toner") ||
        subName.includes("cartridge")
    ) {
        const tonerType = /Bottle/i.test(name)
            ? "Ultra High Yield Ink Bottle"
            : /Cartridge/i.test(name)
              ? "Original Ink Cartridge"
              : "High-Capacity Laser Toner Cartridge";

        return {
            categoryTitle: "Toner & Cartridge",
            items: [
                { label: "Condition", value: condition },
                { label: "Brand", value: brand },
                { label: "Type", value: findExisting("Type") || tonerType },
            ],
        };
    }

    // 17. PRODUCTS - UPS, CHAIRS, TABLES, POWER BANKS, EXTENSION CARDS, PRINTERS, SCANNERS
    const standardProductSubcategories = [
        "ups",
        "chairs",
        "tables",
        "power-banks",
        "extension-cards",
        "printers",
        "scanners",
        "products",
    ];
    if (
        standardProductSubcategories.includes(subSlug) ||
        catSlug === "products"
    ) {
        return {
            categoryTitle: product.subCategory || "Product",
            items: [
                { label: "Condition", value: condition },
                { label: "Brand", value: brand },
            ],
        };
    }

    // 18. SOFTWARE
    if (catSlug === "software" || catName.includes("software")) {
        return {
            categoryTitle: "Software",
            items: [{ label: "Brand", value: brand }],
        };
    }

    // General Fallback
    const fallbackItems: MainSpecItem[] = [
        { label: "Condition", value: condition },
        { label: "Brand", value: brand },
    ];
    if (product.main_specifications && typeof product.main_specifications === "object") {
        for (const [k, v] of Object.entries(product.main_specifications)) {
            if (v && typeof v === "string" && v.trim().length > 0) {
                const label = k.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
                if (!fallbackItems.some((fi) => fi.label.toLowerCase() === label.toLowerCase())) {
                    fallbackItems.push({ label, value: String(v) });
                }
            }
        }
    }
    return {
        categoryTitle: product.subCategory || product.category || "Hardware",
        items: fallbackItems,
    };
}
