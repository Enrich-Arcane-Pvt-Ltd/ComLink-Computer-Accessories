import { ProductCard, ProductCardData } from "@/Components/product/ProductCard";
import AppLayout from "@/Layouts/AppLayout";
import { formatPrice, CURRENCY_SYMBOL } from "@/utils/currency";
import { Head, Link, router } from "@inertiajs/react";
import {
    ChevronDown,
    ChevronRight,
    Filter,
    Grid3X3,
    LayoutGrid,
    RotateCcw,
    Search,
    ShieldCheck,
    SlidersHorizontal,
    Truck,
    Wrench,
    X,
} from "lucide-react";
import React, { useMemo, useState } from "react";

/* -------------------------------------------------------------------------- */
/*  Types                                                                     */
/* -------------------------------------------------------------------------- */

export interface SubCategoryData {
    id: number | string;
    name: string;
    slug: string;
    description?: string;
    image?: string;
    count?: number;
}

export interface CategoryData {
    id: number | string;
    name: string;
    slug: string;
    description?: string;
    image?: string;
    count?: number;
    subcategories: SubCategoryData[];
}

export interface FilterFacet {
    id: string;
    label: string;
    options: string[];
}

export interface CategoryFilterSchema {
    hasCondition: boolean; // false for thermal-paste & software
    facets: FilterFacet[];
}

interface ShopProps {
    categories: CategoryData[];
    activeCategory?: CategoryData | null;
    activeSubCategory?: SubCategoryData | null;
    products: ProductCardData[];
    dynamicFacets?: FilterFacet[];
    filters: Record<string, any>;
}

/* -------------------------------------------------------------------------- */
/*  Category & Subcategory Specific Filter Configurations                     */
/* -------------------------------------------------------------------------- */

const CATEGORY_FILTER_CONFIG: Record<string, CategoryFilterSchema> = {
    // 1. Laptops
    laptops: {
        hasCondition: true,
        facets: [
            { id: "brand", label: "Brand", options: ["ASUS", "Lenovo", "MSI", "Dell", "HP", "Acer", "Apple", "Razer"] },
            { id: "display_size", label: "Display Size", options: ['13.3"', '14.0"', '15.6"', '16.0"', '17.3"'] },
            { id: "graphic_cards", label: "Graphic Cards", options: ["NVIDIA RTX 4090", "NVIDIA RTX 4080", "NVIDIA RTX 4070", "NVIDIA RTX 4060", "NVIDIA RTX 4050", "NVIDIA RTX 3050", "Intel Iris Xe", "AMD Radeon"] },
            { id: "memory", label: "Memory", options: ["8GB", "16GB", "32GB", "64GB"] },
            { id: "processor", label: "Processor", options: ["Intel Core i5", "Intel Core i7", "Intel Core i9", "Intel Core Ultra 7", "Intel Core Ultra 9", "AMD Ryzen 5", "AMD Ryzen 7", "AMD Ryzen 9"] },
            { id: "storage", label: "Storage", options: ["512GB SSD", "1TB SSD", "2TB SSD"] },
            { id: "type", label: "Type", options: ["Gaming Laptop", "Ultrabook", "Business Laptop", "2-in-1 Convertible"] },
        ],
    },

    // 2. PC Components - Processors
    processors: {
        hasCondition: true,
        facets: [
            { id: "brand", label: "Brand", options: ["Intel", "AMD"] },
            { id: "generation", label: "Generation", options: ["Intel 14th Gen", "Intel 13th Gen", "Intel 12th Gen", "AMD Ryzen 9000 Series", "AMD Ryzen 7000 Series", "AMD Ryzen 5000 Series"] },
            { id: "cores", label: "CORES", options: ["4 Cores", "6 Cores", "8 Cores", "12 Cores", "14 Cores", "16 Cores", "20 Cores", "24 Cores"] },
            { id: "threads", label: "THREADS", options: ["8 Threads", "12 Threads", "16 Threads", "20 Threads", "24 Threads", "28 Threads", "32 Threads"] },
        ],
    },

    // 3. PC Components - Thermal Paste
    "thermal-paste": {
        hasCondition: false,
        facets: [
            { id: "brand", label: "Brand", options: ["Arctic", "Thermal Grizzly", "Noctua", "Corsair", "Cooler Master"] },
        ],
    },

    // 4. PC Components - Motherboards
    motherboards: {
        hasCondition: true,
        facets: [
            { id: "brand", label: "Brand", options: ["ASUS", "MSI", "Gigabyte", "ASRock"] },
            { id: "performance_level", label: "Performance Level", options: ["Entry Level", "Mainstream", "High-End Enthusiast"] },
            { id: "cpu_socket", label: "CPU Socket", options: ["LGA 1700", "AM5", "AM4", "LGA 1851"] },
            { id: "chipset_model", label: "Chipset Model", options: ["Intel Z790", "Intel B760", "Intel Z890", "AMD B650", "AMD X670E", "AMD X870", "AMD A620"] },
            { id: "mb_platform", label: "MB Platform", options: ["Intel Platform", "AMD Platform"] },
        ],
    },

    // 5. PC Components - Coolers
    coolers: {
        hasCondition: true,
        facets: [
            { id: "fans_size", label: "Fans Size", options: ["120mm", "140mm"] },
            { id: "radiator_size", label: "Radiator Size", options: ["120mm", "240mm", "280mm", "360mm", "420mm"] },
            { id: "cooling_type", label: "Cooling Type", options: ["AIO Liquid Cooler", "Air Cooler", "Custom Loop"] },
            { id: "brand", label: "Brand", options: ["NZXT", "Corsair", "DeepCool", "Arctic", "Cooler Master", "Thermalright"] },
        ],
    },

    // 6. PC Components - Memory Card (RAM)
    memory: {
        hasCondition: true,
        facets: [
            { id: "brand", label: "Brand", options: ["Corsair", "G.Skill", "Kingston", "TeamGroup", "Crucial"] },
            { id: "ram_capacity", label: "RAM Capacity", options: ["8GB", "16GB", "32GB (2x16GB)", "64GB (2x32GB)", "128GB"] },
            { id: "memory_speed", label: "Memory Speed", options: ["3200MHz", "3600MHz", "5200MHz", "5600MHz", "6000MHz", "6400MHz", "7200MHz"] },
            { id: "form_factor", label: "Form Factor", options: ["Desktop UDIMM", "Laptop SODIMM"] },
            { id: "memory_gen", label: "Memory Gen", options: ["DDR4", "DDR5"] },
        ],
    },

    // 7. PC Components - SSD
    ssd: {
        hasCondition: true,
        facets: [
            { id: "generation", label: "Generation", options: ["PCIe Gen 3.0", "PCIe Gen 4.0", "PCIe Gen 5.0"] },
            { id: "storage_capacity", label: "Storage Capacity", options: ["256GB", "500GB / 512GB", "1TB", "2TB", "4TB"] },
            { id: "brand", label: "Brand", options: ["Samsung", "Western Digital", "Kingston", "Crucial", "Lexar"] },
            { id: "ssd_type", label: "SSD Type", options: ["M.2 NVMe", "2.5\" SATA III"] },
        ],
    },

    // 8. PC Components - Hard Disk
    "hard-disk": {
        hasCondition: true,
        facets: [
            { id: "brand", label: "Brand", options: ["Seagate", "Western Digital", "Toshiba"] },
            { id: "hdd_color_type", label: "HDD Color Type", options: ["WD Blue", "WD Black", "WD Red (NAS)", "WD Purple (Surveillance)", "Seagate Barracuda", "Seagate IronWolf"] },
            { id: "storage_capacity", label: "Storage Capacity", options: ["1TB", "2TB", "4TB", "6TB", "8TB", "12TB+"] },
            { id: "storage_type", label: "Storage Type", options: ["Internal 3.5\"", "External Portable 2.5\""] },
        ],
    },

    // 9. PC Components - Graphic Cards
    "graphic-cards": {
        hasCondition: true,
        facets: [
            { id: "brand", label: "Brand", options: ["NVIDIA", "ASUS", "MSI", "Gigabyte", "Sapphire", "Zotac", "PNY", "PowerColor"] },
            { id: "gpu_series", label: "GPU Series", options: ["GeForce RTX 40 Series", "GeForce RTX 30 Series", "Radeon RX 7000 Series", "Radeon RX 6000 Series"] },
            { id: "vram", label: "Memory (VRAM)", options: ["6GB", "8GB", "12GB", "16GB", "20GB", "24GB"] },
            { id: "accessory_type", label: "Accessory Type", options: ["GPU Anti-Sag Holder", "PCIe Riser Cable", "None"] },
            { id: "psu_requirement", label: "Power Supply Requirement", options: ["500W", "650W", "750W", "850W", "1000W+"] },
        ],
    },

    // 10. PC Components - Power Supply
    "power-supply": {
        hasCondition: true,
        facets: [
            { id: "power_output", label: "Power Output", options: ["550W", "650W", "750W", "850W", "1000W", "1200W+"] },
            { id: "brand", label: "Brand", options: ["Corsair", "Seasonic", "Cooler Master", "Thermaltake", "MSI", "ASUS ROG"] },
            { id: "modular", label: "Modular", options: ["Fully Modular", "Semi-Modular", "Non-Modular"] },
            { id: "power_rating", label: "Power Rating", options: ["80 PLUS Bronze", "80 PLUS Gold", "80 PLUS Platinum", "80 PLUS Titanium"] },
        ],
    },

    // 11. PC Components - PC Cases
    "pc-cases": {
        hasCondition: true,
        facets: [
            { id: "accessory_type", label: "Accessory Type", options: ["ARGB Fan Kit", "Vertical GPU Mount", "RGB Strip"] },
            { id: "brand", label: "Brand", options: ["NZXT", "Lian Li", "Corsair", "Fractal Design", "Cooler Master", "Phanteks"] },
            { id: "cooler_size", label: "Cooler Size", options: ["240mm Support", "280mm Support", "360mm Support", "420mm Support"] },
            { id: "mb_size", label: "MB Size", options: ["Mini-ITX", "Micro-ATX", "ATX", "E-ATX"] },
        ],
    },

    // General fallback for PC Components main
    "pc-components": {
        hasCondition: true,
        facets: [
            { id: "brand", label: "Brand", options: ["Intel", "AMD", "ASUS", "MSI", "Corsair", "Gigabyte", "NZXT", "Samsung"] },
        ],
    },

    // 12. Peripherals
    headsets: {
        hasCondition: true,
        facets: [{ id: "brand", label: "Brand", options: ["Razer", "Logitech G", "HyperX", "SteelSeries", "Corsair", "Sony"] }],
    },
    keyboards: {
        hasCondition: true,
        facets: [{ id: "brand", label: "Brand", options: ["Redragon", "Logitech G", "Razer", "Keychron", "SteelSeries", "Corsair"] }],
    },
    mouse: {
        hasCondition: true,
        facets: [{ id: "brand", label: "Brand", options: ["Logitech G", "Razer", "SteelSeries", "Glorious", "Pulsar", "Zowie"] }],
    },
    "mouse-pad": {
        hasCondition: true,
        facets: [{ id: "brand", label: "Brand", options: ["Razer", "SteelSeries", "Corsair", "Logitech G", "Aqua Control"] }],
    },
    speakers: {
        hasCondition: true,
        facets: [{ id: "brand", label: "Brand", options: ["Logitech", "Creative", "Edifier", "Razer"] }],
    },
    webcam: {
        hasCondition: true,
        facets: [{ id: "brand", label: "Brand", options: ["Logitech", "Elgato", "Razer", "Anker"] }],
    },
    converters: {
        hasCondition: true,
        facets: [{ id: "brand", label: "Brand", options: ["UGREEN", "Baseus", "Vention"] }],
    },
    adapters: {
        hasCondition: true,
        facets: [{ id: "brand", label: "Brand", options: ["UGREEN", "Baseus", "TP-Link", "Apple"] }],
    },
    "video-creator-equipments": {
        hasCondition: true,
        facets: [{ id: "brand", label: "Brand", options: ["Elgato", "Rode", "Shure", "Sony", "DJI"] }],
    },
    peripherals: {
        hasCondition: true,
        facets: [{ id: "brand", label: "Brand", options: ["Logitech", "Razer", "SteelSeries", "Corsair", "Elgato", "Redragon"] }],
    },

    // 13. Monitors & Displays
    "monitors-displays": {
        hasCondition: true,
        facets: [
            { id: "brand", label: "Brand", options: ["Samsung", "LG", "ASUS ROG", "Dell", "MSI", "ViewSonic", "AOC"] },
            { id: "panel", label: "Panel", options: ["IPS", "Fast IPS", "OLED", "QD-OLED", "VA", "TN"] },
            { id: "screen_size", label: "Screen Size", options: ['24 Inch', '27 Inch', '32 Inch', '34 Inch Ultrawide', '49 Inch Super Ultrawide'] },
        ],
    },

    // 14. Gaming
    "gaming-laptops": {
        hasCondition: true,
        facets: [
            { id: "brand", label: "Brand", options: ["ASUS ROG", "Lenovo Legion", "MSI Gaming", "Acer Predator", "Dell Alienware", "Razer Blade"] },
            { id: "display_size", label: "Display Size", options: ['15.6"', '16.0"', '17.3"', '18.0"'] },
            { id: "graphic_cards", label: "Graphic Cards", options: ["RTX 4090", "RTX 4080", "RTX 4070", "RTX 4060", "RTX 4050"] },
            { id: "memory", label: "Memory", options: ["16GB", "32GB", "64GB"] },
            { id: "processor", label: "Processor", options: ["Intel Core i7", "Intel Core i9", "AMD Ryzen 7", "AMD Ryzen 9"] },
            { id: "storage", label: "Storage", options: ["512GB SSD", "1TB SSD", "2TB SSD"] },
            { id: "type", label: "Type", options: ["High Performance", "Slim Gaming", "Desktop Replacement"] },
        ],
    },
    consoles: {
        hasCondition: true,
        facets: [{ id: "brand", label: "Brand", options: ["Sony PlayStation", "Microsoft Xbox", "Nintendo Switch", "Valve Steam Deck"] }],
    },
    controllers: {
        hasCondition: true,
        facets: [{ id: "brand", label: "Brand", options: ["Sony DualSense", "Xbox Wireless", "Razer Wolverine", "Logitech", "8BitDo"] }],
    },
    gaming: {
        hasCondition: true,
        facets: [{ id: "brand", label: "Brand", options: ["Sony", "Microsoft", "Nintendo", "ASUS ROG", "Razer", "Logitech"] }],
    },

    // 15. Products
    computers: {
        hasCondition: true,
        facets: [
            { id: "brand", label: "Brand", options: ["ComLink Custom PC", "ASUS", "HP", "Dell", "Lenovo", "Apple"] },
            { id: "computer_type", label: "Computer Type", options: ["Custom Gaming PC", "Workstation PC", "All-In-One", "Mini PC", "Budget Office PC"] },
            { id: "processor", label: "Processor", options: ["Intel Core i5", "Intel Core i7", "Intel Core i9", "AMD Ryzen 5", "AMD Ryzen 7", "AMD Ryzen 9"] },
            { id: "graphics_card", label: "Graphics Card", options: ["RTX 4060", "RTX 4070", "RTX 4080", "RTX 4090", "Integrated Graphics"] },
            { id: "memory_ram", label: "Memory (RAM)", options: ["16GB", "32GB", "64GB", "128GB"] },
            { id: "storage", label: "Storage", options: ["512GB SSD", "1TB SSD", "2TB SSD", "1TB SSD + 2TB HDD"] },
            { id: "warranty", label: "Warranty", options: ["1 Year Warranty", "2 Years Warranty", "3 Years Warranty"] },
        ],
    },
    ups: {
        hasCondition: true,
        facets: [{ id: "brand", label: "Brand", options: ["APC", "Prolink", "Mercury", "Eaton"] }],
    },
    chairs: {
        hasCondition: true,
        facets: [{ id: "brand", label: "Brand", options: ["Secretlab", "DXRacer", "Cougar", "Razer", "Fantech"] }],
    },
    tables: {
        hasCondition: true,
        facets: [{ id: "brand", label: "Brand", options: ["Eureka Ergonomic", "Cougar", "Thermaltake", "Custom Gaming Desk"] }],
    },
    "power-banks": {
        hasCondition: true,
        facets: [{ id: "brand", label: "Brand", options: ["Anker", "Xiaomi", "Baseus", "Romoss"] }],
    },
    "extension-cards": {
        hasCondition: true,
        facets: [{ id: "brand", label: "Brand", options: ["Huntkey", "Belkin", "LDNIO", "Schneider Electric"] }],
    },
    printers: {
        hasCondition: true,
        facets: [{ id: "brand", label: "Brand", options: ["Epson", "Canon", "HP", "Brother"] }],
    },
    "toners-cartridges": {
        hasCondition: true,
        facets: [
            { id: "brand", label: "Brand", options: ["HP", "Canon", "Epson", "Brother"] },
            { id: "toner_type", label: "Type", options: ["Ink Bottle", "Ink Cartridge", "Ribbon", "Toner Cartridge"] },
        ],
    },
    scanners: {
        hasCondition: true,
        facets: [{ id: "brand", label: "Brand", options: ["Canon", "Epson", "HP", "Fujitsu"] }],
    },
    products: {
        hasCondition: true,
        facets: [{ id: "brand", label: "Brand", options: ["ComLink", "HP", "Epson", "Secretlab", "APC", "Anker"] }],
    },

    // 16. Software
    software: {
        hasCondition: false,
        facets: [
            { id: "brand", label: "Brand", options: ["Microsoft", "Kaspersky", "Adobe", "Bitdefender", "ESET"] },
        ],
    },
};

/* -------------------------------------------------------------------------- */
/*  Price Range Bar Component                                                 */
/* -------------------------------------------------------------------------- */

function PriceBarSlider({
    minPrice,
    maxPrice,
    onMinChange,
    onMaxChange,
    onApply,
    onReset,
}: {
    minPrice: string | number;
    maxPrice: string | number;
    onMinChange: (val: string) => void;
    onMaxChange: (val: string) => void;
    onApply: () => void;
    onReset: () => void;
}) {
    const minLimit = 0;
    const maxLimit = 3000;
    const parsedMin = minPrice === "" ? minLimit : Math.max(minLimit, Math.min(maxLimit, Number(minPrice)));
    const parsedMax = maxPrice === "" ? maxLimit : Math.max(minLimit, Math.min(maxLimit, Number(maxPrice)));

    const leftPercent = (parsedMin / maxLimit) * 100;
    const rightPercent = (parsedMax / maxLimit) * 100;
    const fillWidth = Math.max(0, rightPercent - leftPercent);

    return (
        <div className="space-y-4 pt-1">
            {/* Visual Bar Track with Dual Thumbs */}
            <div className="relative w-full h-7 flex items-center select-none">
                {/* Background Track */}
                <div className="absolute w-full h-2 bg-muted/20 dark:bg-muted/30 rounded-full" />

                {/* Active Colored Fill Bar */}
                <div
                    className="absolute h-2 bg-accent rounded-full transition-all pointer-events-none"
                    style={{
                        left: `${leftPercent}%`,
                        width: `${fillWidth}%`,
                    }}
                />

                {/* Min Slider Input (invisible thumb overlay) */}
                <input
                    type="range"
                    min={minLimit}
                    max={maxLimit}
                    step={25}
                    value={parsedMin}
                    onChange={(e) => {
                        const val = Math.min(Number(e.target.value), parsedMax - 25);
                        onMinChange(val === 0 ? "" : val.toString());
                    }}
                    className="absolute w-full h-3 opacity-0 cursor-pointer pointer-events-auto z-30"
                />

                {/* Max Slider Input (invisible thumb overlay) */}
                <input
                    type="range"
                    min={minLimit}
                    max={maxLimit}
                    step={25}
                    value={parsedMax}
                    onChange={(e) => {
                        const val = Math.max(Number(e.target.value), parsedMin + 25);
                        onMaxChange(val === maxLimit ? "" : val.toString());
                    }}
                    className="absolute w-full h-3 opacity-0 cursor-pointer pointer-events-auto z-30"
                />

                {/* Styled Visual Thumb: Min */}
                <div
                    className="absolute w-4 h-4 rounded-full bg-accent border-2 border-card shadow-md -translate-x-1/2 pointer-events-none z-20 transition-all"
                    style={{ left: `${leftPercent}%` }}
                />

                {/* Styled Visual Thumb: Max */}
                <div
                    className="absolute w-4 h-4 rounded-full bg-accent border-2 border-card shadow-md -translate-x-1/2 pointer-events-none z-20 transition-all"
                    style={{ left: `${rightPercent}%` }}
                />
            </div>

            {/* Price values text along bar */}
            <div className="flex items-center justify-between text-[11px] text-muted font-medium px-0.5">
                <span>{formatPrice(0, 0)}</span>
                <span className="text-accent font-semibold">
                    {formatPrice(parsedMin, 0)} - {formatPrice(parsedMax, 0)}
                </span>
                <span>{formatPrice(500000, 0)}+</span>
            </div>

            {/* Input boxes */}
            <div className="grid grid-cols-2 gap-2">
                <div>
                    <span className="text-[10px] text-muted block mb-1 font-medium">Min Price ({CURRENCY_SYMBOL.trim()})</span>
                    <input
                        type="number"
                        placeholder="0"
                        value={minPrice}
                        onChange={(e) => onMinChange(e.target.value)}
                        className="w-full bg-bg border border-border rounded-lg px-2.5 py-1.5 text-xs text-fg focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-colors"
                    />
                </div>
                <div>
                    <span className="text-[10px] text-muted block mb-1 font-medium">Max Price ({CURRENCY_SYMBOL.trim()})</span>
                    <input
                        type="number"
                        placeholder="500000+"
                        value={maxPrice}
                        onChange={(e) => onMaxChange(e.target.value)}
                        className="w-full bg-bg border border-border rounded-lg px-2.5 py-1.5 text-xs text-fg focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-colors"
                    />
                </div>
            </div>

            {/* Quick Price Ranges */}
            <div className="flex flex-wrap gap-1.5 pt-0.5">
                {[
                    { label: `< ${formatPrice(10000, 0)}`, min: "0", max: "10000" },
                    { label: `${CURRENCY_SYMBOL}10k - 50k`, min: "10000", max: "50000" },
                    { label: `${CURRENCY_SYMBOL}50k - 150k`, min: "50000", max: "150000" },
                    { label: `${CURRENCY_SYMBOL}150k - 300k`, min: "150000", max: "300000" },
                    { label: `${CURRENCY_SYMBOL}300k+`, min: "300000", max: "1000000" },
                ].map((preset) => (
                    <button
                        key={preset.label}
                        type="button"
                        onClick={() => {
                            onMinChange(preset.min);
                            onMaxChange(preset.max);
                        }}
                        className="text-[10px] px-2 py-1 bg-bg border border-border/80 rounded-md text-muted hover:text-fg hover:border-accent transition-colors"
                    >
                        {preset.label}
                    </button>
                ))}
            </div>

            <button
                type="button"
                onClick={onApply}
                className="w-full py-1.5 bg-card hover:bg-accent hover:text-white dark:hover:text-slate-950 border border-border hover:border-accent rounded-lg text-xs font-semibold transition-all mt-1 shadow-sm"
            >
                Apply Price Filter
            </button>
        </div>
    );
}

/* -------------------------------------------------------------------------- */
/*  Main Shop Component                                                       */
/* -------------------------------------------------------------------------- */

export default function ShopIndex({
    categories = [],
    activeCategory = null,
    activeSubCategory = null,
    products = [],
    dynamicFacets = [],
    filters = {},
}: ShopProps) {
    // Current active filter schema based on dynamic specifications or selected category/subcategory
    const activeSchema = useMemo<CategoryFilterSchema>(() => {
        if (dynamicFacets && dynamicFacets.length > 0) {
            const hasCondition = activeCategory?.slug !== "thermal-paste" && activeCategory?.slug !== "software";
            return {
                hasCondition,
                facets: dynamicFacets,
            };
        }
        if (activeSubCategory?.slug && CATEGORY_FILTER_CONFIG[activeSubCategory.slug]) {
            return CATEGORY_FILTER_CONFIG[activeSubCategory.slug];
        }
        if (activeCategory?.slug && CATEGORY_FILTER_CONFIG[activeCategory.slug]) {
            return CATEGORY_FILTER_CONFIG[activeCategory.slug];
        }
        // Universal fallback for All Categories
        return {
            hasCondition: true,
            facets: [
                { id: "brand", label: "Brand", options: ["ASUS", "Intel", "AMD", "MSI", "Corsair", "Logitech", "Samsung", "Razer"] },
            ],
        };
    }, [activeCategory, activeSubCategory, dynamicFacets]);

    // Local filter state
    const [condition, setCondition] = useState<string>(filters.condition || "all");
    const [searchTerm, setSearchTerm] = useState(filters.q || "");
    const [minPrice, setMinPrice] = useState(filters.min_price || "");
    const [maxPrice, setMaxPrice] = useState(filters.max_price || "");
    const [inStockOnly, setInStockOnly] = useState(Boolean(filters.in_stock));
    const [outOfStockOnly, setOutOfStockOnly] = useState(Boolean(filters.out_of_stock));
    const [facetSearches, setFacetSearches] = useState<Record<string, string>>({});
    const [mobileFilterOpen, setMobileFilterOpen] = useState(false);
    const [columns, setColumns] = useState<3 | 4>(3);

    // Track which facet accordions are expanded
    const [expandedFacets, setExpandedFacets] = useState<Record<string, boolean>>(() => {
        const init: Record<string, boolean> = { categories: true };
        activeSchema.facets.forEach((f) => {
            init[f.id] = true;
        });
        return init;
    });

    React.useEffect(() => {
        setExpandedFacets((prev) => {
            const updated: Record<string, boolean> = { ...prev, categories: true };
            activeSchema.facets.forEach((f) => {
                if (updated[f.id] === undefined) {
                    updated[f.id] = true;
                }
            });
            return updated;
        });
    }, [activeSchema]);

    const toggleFacet = (id: string) => {
        setExpandedFacets((prev) => ({ ...prev, [id]: !prev[id] }));
    };

    // Track which category dropdowns are open in the sidebar
    const [expandedCategories, setExpandedCategories] = useState<Record<string, boolean>>(() => {
        const initial: Record<string, boolean> = {};
        if (activeCategory?.slug) {
            initial[activeCategory.slug] = true;
        }
        return initial;
    });

    const toggleAccordion = (slug: string) => {
        setExpandedCategories((prev) => ({
            ...prev,
            [slug]: !prev[slug],
        }));
    };

    // Helper to check if a facet option is selected
    const isOptionSelected = (facetId: string, optionValue: string): boolean => {
        const current = filters[facetId];
        if (!current) return false;
        if (Array.isArray(current)) {
            return current.includes(optionValue);
        }
        return current.toString().split(",").includes(optionValue);
    };

    // Apply URL filter updates via Inertia
    const applyFilters = (newParams: Record<string, any>) => {
        const query: Record<string, any> = {
            category: activeCategory?.slug || undefined,
            subcategory: activeSubCategory?.slug || undefined,
            condition: condition !== "all" ? condition : undefined,
            q: searchTerm || undefined,
            sort: filters.sort || undefined,
            min_price: minPrice || undefined,
            max_price: maxPrice || undefined,
            in_stock: inStockOnly ? "1" : undefined,
            out_of_stock: outOfStockOnly ? "1" : undefined,
            ...filters,
            ...newParams,
        };

        // Clean empty values
        Object.keys(query).forEach((key) => {
            if (query[key] === undefined || query[key] === "" || query[key] === null) {
                delete query[key];
            }
        });

        router.get("/shop", query, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    // Toggle a checkbox inside any facet
    const handleToggleFacetOption = (facetId: string, optionValue: string) => {
        const current = filters[facetId];
        let values: string[] = [];
        if (Array.isArray(current)) {
            values = [...current];
        } else if (typeof current === "string" && current.length > 0) {
            values = current.split(",");
        }

        if (values.includes(optionValue)) {
            values = values.filter((v) => v !== optionValue);
        } else {
            values.push(optionValue);
        }

        applyFilters({
            [facetId]: values.length > 0 ? values.join(",") : undefined,
        });
    };

    const handleConditionChange = (newCondition: string) => {
        setCondition(newCondition);
        applyFilters({
            condition: newCondition !== "all" ? newCondition : undefined,
        });
    };

    const handleSortChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
        applyFilters({ sort: e.target.value });
    };

    const handleSearchSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        applyFilters({ q: searchTerm });
    };

    const clearFilters = () => {
        setCondition("all");
        setSearchTerm("");
        setMinPrice("");
        setMaxPrice("");
        setInStockOnly(false);
        setOutOfStockOnly(false);
        router.get(
            "/shop",
            {
                category: activeCategory?.slug || undefined,
                subcategory: activeSubCategory?.slug || undefined,
            },
            { preserveState: false }
        );
    };

    // Collect all applied filter tags for top pill bar
    const appliedPills = useMemo(() => {
        const pills: { key: string; label: string; onRemove: () => void }[] = [];

        if (condition && condition !== "all") {
            pills.push({
                key: `condition-${condition}`,
                label: `Condition: ${condition === "brandnew" ? "Brandnew" : "Used"}`,
                onRemove: () => handleConditionChange("all"),
            });
        }

        if (inStockOnly) {
            pills.push({
                key: "in_stock",
                label: "In Stock Only",
                onRemove: () => {
                    setInStockOnly(false);
                    applyFilters({ in_stock: undefined });
                },
            });
        }

        if (outOfStockOnly) {
            pills.push({
                key: "out_of_stock",
                label: "Out of Stock",
                onRemove: () => {
                    setOutOfStockOnly(false);
                    applyFilters({ out_of_stock: undefined });
                },
            });
        }

        if (minPrice || maxPrice) {
            pills.push({
                key: "price_range",
                label: `Price: ${CURRENCY_SYMBOL}${minPrice || "0"} - ${CURRENCY_SYMBOL}${maxPrice || "Max"}`,
                onRemove: () => {
                    setMinPrice("");
                    setMaxPrice("");
                    applyFilters({ min_price: undefined, max_price: undefined });
                },
            });
        }

        if (searchTerm) {
            pills.push({
                key: "search_term",
                label: `Search: "${searchTerm}"`,
                onRemove: () => {
                    setSearchTerm("");
                    applyFilters({ q: undefined });
                },
            });
        }

        activeSchema.facets.forEach((facet) => {
            const raw = filters[facet.id];
            if (raw) {
                const values = raw.toString().split(",");
                values.forEach((val: string) => {
                    pills.push({
                        key: `${facet.id}-${val}`,
                        label: `${facet.label}: ${val}`,
                        onRemove: () => handleToggleFacetOption(facet.id, val),
                    });
                });
            }
        });

        return pills;
    }, [condition, inStockOnly, outOfStockOnly, minPrice, maxPrice, searchTerm, filters, activeSchema]);

    // Page title
    const pageTitle = useMemo(() => {
        if (activeSubCategory && activeCategory) {
            return `${activeSubCategory.name} - ${activeCategory.name} | WikiStore`;
        }
        if (activeCategory) {
            return `${activeCategory.name} | WikiStore`;
        }
        return "All Categories & Products | WikiStore";
    }, [activeCategory, activeSubCategory]);

    // Active subcategories list
    const currentSubcategories = activeCategory?.subcategories || [];

    /* ---------------------------------------------------------------------- */
    /*  Reusable Filter Controls Component                                    */
    /* ---------------------------------------------------------------------- */
    const renderFilterControls = (isMobile = false) => (
        <div className="space-y-5">
            {/* 1. Condition Selector (All / Brandnew / Used) - Only if enabled for this category */}
            {activeSchema.hasCondition && (
                <div className="bg-card rounded-2xl border border-border p-4 shadow-sm">
                    <label className="text-xs font-bold uppercase tracking-wider text-muted block mb-3">
                        Condition
                    </label>
                    <div className="grid grid-cols-3 gap-1.5 p-1 bg-bg/80 rounded-xl border border-border/80">
                        <button
                            type="button"
                            onClick={() => handleConditionChange("all")}
                            className={`py-1.5 px-2 rounded-lg text-xs font-bold transition-all ${
                                condition === "all"
                                    ? "bg-accent text-white dark:text-slate-950 shadow-sm"
                                    : "text-muted hover:text-fg hover:bg-muted/10"
                            }`}
                        >
                            All
                        </button>
                        <button
                            type="button"
                            onClick={() => handleConditionChange("brandnew")}
                            className={`py-1.5 px-2 rounded-lg text-xs font-bold transition-all ${
                                condition === "brandnew"
                                    ? "bg-accent text-white dark:text-slate-950 shadow-sm"
                                    : "text-muted hover:text-fg hover:bg-muted/10"
                            }`}
                        >
                            Brandnew
                        </button>
                        <button
                            type="button"
                            onClick={() => handleConditionChange("used")}
                            className={`py-1.5 px-2 rounded-lg text-xs font-bold transition-all ${
                                condition === "used"
                                    ? "bg-accent text-white dark:text-slate-950 shadow-sm"
                                    : "text-muted hover:text-fg hover:bg-muted/10"
                            }`}
                        >
                            Used
                        </button>
                    </div>
                </div>
            )}

            {/* 2. Price Range Bar & Visual Slider */}
            <div className="bg-card rounded-2xl border border-border p-4 shadow-sm space-y-3">
                <div className="flex items-center justify-between">
                    <label className="text-xs font-bold uppercase tracking-wider text-muted">
                        Price Range
                    </label>
                    {(minPrice || maxPrice) && (
                        <button
                            type="button"
                            onClick={() => {
                                setMinPrice("");
                                setMaxPrice("");
                                applyFilters({ min_price: undefined, max_price: undefined });
                            }}
                            className="text-[10px] text-accent hover:underline font-medium"
                        >
                            Reset
                        </button>
                    )}
                </div>

                <PriceBarSlider
                    minPrice={minPrice}
                    maxPrice={maxPrice}
                    onMinChange={setMinPrice}
                    onMaxChange={setMaxPrice}
                    onApply={() => applyFilters({ min_price: minPrice, max_price: maxPrice })}
                    onReset={() => {
                        setMinPrice("");
                        setMaxPrice("");
                        applyFilters({ min_price: undefined, max_price: undefined });
                    }}
                />
            </div>

            {/* 3. Availability Checkboxes */}
            <div className="bg-card rounded-2xl border border-border p-4 shadow-sm space-y-2.5">
                <label className="text-xs font-bold uppercase tracking-wider text-muted block">
                    Availability
                </label>
                <div className="space-y-2">
                    <label className="flex items-center gap-2.5 cursor-pointer text-xs text-fg select-none group">
                        <input
                            type="checkbox"
                            checked={inStockOnly}
                            onChange={(e) => {
                                const val = e.target.checked;
                                setInStockOnly(val);
                                applyFilters({ in_stock: val ? "1" : undefined });
                            }}
                            className="rounded border-border text-accent focus:ring-accent w-4 h-4 bg-bg cursor-pointer accent-cyan-600 dark:accent-cyan-400"
                        />
                        <span className="flex items-center gap-1.5 text-fg group-hover:text-accent transition-colors">
                            <span className="w-2 h-2 rounded-full bg-emerald-500 inline-block" />
                            In Stock
                        </span>
                    </label>

                    <label className="flex items-center gap-2.5 cursor-pointer text-xs text-fg select-none group">
                        <input
                            type="checkbox"
                            checked={outOfStockOnly}
                            onChange={(e) => {
                                const val = e.target.checked;
                                setOutOfStockOnly(val);
                                applyFilters({ out_of_stock: val ? "1" : undefined });
                            }}
                            className="rounded border-border text-accent focus:ring-accent w-4 h-4 bg-bg cursor-pointer accent-cyan-600 dark:accent-cyan-400"
                        />
                        <span className="flex items-center gap-1.5 text-fg group-hover:text-accent transition-colors">
                            <span className="w-2 h-2 rounded-full bg-rose-500 inline-block" />
                            Out of Stock
                        </span>
                    </label>
                </div>
            </div>

            {/* 4. Dynamic Category Facets (Brand, Socket, Cores, VRAM, RAM, etc.) */}
            {activeSchema.facets.map((facet) => {
                const isExpanded = Boolean(expandedFacets[facet.id]);
                const searchVal = facetSearches[facet.id] || "";
                const filteredOptions = facet.options.filter((opt) =>
                    opt.toLowerCase().includes(searchVal.toLowerCase())
                );
                const selectedCount = facet.options.filter((opt) =>
                    isOptionSelected(facet.id, opt)
                ).length;

                return (
                    <div
                        key={facet.id}
                        className="bg-card rounded-2xl border border-border shadow-sm overflow-hidden"
                    >
                        {/* Facet Header */}
                        <button
                            type="button"
                            onClick={() => toggleFacet(facet.id)}
                            className="w-full flex items-center justify-between p-4 text-left transition-colors hover:bg-muted/10 group"
                        >
                            <div className="flex items-center gap-2">
                                <span className="font-display font-bold text-xs uppercase tracking-wider text-fg group-hover:text-accent transition-colors">
                                    {facet.label}
                                </span>
                                {selectedCount > 0 && (
                                    <span className="text-[10px] px-1.5 py-0.2 bg-accent text-white dark:text-slate-950 font-bold rounded-full">
                                        {selectedCount}
                                    </span>
                                )}
                            </div>
                            <ChevronDown
                                className={`w-4 h-4 text-muted group-hover:text-fg transition-transform duration-200 ${
                                    isExpanded ? "rotate-180" : ""
                                }`}
                            />
                        </button>

                        {/* Facet Body */}
                        {isExpanded && (
                            <div className="px-4 pb-4 pt-1 space-y-2 border-t border-border/60">
                                {facet.options.length > 6 && (
                                    <div className="relative mb-2">
                                        <Search className="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-muted" />
                                        <input
                                            type="text"
                                            placeholder={`Filter ${facet.label.toLowerCase()}...`}
                                            value={searchVal}
                                            onChange={(e) =>
                                                setFacetSearches((prev) => ({
                                                    ...prev,
                                                    [facet.id]: e.target.value,
                                                }))
                                            }
                                            className="w-full bg-bg border border-border rounded-lg pl-7 pr-2 py-1 text-[11px] text-fg focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent"
                                        />
                                    </div>
                                )}

                                <div className="max-h-48 overflow-y-auto space-y-1.5 pr-1 scrollbar-thin">
                                    {filteredOptions.length > 0 ? (
                                        filteredOptions.map((opt) => {
                                            const checked = isOptionSelected(facet.id, opt);
                                            return (
                                                <label
                                                    key={opt}
                                                    className="flex items-center gap-2 cursor-pointer text-xs text-muted hover:text-fg select-none py-0.5 group"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        checked={checked}
                                                        onChange={() =>
                                                            handleToggleFacetOption(facet.id, opt)
                                                        }
                                                        className="rounded border-border text-accent focus:ring-accent w-3.5 h-3.5 bg-bg cursor-pointer accent-cyan-600 dark:accent-cyan-400"
                                                    />
                                                    <span
                                                        className={`truncate transition-colors ${
                                                            checked
                                                                ? "text-accent font-semibold"
                                                                : "group-hover:text-fg"
                                                        }`}
                                                    >
                                                        {opt}
                                                    </span>
                                                </label>
                                            );
                                        })
                                    ) : (
                                        <p className="text-[11px] text-muted py-1 italic">
                                            No options match search
                                        </p>
                                    )}
                                </div>
                            </div>
                        )}
                    </div>
                );
            })}

            {/* Clear All Filters Button */}
            {appliedPills.length > 0 && (
                <button
                    type="button"
                    onClick={clearFilters}
                    className="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-xs font-semibold text-rose-500 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 transition-all"
                >
                    <RotateCcw className="w-3.5 h-3.5" />
                    <span>Clear All Filters</span>
                </button>
            )}
        </div>
    );

    return (
        <AppLayout>
            <Head title={pageTitle}>
                <meta
                    name="description"
                    content={
                        activeCategory?.description ||
                        "Explore laptops, PC components, peripherals, displays, gaming gear, and computer accessories."
                    }
                />
            </Head>

            <div className="min-h-screen bg-bg text-fg">
                {/* ------------------------------------------------------------------ */}
                {/*  Header / Category Banner                                          */}
                {/* ------------------------------------------------------------------ */}
                <div className="relative border-b border-border bg-gradient-to-b from-card/80 to-bg py-8 md:py-10">
                    <div className="container mx-auto px-4 md:px-6">
                        {/* Breadcrumbs */}
                        <nav className="flex items-center gap-2 text-xs md:text-sm text-muted mb-4 flex-wrap">
                            {!activeCategory ? (
                                <span className="text-accent font-semibold">
                                    Shop
                                </span>
                            ) : (
                                <>
                                    <Link
                                        href="/shop"
                                        className="hover:text-accent transition-colors text-muted"
                                    >
                                        Shop
                                    </Link>
                                    <ChevronRight className="w-3.5 h-3.5 opacity-50" />
                                    <Link
                                        href={`/shop?category=${activeCategory.slug}`}
                                        className={`hover:text-accent transition-colors ${
                                            !activeSubCategory ? "text-accent font-semibold" : "text-muted"
                                        }`}
                                    >
                                        {activeCategory.name}
                                    </Link>
                                    {activeSubCategory && (
                                        <>
                                            <ChevronRight className="w-3.5 h-3.5 opacity-50" />
                                            <span className="text-accent font-semibold">
                                                {activeSubCategory.name}
                                            </span>
                                        </>
                                    )}
                                </>
                            )}
                        </nav>

                        {/* Banner Content (Cleaned: AI icon section and top right badge row removed) */}
                        <div className="max-w-3xl">
                            <h1 className="text-2xl sm:text-3xl md:text-4xl font-extrabold font-display tracking-tight text-fg">
                                {activeSubCategory
                                    ? activeSubCategory.name
                                    : activeCategory
                                    ? activeCategory.name
                                    : "ALL CATEGORIES & PRODUCTS"}
                            </h1>
                            <p className="text-sm md:text-base text-muted mt-2 leading-relaxed">
                                {activeSubCategory?.description ||
                                    activeCategory?.description ||
                                    "Browse verified selection of gaming hardware, workstation components, ultrabooks, and everyday peripherals with official warranty."}
                            </p>
                        </div>

                        {/* Horizontal Subcategory Bar (if category has subcategories) */}
                        {currentSubcategories.length > 0 && (
                            <div className="mt-6 pt-5 border-t border-border/60">
                                <div className="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin">
                                    <span className="text-xs font-semibold text-muted uppercase tracking-wider mr-2 shrink-0">
                                        Subcategories:
                                    </span>
                                    <Link
                                        href={`/shop?category=${activeCategory?.slug}`}
                                        className={`px-3.5 py-1.5 rounded-full text-xs font-semibold transition-all shrink-0 border ${
                                            !activeSubCategory
                                                ? "bg-accent text-white dark:text-slate-950 border-accent shadow-sm"
                                                : "bg-card border-border text-muted hover:text-fg hover:border-accent/40"
                                        }`}
                                    >
                                        All {activeCategory?.name}
                                    </Link>
                                    {currentSubcategories.map((sub) => {
                                        const isSelected = activeSubCategory?.slug === sub.slug;
                                        return (
                                            <Link
                                                key={sub.slug}
                                                href={`/shop?category=${activeCategory?.slug}&subcategory=${sub.slug}`}
                                                className={`px-3.5 py-1.5 rounded-full text-xs font-semibold transition-all shrink-0 border flex items-center gap-1.5 ${
                                                    isSelected
                                                        ? "bg-accent text-white dark:text-slate-950 border-accent shadow-sm"
                                                        : "bg-card border-border text-muted hover:text-fg hover:border-accent/50"
                                                }`}
                                            >
                                                <span>{sub.name}</span>
                                                {typeof sub.count === "number" && sub.count > 0 && (
                                                    <span
                                                        className={`text-[10px] px-1.5 py-0.2 rounded-full ${
                                                            isSelected
                                                                ? "bg-black/20 text-white"
                                                                : "bg-muted/20 text-muted"
                                                        }`}
                                                    >
                                                        {sub.count}
                                                    </span>
                                                )}
                                            </Link>
                                        );
                                    })}
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                {/* ------------------------------------------------------------------ */}
                {/*  Main Container: Sidebar + Product Grid                            */}
                {/* ------------------------------------------------------------------ */}
                <div className="container mx-auto px-4 md:px-6 py-8">
                    <div className="flex flex-col lg:flex-row gap-8 items-start">
                        {/* -------------------------------------------------------------- */}
                        {/*  Desktop Filter Sidebar                                        */}
                        {/* -------------------------------------------------------------- */}
                        <aside className="hidden lg:block w-72 shrink-0 sticky top-24 space-y-6">
                            {/* Categories Tree Navigation */}
                            <div className="bg-card rounded-2xl border border-border p-5 shadow-sm">
                                <div className="flex items-center justify-between pb-3 mb-4 border-b border-border">
                                    <h3 className="font-display font-bold text-xs tracking-wide uppercase text-fg">
                                        Categories
                                    </h3>
                                    <Link
                                        href="/shop"
                                        className="text-xs text-accent hover:underline font-semibold"
                                    >
                                        View All
                                    </Link>
                                </div>

                                <div className="space-y-1">
                                    {categories.map((cat) => {
                                        const isCatActive = activeCategory?.slug === cat.slug;
                                        const hasSubs = cat.subcategories && cat.subcategories.length > 0;
                                        const isExpanded = Boolean(expandedCategories[cat.slug]);

                                        return (
                                            <div key={cat.slug} className="rounded-lg overflow-hidden">
                                                <div
                                                    className={`flex items-center justify-between px-3 py-2 text-xs rounded-lg transition-colors cursor-pointer ${
                                                        isCatActive && !activeSubCategory
                                                            ? "bg-accent/10 text-accent font-semibold border-l-2 border-accent"
                                                            : "text-muted hover:bg-muted/10 hover:text-fg"
                                                    }`}
                                                >
                                                    <Link
                                                        href={`/shop?category=${cat.slug}`}
                                                        className="flex items-center gap-2 flex-1"
                                                    >
                                                        {cat.image && (
                                                            <img
                                                                src={cat.image}
                                                                alt={cat.name}
                                                                className="w-4 h-4 rounded-full object-cover shrink-0"
                                                            />
                                                        )}
                                                        <span className="truncate">{cat.name}</span>
                                                    </Link>

                                                    {hasSubs && (
                                                        <button
                                                            type="button"
                                                            onClick={(e) => {
                                                                e.stopPropagation();
                                                                toggleAccordion(cat.slug);
                                                            }}
                                                            className="p-1 text-muted hover:text-fg"
                                                        >
                                                            <ChevronDown
                                                                className={`w-3.5 h-3.5 transition-transform duration-200 ${
                                                                    isExpanded ? "rotate-180" : ""
                                                                }`}
                                                            />
                                                        </button>
                                                    )}
                                                </div>

                                                {/* Subcategories list */}
                                                {hasSubs && isExpanded && (
                                                    <div className="ml-5 pl-2 my-1 border-l border-border/80 space-y-0.5 py-1">
                                                        {cat.subcategories.map((sub) => {
                                                            const isSubActive =
                                                                activeCategory?.slug === cat.slug &&
                                                                activeSubCategory?.slug === sub.slug;

                                                            return (
                                                                <Link
                                                                    key={sub.slug}
                                                                    href={`/shop?category=${cat.slug}&subcategory=${sub.slug}`}
                                                                    className={`flex items-center justify-between px-2 py-1 text-[11px] rounded transition-colors ${
                                                                        isSubActive
                                                                            ? "text-accent font-semibold bg-accent/10"
                                                                            : "text-muted hover:text-fg hover:bg-muted/10"
                                                                    }`}
                                                                >
                                                                    <span className="truncate">{sub.name}</span>
                                                                    {typeof sub.count === "number" && sub.count > 0 && (
                                                                        <span className="text-[10px] text-muted">
                                                                            {sub.count}
                                                                        </span>
                                                                    )}
                                                                </Link>
                                                            );
                                                        })}
                                                    </div>
                                                )}
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>

                            {/* Dynamic Category Specific Filters */}
                            {renderFilterControls(false)}
                        </aside>

                        {/* -------------------------------------------------------------- */}
                        {/*  Right Column: Toolbar & Product Grid                          */}
                        {/* -------------------------------------------------------------- */}
                        <main className="flex-1 w-full space-y-6">
                            {/* Toolbar */}
                            <div className="bg-card rounded-2xl border border-border p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                                {/* Search Form */}
                                <form onSubmit={handleSearchSubmit} className="relative flex-1 max-w-md">
                                    <Search className="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-muted" />
                                    <input
                                        type="text"
                                        placeholder={
                                            activeSubCategory
                                                ? `Search in ${activeSubCategory.name}...`
                                                : activeCategory
                                                ? `Search in ${activeCategory.name}...`
                                                : "Search across all hardware..."
                                        }
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="w-full bg-bg border border-border rounded-xl pl-9 pr-8 py-2 text-xs md:text-sm text-fg placeholder:text-muted focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all"
                                    />
                                    {searchTerm && (
                                        <button
                                            type="button"
                                            onClick={() => {
                                                setSearchTerm("");
                                                applyFilters({ q: "" });
                                            }}
                                            className="absolute right-3 top-1/2 -translate-y-1/2 text-muted hover:text-fg"
                                        >
                                            <X className="w-3.5 h-3.5" />
                                        </button>
                                    )}
                                </form>

                                {/* Controls: Filter Toggle (Mobile), Sort, Layout Switcher */}
                                <div className="flex items-center gap-3 justify-between md:justify-end">
                                    {/* Mobile Filter Button */}
                                    <button
                                        type="button"
                                        onClick={() => setMobileFilterOpen(true)}
                                        className="lg:hidden flex items-center gap-2 px-3 py-2 bg-card border border-border rounded-xl text-xs font-semibold text-fg hover:bg-muted/10"
                                    >
                                        <Filter className="w-3.5 h-3.5 text-accent" />
                                        <span>Filters</span>
                                        {appliedPills.length > 0 && (
                                            <span className="w-4 h-4 rounded-full bg-accent text-white dark:text-slate-950 text-[10px] flex items-center justify-center font-bold">
                                                {appliedPills.length}
                                            </span>
                                        )}
                                    </button>

                                    {/* Sort Dropdown */}
                                    <div className="flex items-center gap-2">
                                        <span className="text-xs text-muted hidden sm:inline">Sort:</span>
                                        <select
                                            value={filters.sort || "featured"}
                                            onChange={handleSortChange}
                                            className="bg-bg border border-border rounded-xl px-3 py-2 text-xs text-fg focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent font-medium"
                                        >
                                            <option value="featured">Featured / Best Matches</option>
                                            <option value="newest">Newest Arrivals</option>
                                            <option value="price_asc">Price: Low to High</option>
                                            <option value="price_desc">Price: High to Low</option>
                                        </select>
                                    </div>

                                    {/* Grid Layout Switcher (Desktop) */}
                                    <div className="hidden sm:flex items-center border border-border rounded-xl overflow-hidden bg-bg">
                                        <button
                                            type="button"
                                            onClick={() => setColumns(3)}
                                            className={`p-2 transition-colors ${
                                                columns === 3 ? "bg-accent text-white dark:text-slate-950 font-bold" : "text-muted hover:text-fg hover:bg-muted/10"
                                            }`}
                                            title="3 Columns Grid"
                                        >
                                            <LayoutGrid className="w-4 h-4" />
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => setColumns(4)}
                                            className={`p-2 transition-colors ${
                                                columns === 4 ? "bg-accent text-white dark:text-slate-950 font-bold" : "text-muted hover:text-fg hover:bg-muted/10"
                                            }`}
                                            title="4 Columns Grid"
                                        >
                                            <Grid3X3 className="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {/* Applied Filter Pills Bar */}
                            {appliedPills.length > 0 && (
                                <div className="flex items-center gap-2 flex-wrap text-xs bg-card p-3 rounded-2xl border border-border">
                                    <span className="text-muted font-medium mr-1">Active Filters:</span>
                                    {appliedPills.map((pill) => (
                                        <span
                                            key={pill.key}
                                            className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-bg border border-border text-fg text-xs font-medium"
                                        >
                                            <span>{pill.label}</span>
                                            <button
                                                type="button"
                                                onClick={pill.onRemove}
                                                className="text-muted hover:text-rose-500 transition-colors"
                                                title="Remove filter"
                                            >
                                                <X className="w-3 h-3" />
                                            </button>
                                        </span>
                                    ))}
                                    <button
                                        type="button"
                                        onClick={clearFilters}
                                        className="text-xs text-accent hover:underline ml-auto font-medium"
                                    >
                                        Clear all
                                    </button>
                                </div>
                            )}

                            {/* Results Count */}
                            <div className="flex items-center justify-between text-xs text-muted px-1">
                                <span>
                                    Showing <strong className="text-fg">{products.length}</strong> {products.length === 1 ? "product" : "products"}
                                    {activeCategory && ` in ${activeCategory.name}`}
                                    {activeSubCategory && ` > ${activeSubCategory.name}`}
                                </span>
                            </div>

                            {/* Product Cards Grid */}
                            {products.length > 0 ? (
                                <div
                                    className={`grid grid-cols-1 sm:grid-cols-2 ${
                                        columns === 4 ? "xl:grid-cols-4" : "xl:grid-cols-3"
                                    } gap-5`}
                                >
                                    {products.map((product) => (
                                        <ProductCard key={product.id} product={product} />
                                    ))}
                                </div>
                            ) : (
                                /* Empty State */
                                <div className="text-center py-20 px-4 rounded-3xl bg-card border border-border my-8">
                                    <div className="w-16 h-16 rounded-full bg-accent/10 text-accent mx-auto flex items-center justify-center mb-4">
                                        <SlidersHorizontal className="w-8 h-8" />
                                    </div>
                                    <h3 className="font-display font-bold text-xl text-fg mb-2">
                                        No products match your filter criteria
                                    </h3>
                                    <p className="text-sm text-muted max-w-md mx-auto mb-6">
                                        Try adjusting your price range, clearing some filters, or explore our full hardware collection.
                                    </p>
                                    <div className="flex items-center justify-center gap-3">
                                        <button
                                            type="button"
                                            onClick={clearFilters}
                                            className="px-5 py-2.5 bg-accent text-white dark:text-slate-950 font-bold rounded-xl text-xs transition-transform active:scale-95 shadow-md"
                                        >
                                            Reset All Filters
                                        </button>
                                        <Link
                                            href="/shop"
                                            className="px-5 py-2.5 bg-bg border border-border text-fg font-semibold rounded-xl text-xs hover:bg-muted/10 transition-colors"
                                        >
                                            View All Products
                                        </Link>
                                    </div>
                                </div>
                            )}
                        </main>
                    </div>
                </div>

                {/* ------------------------------------------------------------------ */}
                {/*  Mobile Filter Drawer                                              */}
                {/* ------------------------------------------------------------------ */}
                {mobileFilterOpen && (
                    <div className="fixed inset-0 z-50 lg:hidden flex">
                        {/* Backdrop */}
                        <div
                            className="fixed inset-0 bg-black/70 backdrop-blur-sm transition-opacity"
                            onClick={() => setMobileFilterOpen(false)}
                        />

                        {/* Drawer content */}
                        <div className="relative ml-auto w-full max-w-xs bg-bg h-full p-6 overflow-y-auto shadow-2xl flex flex-col justify-between border-l border-border z-10">
                            <div>
                                <div className="flex items-center justify-between pb-4 border-b border-border mb-6">
                                    <div className="flex items-center gap-2">
                                        <Filter className="w-4 h-4 text-accent" />
                                        <h2 className="font-display font-bold text-base text-fg">Filter Products</h2>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={() => setMobileFilterOpen(false)}
                                        className="p-1 rounded-lg text-muted hover:text-fg"
                                    >
                                        <X className="w-5 h-5" />
                                    </button>
                                </div>

                                {renderFilterControls(true)}
                            </div>

                            <div className="pt-6 border-t border-border mt-6 space-y-2">
                                <button
                                    type="button"
                                    onClick={() => setMobileFilterOpen(false)}
                                    className="w-full py-2.5 bg-accent text-white dark:text-slate-950 font-bold rounded-xl text-xs shadow-md"
                                >
                                    Show {products.length} Products
                                </button>
                                <button
                                    type="button"
                                    onClick={() => {
                                        clearFilters();
                                        setMobileFilterOpen(false);
                                    }}
                                    className="w-full py-2 bg-card border border-border text-muted hover:text-fg rounded-xl text-xs"
                                >
                                    Reset Filters
                                </button>
                            </div>
                        </div>
                    </div>
                )}

                {/* ------------------------------------------------------------------ */}
                {/*  Trust & Value Proposition Bar                                     */}
                {/* ------------------------------------------------------------------ */}
                <section className="border-t border-border bg-card py-12 mt-12">
                    <div className="container mx-auto px-4 md:px-6">
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div className="flex items-center gap-4 p-4 rounded-2xl bg-bg border border-border">
                                <div className="w-12 h-12 rounded-xl bg-accent/10 text-accent flex items-center justify-center shrink-0">
                                    <ShieldCheck className="w-6 h-6" />
                                </div>
                                <div>
                                    <h4 className="font-semibold text-sm text-fg">100% Genuine Brands</h4>
                                    <p className="text-xs text-muted mt-0.5">Direct manufacturer warranty</p>
                                </div>
                            </div>
                            <div className="flex items-center gap-4 p-4 rounded-2xl bg-bg border border-border">
                                <div className="w-12 h-12 rounded-xl bg-accent/10 text-accent flex items-center justify-center shrink-0">
                                    <Truck className="w-6 h-6" />
                                </div>
                                <div>
                                    <h4 className="font-semibold text-sm text-fg">Islandwide Delivery</h4>
                                    <p className="text-xs text-muted mt-0.5">Fast & secure shipping</p>
                                </div>
                            </div>
                            <div className="flex items-center gap-4 p-4 rounded-2xl bg-bg border border-border">
                                <div className="w-12 h-12 rounded-xl bg-accent/10 text-accent flex items-center justify-center shrink-0">
                                    <Wrench className="w-6 h-6" />
                                </div>
                                <div>
                                    <h4 className="font-semibold text-sm text-fg">Custom PC Assembly</h4>
                                    <p className="text-xs text-muted mt-0.5">Free testing & stress checks</p>
                                </div>
                            </div>
                            <div className="flex items-center gap-4 p-4 rounded-2xl bg-bg border border-border">
                                <div className="w-12 h-12 rounded-xl bg-accent/10 text-accent flex items-center justify-center shrink-0">
                                    <RotateCcw className="w-6 h-6" />
                                </div>
                                <div>
                                    <h4 className="font-semibold text-sm text-fg">7-Day Easy Returns</h4>
                                    <p className="text-xs text-muted mt-0.5">Hassle-free replacement policy</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </AppLayout>
    );
}
