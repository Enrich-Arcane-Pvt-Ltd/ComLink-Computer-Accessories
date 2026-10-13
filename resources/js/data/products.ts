export interface Product {
    id: string;
    name: string;
    price: number;
    originalPrice?: number;
    images: string[];
    category: string;
    rating: number;
    reviews: number;
    isNew?: boolean;
    isSale?: boolean;
    colors: string[];
    sizes: string[];
    description: string;
    brand: string;
}

export const products: Product[] = [
    {
        id: "p1",
        name: "Essential Oversized Tee",
        price: 35.0,
        images: [
            "https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?auto=format&fit=crop&q=80&w=800",
            "https://images.unsplash.com/photo-1529374255404-311a2a4f1fd9?auto=format&fit=crop&q=80&w=800",
        ],

        category: "T-Shirts",
        rating: 4.8,
        reviews: 124,
        isNew: true,
        colors: ["#000000", "#ffffff", "#94a3b8"],
        sizes: ["S", "M", "L", "XL"],
        description:
            "Crafted from heavyweight premium cotton, this oversized tee offers a relaxed, streetwear-inspired silhouette. Perfect for everyday wear with a structured drape.",
        brand: "Milana Basics",
    },
    {
        id: "p2",
        name: "Vintage Wash Straight Denim",
        price: 89.0,
        originalPrice: 120.0,
        images: [
            "https://images.unsplash.com/photo-1542272604-787c3835535d?auto=format&fit=crop&q=80&w=800",
            "https://images.unsplash.com/photo-1475403614135-5f1aa0eb5015?auto=format&fit=crop&q=80&w=800",
        ],

        category: "Denim",
        rating: 4.5,
        reviews: 89,
        isSale: true,
        colors: ["#3b82f6", "#1e3a8a"],
        sizes: ["28", "30", "32", "34", "36"],
        description:
            "Classic straight-leg denim with a vintage wash. Features subtle distressing and premium hardware for a timeless look.",
        brand: "Milana Denim",
    },
    {
        id: "p3",
        name: "Urban Tech Jacket",
        price: 145.0,
        images: [
            "https://images.unsplash.com/photo-1559551409-dadc959f76b8?auto=format&fit=crop&q=80&w=800",
            "https://images.unsplash.com/photo-1520975954732-57dd22299614?auto=format&fit=crop&q=80&w=800",
        ],

        category: "Jackets",
        rating: 4.9,
        reviews: 56,
        colors: ["#0f172a", "#475569"],
        sizes: ["M", "L", "XL"],
        description:
            "Water-resistant tech jacket with multiple utility pockets. Lightweight yet warm, designed for the modern urban explorer.",
        brand: "Milana Tech",
    },
    {
        id: "p4",
        name: "Classic Boxy Hoodie",
        price: 65.0,
        images: [
            "https://images.unsplash.com/photo-1556821840-3a63f95609a7?auto=format&fit=crop&q=80&w=800",
            "https://images.unsplash.com/photo-1578768079052-aa76e52ff62e?auto=format&fit=crop&q=80&w=800",
        ],

        category: "Hoodies",
        rating: 4.7,
        reviews: 210,
        isNew: true,
        colors: ["#000000", "#e2e8f0", "#b91c1c"],
        sizes: ["S", "M", "L", "XL", "XXL"],
        description:
            "Ultra-soft fleece interior with a boxy, cropped fit. The perfect layering piece for any season.",
        brand: "Milana Basics",
    },
    {
        id: "p5",
        name: "Women's High-Rise Wide Leg",
        price: 95.0,
        images: [
            "https://images.unsplash.com/photo-1541099649105-f69ad21f3246?auto=format&fit=crop&q=80&w=800",
            "https://images.unsplash.com/photo-1582552938357-32b906df40cb?auto=format&fit=crop&q=80&w=800",
        ],

        category: "Women",
        rating: 4.6,
        reviews: 78,
        colors: ["#60a5fa", "#000000"],
        sizes: ["24", "26", "28", "30", "32"],
        description:
            "Flattering high-rise fit with a dramatic wide leg. Made from rigid denim that molds to your body over time.",
        brand: "Milana Denim",
    },
    {
        id: "p6",
        name: "Textured Knit Polo",
        price: 55.0,
        originalPrice: 75.0,
        images: [
            "https://images.unsplash.com/photo-1617114919297-3c8ddb01f599?auto=format&fit=crop&q=80&w=800",
            "https://images.unsplash.com/photo-1617127365659-c47fa864d8bc?auto=format&fit=crop&q=80&w=800",
        ],

        category: "Shirts",
        rating: 4.4,
        reviews: 42,
        isSale: true,
        colors: ["#fef3c7", "#d1d5db"],
        sizes: ["M", "L", "XL"],
        description:
            "Elevated polo shirt featuring a textured knit pattern. Breathable and stylish for smart-casual occasions.",
        brand: "Milana Studio",
    },
    {
        id: "p7",
        name: "Graphic Street Tee",
        price: 40.0,
        images: [
            "https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?auto=format&fit=crop&q=80&w=800",
            "https://images.unsplash.com/photo-1527010154944-f2241763d806?auto=format&fit=crop&q=80&w=800",
        ],

        category: "T-Shirts",
        rating: 4.8,
        reviews: 156,
        colors: ["#ffffff", "#000000"],
        sizes: ["S", "M", "L", "XL"],
        description:
            "Bold graphic print on our signature mid-weight cotton tee. A standout piece for any streetwear collection.",
        brand: "Milana X",
    },
    {
        id: "p8",
        name: "Slim Fit Black Denim",
        price: 85.0,
        images: [
            "https://images.unsplash.com/photo-1541533848490-bc8115cd6522?auto=format&fit=crop&q=80&w=800",
            "https://images.unsplash.com/photo-1489987707023-af8233665369?auto=format&fit=crop&q=80&w=800",
        ],

        category: "Denim",
        rating: 4.7,
        reviews: 312,
        colors: ["#000000"],
        sizes: ["28", "30", "32", "34", "36"],
        description:
            "The ultimate everyday black jean. Slim but not skinny, with just enough stretch for all-day comfort.",
        brand: "Milana Denim",
    },
];

export const categories = [
    {
        name: "T-Shirts",
        count: 124,
        image: "https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&q=80&w=400",
    },
    {
        name: "Denim",
        count: 86,
        image: "https://images.unsplash.com/photo-1542272604-787c3835535d?auto=format&fit=crop&q=80&w=400",
    },
    {
        name: "Women",
        count: 28,
        image: "https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&q=80&w=400",
    },
    {
        name: "Accessories",
        count: 45,
        image: "https://images.unsplash.com/photo-1541099649105-f69ad21f3246?auto=format&fit=crop&q=80&w=400",
    },
    // {
    //     name: "Jackets",
    //     count: 32,
    //     image: "https://images.unsplash.com/photo-1559551409-dadc959f76b8?auto=format&fit=crop&q=80&w=400",
    // },
    // {
    //     name: "Accessories",
    //     count: 54,
    //     image: "https://images.unsplash.com/photo-1523206489230-c012c64b2b48?auto=format&fit=crop&q=80&w=400",
    // },
];
