import AppLayout from "@/Layouts/AppLayout";
import { cn } from "@/utils/cn";
import { Head } from "@inertiajs/react";
import { motion } from "framer-motion";
import {
    Calendar,
    CheckCircle2,
    Clock,
    Package,
    ShieldCheck,
    Truck,
} from "lucide-react";
import React from "react";

interface ShippingData {
    title: string;
    last_updated: string;
    content: string;
}

interface ShippingDeliveryProps {
    auth: any;
    shipping: ShippingData;
}

/* -------------------------------------------------------------------------- */
/*  Vector Logistics & Delivery Graphic Components (Reference Image Style)    */
/* -------------------------------------------------------------------------- */

// 1. Delivery Cargo Truck (Yellow Cabin + Blue Cargo Box)
function DeliveryTruckSVG({ className }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 120 70"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            className={className}
        >
            {/* Cargo Box */}
            <rect
                x="5"
                y="10"
                width="70"
                height="42"
                rx="3"
                fill="#0284c7"
                className="dark:fill-cyan-600"
            />
            <path
                d="M 5 24 H 75 M 5 38 H 75 M 28 10 V 52 M 52 10 V 52"
                stroke="white"
                strokeOpacity="0.25"
                strokeWidth="1.5"
            />
            {/* Cabin */}
            <path
                d="M 75 22 H 98 L 112 36 V 52 H 75 V 22 Z"
                fill="#eab308"
                className="dark:fill-amber-400"
            />
            {/* Windshield */}
            <path
                d="M 80 26 H 96 L 107 36 H 80 V 26 Z"
                fill="#38bdf8"
                fillOpacity="0.85"
            />
            {/* Bumper & Headlight */}
            <rect x="108" y="44" width="5" height="5" rx="1" fill="#fef08a" />
            <rect x="75" y="49" width="37" height="4" fill="#334155" />
            {/* Wheels */}
            <circle cx="28" cy="52" r="9" fill="#1e293b" />
            <circle cx="28" cy="52" r="4" fill="#94a3b8" />
            <circle cx="94" cy="52" r="9" fill="#1e293b" />
            <circle cx="94" cy="52" r="4" fill="#94a3b8" />
        </svg>
    );
}

// 2. Cargo Airplane (Translucent vector silhouette)
function CargoAirplaneSVG({ className }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 100 80"
            fill="currentColor"
            xmlns="http://www.w3.org/2000/svg"
            className={className}
        >
            <path d="M 5 40 L 40 38 L 48 8 H 56 L 50 38 L 84 39 L 90 28 H 96 L 93 42 L 96 56 H 90 L 84 45 L 50 46 L 56 76 H 48 L 40 46 L 5 44 C 1 44 1 40 5 40 Z" />
        </svg>
    );
}

// 3. Cargo Ship / Freight Vessel
function CargoShipSVG({ className }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 120 60"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            className={className}
        >
            {/* Hull */}
            <path
                d="M 8 36 L 22 52 H 100 L 114 36 H 8 Z"
                fill="#0369a1"
                className="dark:fill-cyan-700"
            />
            {/* Red Stripe waterline */}
            <path d="M 12 44 L 22 52 H 100 L 110 44 H 12 Z" fill="#ef4444" />
            {/* Containers on deck */}
            <rect x="22" y="20" width="16" height="15" fill="#f59e0b" rx="1" />
            <rect x="40" y="20" width="16" height="15" fill="#0284c7" rx="1" />
            <rect x="58" y="20" width="16" height="15" fill="#10b981" rx="1" />
            <rect x="31" y="6" width="16" height="14" fill="#ef4444" rx="1" />
            <rect x="49" y="6" width="16" height="14" fill="#06b6d4" rx="1" />
            {/* Bridge / Control tower */}
            <path
                d="M 78 12 H 94 V 36 H 78 Z"
                fill="#e2e8f0"
                className="dark:fill-slate-300"
            />
            <rect x="80" y="16" width="12" height="4" fill="#0284c7" />
            {/* Mast */}
            <line x1="86" y1="5" x2="86" y2="12" stroke="#64748b" strokeWidth="2" />
        </svg>
    );
}

// 4. Forklift lifting pallet
function ForkliftSVG({ className }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 110 75"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            className={className}
        >
            {/* Body */}
            <path
                d="M 10 48 H 55 V 32 H 30 L 10 48 Z"
                fill="#ef4444"
                className="dark:fill-rose-500"
            />
            {/* Cabin roll cage */}
            <path
                d="M 28 32 V 12 H 46 V 32"
                stroke="#334155"
                strokeWidth="3"
                fill="none"
            />
            {/* Seat & steering */}
            <line
                x1="38"
                y1="28"
                x2="33"
                y2="20"
                stroke="#475569"
                strokeWidth="2.5"
            />
            <circle cx="32" cy="19" r="3.5" fill="#64748b" />
            {/* Mast */}
            <rect x="58" y="6" width="4" height="48" fill="#475569" />
            <rect x="65" y="6" width="4" height="48" fill="#475569" />
            {/* Fork */}
            <path d="M 60 48 H 96 V 52 H 60 Z" fill="#1e293b" />
            {/* Box on fork */}
            <rect x="68" y="24" width="24" height="24" rx="2" fill="#d97706" />
            <line
                x1="68"
                y1="36"
                x2="92"
                y2="36"
                stroke="#b45309"
                strokeWidth="2"
            />
            <rect
                x="74"
                y="32"
                width="12"
                height="6"
                fill="#fef3c7"
                opacity="0.8"
            />
            {/* Wheels */}
            <circle cx="22" cy="54" r="10" fill="#1e293b" />
            <circle cx="22" cy="54" r="4.5" fill="#94a3b8" />
            <circle cx="50" cy="54" r="10" fill="#1e293b" />
            <circle cx="50" cy="54" r="4.5" fill="#94a3b8" />
        </svg>
    );
}

// 5. Courier Delivery Scooter
function DeliveryScooterSVG({ className }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 100 70"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            className={className}
        >
            {/* Wheels */}
            <circle cx="22" cy="52" r="10" fill="#1e293b" />
            <circle cx="22" cy="52" r="4" fill="#94a3b8" />
            <circle cx="80" cy="52" r="10" fill="#1e293b" />
            <circle cx="80" cy="52" r="4" fill="#94a3b8" />
            {/* Chassis */}
            <path
                d="M 22 52 L 40 46 H 62 L 72 26 L 80 52"
                stroke="#0284c7"
                strokeWidth="4"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
            <path
                d="M 38 48 H 60"
                stroke="#334155"
                strokeWidth="5"
                strokeLinecap="round"
            />
            {/* Handlebars */}
            <path
                d="M 68 22 L 72 26 L 68 30"
                stroke="#475569"
                strokeWidth="3"
                strokeLinecap="round"
            />
            {/* Delivery Box at back */}
            <rect x="14" y="24" width="22" height="20" rx="3" fill="#06b6d4" />
            <rect
                x="18"
                y="28"
                width="14"
                height="12"
                rx="1"
                fill="#ecfeff"
                opacity="0.6"
            />
        </svg>
    );
}

// 6. Stack of Shipping Boxes with Tape & Fragile Stamp
function ShippingBoxesSVG({ className }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 90 80"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            className={className}
        >
            {/* Bottom large box */}
            <rect x="4" y="38" width="82" height="36" rx="2" fill="#d97706" />
            <rect
                x="4"
                y="38"
                width="82"
                height="36"
                rx="2"
                stroke="#b45309"
                strokeWidth="1.5"
            />
            {/* Tape */}
            <rect
                x="38"
                y="38"
                width="14"
                height="36"
                fill="#fef3c7"
                opacity="0.75"
            />
            {/* Fragile stamp */}
            <rect
                x="12"
                y="46"
                width="18"
                height="10"
                rx="1"
                fill="#ef4444"
                opacity="0.8"
            />
            <path
                d="M 18 49 L 21 54 M 21 49 L 18 54"
                stroke="white"
                strokeWidth="1.2"
            />

            {/* Top box */}
            <rect x="20" y="10" width="50" height="28" rx="2" fill="#f59e0b" />
            <rect
                x="20"
                y="10"
                width="50"
                height="28"
                rx="2"
                stroke="#d97706"
                strokeWidth="1.5"
            />
            {/* Top Box Tape */}
            <rect
                x="40"
                y="10"
                width="10"
                height="28"
                fill="#fef3c7"
                opacity="0.75"
            />
            <rect
                x="26"
                y="17"
                width="10"
                height="8"
                rx="1"
                fill="#0284c7"
                opacity="0.8"
            />
        </svg>
    );
}

// 7. Warehouse Logistics Depot
function WarehouseDepotSVG({ className }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 110 80"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            className={className}
        >
            {/* Roof */}
            <polygon points="55,8 10,28 100,28" fill="#334155" />
            {/* Main Building */}
            <rect x="16" y="28" width="78" height="48" fill="#1e293b" />
            {/* Shutter Doors */}
            <rect x="24" y="38" width="28" height="38" fill="#475569" rx="1" />
            <line
                x1="24"
                y1="46"
                x2="52"
                y2="46"
                stroke="#334155"
                strokeWidth="1.5"
            />
            <line
                x1="24"
                y1="54"
                x2="52"
                y2="54"
                stroke="#334155"
                strokeWidth="1.5"
            />
            <line
                x1="24"
                y1="62"
                x2="52"
                y2="62"
                stroke="#334155"
                strokeWidth="1.5"
            />
            <line
                x1="24"
                y1="70"
                x2="52"
                y2="70"
                stroke="#334155"
                strokeWidth="1.5"
            />
            {/* Bay 2 with boxes inside */}
            <rect x="58" y="38" width="28" height="38" fill="#0f172a" rx="1" />
            <rect x="62" y="56" width="12" height="12" fill="#d97706" />
            <rect x="72" y="52" width="10" height="16" fill="#f59e0b" />
            <rect x="64" y="46" width="10" height="10" fill="#b45309" />
        </svg>
    );
}

// 8. Doorstep House Delivery
function DoorstepDeliverySVG({ className }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 100 80"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            className={className}
        >
            {/* Roof */}
            <polygon points="50,10 12,38 88,38" fill="#ea580c" />
            <rect x="62" y="16" width="8" height="16" fill="#78350f" />
            {/* House body */}
            <rect x="22" y="38" width="56" height="36" fill="#0284c7" />
            {/* Door */}
            <rect x="42" y="48" width="16" height="26" fill="#0f172a" />
            {/* Window */}
            <rect x="28" y="46" width="10" height="10" fill="#fef08a" />
            <line
                x1="33"
                y1="46"
                x2="33"
                y2="56"
                stroke="#0284c7"
                strokeWidth="1"
            />
            <line
                x1="28"
                y1="51"
                x2="38"
                y2="51"
                stroke="#0284c7"
                strokeWidth="1"
            />
            {/* Parcel delivered at doorstep */}
            <rect x="62" y="58" width="18" height="16" rx="1" fill="#d97706" />
            <rect
                x="68"
                y="58"
                width="6"
                height="16"
                fill="#fef3c7"
                opacity="0.8"
            />
        </svg>
    );
}

/* -------------------------------------------------------------------------- */
/*  Main Shipping & Delivery Page Component                                   */
/* -------------------------------------------------------------------------- */

export default function ShippingDelivery({
    auth,
    shipping,
}: ShippingDeliveryProps) {
    return (
        <AppLayout>
            <Head title={shipping?.title || "Shipping & Delivery Policy"} />

            <div
                className="relative overflow-hidden min-h-screen bg-gradient-to-b from-[#eaf4ff] via-[#f1f7ff] to-[#e9f3fe] dark:from-[#060f1c] dark:via-[#09182d] dark:to-[#050b15] text-slate-800 dark:text-slate-200 transition-colors duration-300 py-16 md:py-24"
                style={{ contain: "paint" }}
            >
                {/* ========================================================================= */}
                {/* 1. CREATIVE LOGISTICS & SHIPPING NETWORK VECTOR BACKGROUND                */}
                {/* ========================================================================= */}
                <div
                    className="pointer-events-none absolute inset-0 z-0 overflow-hidden select-none"
                    style={{ contain: "paint" }}
                >
                    {/* Atmospheric Glow Blurs */}
                    <div className="absolute left-1/2 top-1/4 -translate-x-1/2 -translate-y-1/2 w-[900px] max-w-full h-[550px] bg-gradient-to-tr from-cyan-400/20 via-sky-300/25 to-blue-400/15 dark:from-cyan-500/12 dark:via-blue-600/10 dark:to-cyan-400/10 blur-[140px] rounded-full" />
                    <div className="absolute -top-20 -left-20 w-[450px] h-[450px] bg-cyan-400/20 dark:bg-cyan-500/10 blur-[130px] rounded-full" />
                    <div className="absolute -bottom-24 -right-24 w-[480px] h-[480px] bg-blue-500/20 dark:bg-blue-600/10 blur-[130px] rounded-full" />

                    {/* SVG Global Supply Chain Network Lines & Hubs */}
                    <svg
                        className="w-full h-full object-cover opacity-60 dark:opacity-40"
                        viewBox="0 0 1440 900"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        preserveAspectRatio="xMidYMid slice"
                    >
                        {/* World Map Ambient Continents Silhouette */}
                        <path
                            d="M 120 180 C 180 120 280 140 320 220 C 350 280 300 360 220 380 C 150 400 100 340 90 260 Z"
                            fill="currentColor"
                            className="text-cyan-500/10 dark:text-cyan-400/5"
                        />
                        <path
                            d="M 220 440 C 260 420 310 460 300 560 C 290 640 240 700 200 720 C 170 660 180 520 220 440 Z"
                            fill="currentColor"
                            className="text-cyan-500/10 dark:text-cyan-400/5"
                        />
                        <path
                            d="M 680 140 C 760 120 880 150 920 230 C 900 320 800 350 720 320 C 650 290 620 200 680 140 Z"
                            fill="currentColor"
                            className="text-cyan-500/10 dark:text-cyan-400/5"
                        />
                        <path
                            d="M 660 360 C 740 340 800 420 810 520 C 800 620 740 720 680 700 C 620 650 600 450 660 360 Z"
                            fill="currentColor"
                            className="text-cyan-500/10 dark:text-cyan-400/5"
                        />
                        <path
                            d="M 940 180 C 1080 140 1280 200 1340 320 C 1320 440 1150 460 1040 400 C 960 350 900 240 940 180 Z"
                            fill="currentColor"
                            className="text-cyan-500/10 dark:text-cyan-400/5"
                        />
                        <path
                            d="M 1120 540 C 1220 520 1320 580 1300 680 C 1260 740 1140 750 1080 680 C 1050 620 1080 560 1120 540 Z"
                            fill="currentColor"
                            className="text-cyan-500/10 dark:text-cyan-400/5"
                        />

                        {/* Dashed Connecting Routes */}
                        <path
                            d="M 180 210 Q 380 120 640 160 T 1080 140"
                            stroke="#0284c7"
                            strokeWidth="2"
                            strokeDasharray="6 6"
                            className="dark:stroke-cyan-500/60"
                        />
                        <path
                            d="M 240 500 Q 520 440 740 520 T 1180 500"
                            stroke="#0284c7"
                            strokeWidth="2"
                            strokeDasharray="6 6"
                            className="dark:stroke-cyan-500/60"
                        />
                        <path
                            d="M 180 210 Q 150 420 240 500"
                            stroke="#0ea5e9"
                            strokeWidth="1.8"
                            strokeDasharray="5 5"
                            className="dark:stroke-cyan-400/50"
                        />
                        <path
                            d="M 640 160 Q 690 340 740 520"
                            stroke="#0ea5e9"
                            strokeWidth="1.8"
                            strokeDasharray="5 5"
                            className="dark:stroke-cyan-400/50"
                        />
                        <path
                            d="M 1080 140 Q 1200 320 1180 500"
                            stroke="#0ea5e9"
                            strokeWidth="1.8"
                            strokeDasharray="5 5"
                            className="dark:stroke-cyan-400/50"
                        />
                        <path
                            d="M 740 520 Q 940 720 1120 780"
                            stroke="#06b6d4"
                            strokeWidth="2"
                            strokeDasharray="6 6"
                            className="dark:stroke-cyan-400/50"
                        />

                        {/* Pulsing Waypoint Node Hubs */}
                        <g transform="translate(180, 210)">
                            <circle
                                cx="0"
                                cy="0"
                                r="6"
                                fill="#0284c7"
                                className="dark:fill-cyan-400"
                            />
                            <circle
                                cx="0"
                                cy="0"
                                r="12"
                                stroke="#38bdf8"
                                strokeWidth="1.5"
                                opacity="0.6"
                            />
                        </g>
                        <g transform="translate(640, 160)">
                            <circle
                                cx="0"
                                cy="0"
                                r="6"
                                fill="#0284c7"
                                className="dark:fill-cyan-400"
                            />
                            <circle
                                cx="0"
                                cy="0"
                                r="12"
                                stroke="#38bdf8"
                                strokeWidth="1.5"
                                opacity="0.6"
                            />
                        </g>
                        <g transform="translate(1080, 140)">
                            <circle
                                cx="0"
                                cy="0"
                                r="6"
                                fill="#0284c7"
                                className="dark:fill-cyan-400"
                            />
                            <circle
                                cx="0"
                                cy="0"
                                r="12"
                                stroke="#38bdf8"
                                strokeWidth="1.5"
                                opacity="0.6"
                            />
                        </g>
                        <g transform="translate(740, 520)">
                            <circle
                                cx="0"
                                cy="0"
                                r="8"
                                fill="#06b6d4"
                                className="dark:fill-cyan-300"
                            />
                            <circle
                                cx="0"
                                cy="0"
                                r="16"
                                stroke="#38bdf8"
                                strokeWidth="2"
                                opacity="0.8"
                            />
                        </g>
                        <g transform="translate(1180, 500)">
                            <circle
                                cx="0"
                                cy="0"
                                r="6"
                                fill="#0284c7"
                                className="dark:fill-cyan-400"
                            />
                            <circle
                                cx="0"
                                cy="0"
                                r="12"
                                stroke="#38bdf8"
                                strokeWidth="1.5"
                                opacity="0.6"
                            />
                        </g>
                    </svg>

                    {/* --- ANIMATED LOGISTICS VEHICLES & ASSETS (Floating smoothly) --- */}

                    {/* 1. Delivery Cargo Truck (Driving top-right) */}
                    <motion.div
                        initial={{ x: 0 }}
                        animate={{ x: [-18, 18, -18] }}
                        transition={{
                            duration: 7.5,
                            repeat: Infinity,
                            repeatType: "mirror",
                            ease: "easeInOut",
                        }}
                        className="absolute top-[8%] right-[4%] w-[130px] sm:w-[155px] drop-shadow-lg"
                    >
                        <DeliveryTruckSVG className="w-full h-auto" />
                    </motion.div>

                    {/* 2. Cargo Airplane (Soaring gracefully top-left) */}
                    <motion.div
                        initial={{ x: 0, y: 0, rotate: -8 }}
                        animate={{
                            x: [-22, 22, -22],
                            y: [-12, 12, -12],
                            rotate: [-10, -6, -10],
                        }}
                        transition={{
                            duration: 8.5,
                            repeat: Infinity,
                            repeatType: "mirror",
                            ease: "easeInOut",
                        }}
                        className="absolute top-[10%] left-[5%] w-[85px] sm:w-[105px] text-cyan-600/70 dark:text-cyan-400/80 drop-shadow-md"
                    >
                        <CargoAirplaneSVG className="w-full h-auto" />
                    </motion.div>

                    {/* 3. Cargo Ship (Bobbing gently bottom-left) */}
                    <motion.div
                        initial={{ y: 0, rotate: 0 }}
                        animate={{
                            y: [-6, 6, -6],
                            rotate: [-2, 2, -2],
                        }}
                        transition={{
                            duration: 6.5,
                            repeat: Infinity,
                            repeatType: "mirror",
                            ease: "easeInOut",
                        }}
                        className="absolute bottom-[10%] left-[4%] w-[130px] sm:w-[150px] drop-shadow-lg"
                    >
                        <CargoShipSVG className="w-full h-auto" />
                    </motion.div>

                    {/* 4. Forklift (Lifting crate bottom-right) */}
                    <motion.div
                        initial={{ y: 0 }}
                        animate={{ y: [0, -8, 0] }}
                        transition={{
                            duration: 5.2,
                            repeat: Infinity,
                            repeatType: "mirror",
                            ease: "easeInOut",
                        }}
                        className="absolute bottom-[12%] right-[5%] w-[120px] sm:w-[140px] drop-shadow-lg"
                    >
                        <ForkliftSVG className="w-full h-auto" />
                    </motion.div>

                    {/* 5. Courier Scooter (Floating mid-left) */}
                    <motion.div
                        initial={{ x: 0 }}
                        animate={{ x: [-14, 14, -14] }}
                        transition={{
                            duration: 6.2,
                            repeat: Infinity,
                            repeatType: "mirror",
                            ease: "easeInOut",
                        }}
                        className="absolute top-[45%] left-[3%] w-[95px] sm:w-[115px] drop-shadow-md"
                    >
                        <DeliveryScooterSVG className="w-full h-auto" />
                    </motion.div>

                    {/* 6. Stack of Shipping Boxes (Mid-right) */}
                    <motion.div
                        initial={{ y: 0 }}
                        animate={{ y: [-7, 7, -7] }}
                        transition={{
                            duration: 6.8,
                            repeat: Infinity,
                            repeatType: "mirror",
                            ease: "easeInOut",
                            delay: 0.8,
                        }}
                        className="absolute top-[48%] right-[4%] w-[85px] sm:w-[100px] drop-shadow-md"
                    >
                        <ShippingBoxesSVG className="w-full h-auto" />
                    </motion.div>

                    {/* 7. Warehouse Depot (Top-center subtle backdrop) */}
                    <motion.div
                        initial={{ opacity: 0.4 }}
                        animate={{ opacity: [0.35, 0.55, 0.35] }}
                        transition={{
                            duration: 7,
                            repeat: Infinity,
                            repeatType: "mirror",
                            ease: "easeInOut",
                        }}
                        className="absolute top-[2%] left-[44%] -translate-x-1/2 w-[100px] sm:w-[125px] opacity-45 dark:opacity-30 blur-[0.5px]"
                    >
                        <WarehouseDepotSVG className="w-full h-auto" />
                    </motion.div>

                    {/* 8. Doorstep Delivery House (Bottom-center subtle backdrop) */}
                    <motion.div
                        initial={{ opacity: 0.4 }}
                        animate={{ opacity: [0.35, 0.55, 0.35] }}
                        transition={{
                            duration: 7.2,
                            repeat: Infinity,
                            repeatType: "mirror",
                            ease: "easeInOut",
                            delay: 1.2,
                        }}
                        className="absolute bottom-[2%] left-[46%] -translate-x-1/2 w-[90px] sm:w-[115px] opacity-45 dark:opacity-30 blur-[0.5px]"
                    >
                        <DoorstepDeliverySVG className="w-full h-auto" />
                    </motion.div>
                </div>

                {/* ========================================================================= */}
                {/* 2. SHIPPING & DELIVERY POLICY CONTENT CARD (Crisp & Readable)             */}
                {/* ========================================================================= */}
                <div className="container relative z-10 mx-auto px-4 md:px-6 max-w-4xl">
                    <div className="rounded-3xl border border-slate-200/90 dark:border-white/10 bg-white/92 dark:bg-[#0c1424]/90 backdrop-blur-xl p-8 sm:p-12 md:p-16 shadow-2xl shadow-cyan-950/5 dark:shadow-black/40">
                        {/* Header Section */}
                        <div className="text-center mb-12 border-b border-slate-200/80 dark:border-white/10 pb-8">
                            <span className="inline-flex items-center gap-1.5 text-xs font-bold tracking-widest text-cyan-600 dark:text-cyan-400 uppercase mb-3">
                                <Truck className="w-4 h-4" />
                                ComLink Official Dispatch Policy
                            </span>
                            <h1 className="font-display text-4xl sm:text-5xl font-bold text-slate-900 dark:text-white mb-4 uppercase tracking-tight">
                                {shipping?.title || "Shipping & Delivery Policy"}
                            </h1>
                            {shipping?.last_updated && (
                                <p className="text-slate-500 dark:text-slate-400 text-sm flex items-center justify-center gap-2">
                                    <Clock className="w-4 h-4 text-cyan-500" />
                                    <span>Last Updated: {shipping.last_updated}</span>
                                </p>
                            )}

                            {/* Trust Pillars Grid */}
                            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-8 pt-6 border-t border-slate-100 dark:border-white/5 text-left">
                                <div className="flex items-center gap-3 p-3.5 rounded-2xl bg-cyan-50/70 dark:bg-cyan-500/10 border border-cyan-100 dark:border-cyan-500/20">
                                    <div className="w-9 h-9 rounded-xl bg-cyan-500/15 flex items-center justify-center text-cyan-600 dark:text-cyan-400 flex-shrink-0">
                                        <Truck className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p className="font-bold text-xs uppercase tracking-wide text-slate-900 dark:text-white">
                                            Island-Wide Courier
                                        </p>
                                        <p className="text-[11px] text-slate-500 dark:text-slate-400">
                                            Fast door-to-door delivery
                                        </p>
                                    </div>
                                </div>

                                <div className="flex items-center gap-3 p-3.5 rounded-2xl bg-cyan-50/70 dark:bg-cyan-500/10 border border-cyan-100 dark:border-cyan-500/20">
                                    <div className="w-9 h-9 rounded-xl bg-cyan-500/15 flex items-center justify-center text-cyan-600 dark:text-cyan-400 flex-shrink-0">
                                        <ShieldCheck className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p className="font-bold text-xs uppercase tracking-wide text-slate-900 dark:text-white">
                                            Safe Packaging
                                        </p>
                                        <p className="text-[11px] text-slate-500 dark:text-slate-400">
                                            Multi-layer bubble & foam
                                        </p>
                                    </div>
                                </div>

                                <div className="flex items-center gap-3 p-3.5 rounded-2xl bg-cyan-50/70 dark:bg-cyan-500/10 border border-cyan-100 dark:border-cyan-500/20">
                                    <div className="w-9 h-9 rounded-xl bg-cyan-500/15 flex items-center justify-center text-cyan-600 dark:text-cyan-400 flex-shrink-0">
                                        <Package className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p className="font-bold text-xs uppercase tracking-wide text-slate-900 dark:text-white">
                                            Tracked Shipments
                                        </p>
                                        <p className="text-[11px] text-slate-500 dark:text-slate-400">
                                            Live tracking link included
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* HTML Policy Content Render */}
                        {shipping?.content && (
                            <div
                                className="prose prose-base sm:prose-lg dark:prose-invert max-w-none text-slate-700 dark:text-slate-300 leading-relaxed prose-headings:font-display prose-headings:font-bold prose-headings:text-slate-900 dark:prose-headings:text-white prose-headings:uppercase prose-headings:tracking-wide prose-a:text-cyan-600 dark:prose-a:text-cyan-400 prose-a:underline hover:prose-a:text-cyan-700 space-y-6"
                                dangerouslySetInnerHTML={{
                                    __html: shipping.content,
                                }}
                            />
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
