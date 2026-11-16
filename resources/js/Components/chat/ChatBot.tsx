import { useCart } from "@/contexts/CartContext";
import { useWishlist } from "@/contexts/WishlistContext";
import { PageProps } from "@/types";
import { cn } from "@/utils/cn";
import { Link, router, usePage } from "@inertiajs/react";
import axios from "axios";
import { AnimatePresence, motion } from "framer-motion";
import {
    ExternalLink,
    Minimize2,
    Send,
    Volume2,
    VolumeX,
    X,
} from "lucide-react";
import React, { useEffect, useRef, useState } from "react";
import { toast } from "sonner";

// 8 Animation states mapped to Sprite Sheet rows (0..7)
export type RobotAnimationState =
    | "idle" // Row 0: Calm floating / core breathing
    | "walk" // Row 1: Forward patrol
    | "flight" // Row 2: High-speed jet thruster boost (active when dragging)
    | "hover" // Row 3: Hover pulse / vertical scan
    | "scan" // Row 4: Sensor scanning / radar sweep
    | "thinking" // Row 5: Data computation / calculating answer
    | "action" // Row 6: Interactive reaction / spin
    | "success"; // Row 7: Affirmative / celebrate / answer delivered

const ROW_MAP: Record<RobotAnimationState, number> = {
    idle: 0,
    walk: 1,
    flight: 2,
    hover: 3,
    scan: 4,
    thinking: 5,
    action: 6,
    success: 7,
};

// Helper to get frame source
const getFrameSrc = (row: number, frame: number) =>
    `/images/bot/robot/frames/row${row}_frame${frame}.webp`;

// Resolve product image path
const resolveProductImage = (img?: string | null) => {
    if (!img) return "/images/products/sample-keyboard.jpg";
    if (
        img.startsWith("http://") ||
        img.startsWith("https://") ||
        img.startsWith("/")
    ) {
        return img;
    }
    return `/storage/${img}`;
};

export interface ChatProductItem {
    id: number | string;
    name: string;
    slug: string;
    price: string;
    image?: string | null;
    stock: number;
}

export interface ChatOrderItem {
    order_number: string;
    status: string;
    total: string;
    date: string;
    items_count: number;
}

export interface ChatChipItem {
    label: string;
    query: string;
}

interface Message {
    id: string;
    sender: "bot" | "user";
    text: string;
    time: string;
    action?: {
        label: string;
        link?: string;
        requiresAuth?: boolean;
        onClick?: () => void;
    };
    chips?: ChatChipItem[];
    products?: ChatProductItem[];
    orders?: ChatOrderItem[];
}

const QUICK_ACTIONS = [
    { label: "🖥️ Build My PC", query: "build pc" },
    { label: "⚡ New Arrivals", query: "new arrivals" },
    { label: "🏷️ Best Sellers", query: "best sellers" },
    { label: "📦 Track Order", query: "track order" },
    { label: "🛒 My Cart", query: "my cart" },
    { label: "🔧 Upgrade Advice", query: "upgrade my pc" },
    { label: "🚚 Delivery Info", query: "delivery" },
    { label: "🛡️ Warranty Info", query: "warranty" },
    { label: "📞 Live Support", query: "contact" },
];

// Futuristic Web Audio Synthesizer for robot sounds
const playRobotSound = (type: "chirp" | "boost" | "blip" | "success") => {
    try {
        if (typeof window === "undefined") return;
        const AudioCtx =
            window.AudioContext || (window as any).webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        const now = ctx.currentTime;

        if (type === "chirp") {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = "sine";
            osc.frequency.setValueAtTime(440, now);
            osc.frequency.exponentialRampToValueAtTime(880, now + 0.12);
            osc.frequency.exponentialRampToValueAtTime(1320, now + 0.22);
            gain.gain.setValueAtTime(0.12, now);
            gain.gain.exponentialRampToValueAtTime(0.01, now + 0.25);
            osc.start(now);
            osc.stop(now + 0.25);
        } else if (type === "boost") {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = "triangle";
            osc.frequency.setValueAtTime(160, now);
            osc.frequency.exponentialRampToValueAtTime(380, now + 0.18);
            gain.gain.setValueAtTime(0.15, now);
            gain.gain.exponentialRampToValueAtTime(0.01, now + 0.2);
            osc.start(now);
            osc.stop(now + 0.2);
        } else if (type === "blip") {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = "sine";
            osc.frequency.setValueAtTime(620, now);
            osc.frequency.exponentialRampToValueAtTime(940, now + 0.08);
            gain.gain.setValueAtTime(0.1, now);
            gain.gain.exponentialRampToValueAtTime(0.01, now + 0.09);
            osc.start(now);
            osc.stop(now + 0.09);
        } else if (type === "success") {
            const osc1 = ctx.createOscillator();
            const osc2 = ctx.createOscillator();
            const gain = ctx.createGain();
            osc1.connect(gain);
            osc2.connect(gain);
            gain.connect(ctx.destination);
            osc1.type = "sine";
            osc2.type = "sine";
            osc1.frequency.setValueAtTime(523.25, now); // C5
            osc1.frequency.setValueAtTime(659.25, now + 0.1); // E5
            osc1.frequency.setValueAtTime(783.99, now + 0.2); // G5
            osc2.frequency.setValueAtTime(1046.5, now + 0.2); // C6
            gain.gain.setValueAtTime(0.12, now);
            gain.gain.exponentialRampToValueAtTime(0.01, now + 0.35);
            osc1.start(now);
            osc2.start(now + 0.2);
            osc1.stop(now + 0.35);
            osc2.stop(now + 0.35);
        }
    } catch {
        // Silently ignore if audio blocked by browser policy
    }
};

const getWelcomeMessage = (name: string): Message => ({
    id: "robot-init",
    sender: "bot",
    text: `Hello ${name}! 👋 Welcome to ComLink! I'm your friendly shopping assistant. How can I help you today? 😊`,
    time: "Online",
    chips: [
        { label: "🖥️ Build My PC", query: "build pc" },
        { label: "⚡ New Arrivals", query: "new arrivals" },
        { label: "🏷️ Best Sellers", query: "best sellers" },
        { label: "📦 Track Order", query: "track order" },
        { label: "🛒 My Cart", query: "my cart" },
        { label: "🚚 Delivery Info", query: "delivery" },
    ],
});

export default function ChatBot() {
    const { auth } = usePage<PageProps>().props;
    const userName = auth?.user?.name
        ? auth.user.name.trim().split(" ")[0] || "User"
        : "User";

    // Access cart & wishlist context safely
    let cartContext: ReturnType<typeof useCart> | null = null;
    let wishlistContext: ReturnType<typeof useWishlist> | null = null;
    try {
        cartContext = useCart();
    } catch {
        cartContext = null;
    }
    try {
        wishlistContext = useWishlist();
    } catch {
        wishlistContext = null;
    }

    const [isOpen, setIsOpen] = useState(false);
    const [soundEnabled, setSoundEnabled] = useState(true);
    const [animState, setAnimState] = useState<RobotAnimationState>("idle");
    const [frameIndex, setFrameIndex] = useState(0);
    const [isDragging, setIsDragging] = useState(false);
    const [isHovered, setIsHovered] = useState(false);
    const [isTyping, setIsTyping] = useState(false);
    const [input, setInput] = useState("");
    const isDraggingRef = useRef(false);
    const dragMovedRef = useRef(false);
    const messagesEndRef = useRef<HTMLDivElement>(null);

    const [messages, setMessages] = useState<Message[]>([
        getWelcomeMessage(userName),
    ]);

    // Keep initial welcome message updated with the resolved userName
    useEffect(() => {
        setMessages((prev) => {
            if (prev.length === 1 && prev[0].id === "robot-init") {
                return [getWelcomeMessage(userName)];
            }
            return prev;
        });
    }, [userName]);

    // 1. Preload all 64 frames for buttery-smooth 60fps animations
    useEffect(() => {
        if (typeof window === "undefined") return;
        for (let r = 0; r < 8; r++) {
            for (let c = 0; c < 8; c++) {
                const img = new Image();
                img.src = getFrameSrc(r, c);
            }
        }
    }, []);

    // 2. Continuous frame ticker (every 110ms changes to the next frame in the current row)
    useEffect(() => {
        const timer = setInterval(() => {
            setFrameIndex((prev) => (prev + 1) % 8);
        }, 110);
        return () => clearInterval(timer);
    }, []);

    // 3. Spontaneous natural movement flow (when idle, periodically scan, hover or perform a gesture)
    useEffect(() => {
        if (animState !== "idle" || isDragging || isTyping) return;

        // Random interval between 6.5s and 9.5s
        const randomDelay = Math.random() * 3000 + 6500;
        const timeout = setTimeout(() => {
            const spontaneousMoves: RobotAnimationState[] = [
                "scan",
                "hover",
                "walk",
                "action",
            ];
            const nextMove =
                spontaneousMoves[
                    Math.floor(Math.random() * spontaneousMoves.length)
                ];
            setAnimState(nextMove);

            // Flow back to idle after completing 1.8 seconds (approx 2 full 8-frame cycles)
            setTimeout(() => {
                setAnimState("idle");
            }, 1800);
        }, randomDelay);

        return () => clearTimeout(timeout);
    }, [animState, isDragging, isTyping]);

    // Scroll to bottom when messages update
    useEffect(() => {
        if (isOpen) {
            messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
        }
    }, [messages, isTyping, isOpen]);

    // Toggle chat open
    const handleToggleChat = (e?: React.MouseEvent) => {
        if (e) {
            e.stopPropagation();
        }
        if (isDraggingRef.current || dragMovedRef.current) return;

        if (!isOpen) {
            if (soundEnabled) playRobotSound("chirp");
            setIsOpen(true);
            setAnimState("action");
            setTimeout(() => {
                setAnimState("idle");
            }, 1200);
        } else {
            if (soundEnabled) playRobotSound("blip");
            setIsOpen(false);
            setAnimState("idle");
        }
    };

    // User message submission
    const handleSendMessage = async (textToSend?: string) => {
        const query = (textToSend || input).trim();
        if (!query) return;

        const timeStr = new Date().toLocaleTimeString([], {
            hour: "2-digit",
            minute: "2-digit",
        });

        const userMsg: Message = {
            id: Date.now().toString(),
            sender: "user",
            text: query,
            time: timeStr,
        };

        setMessages((prev) => [...prev, userMsg]);
        setInput("");
        if (soundEnabled) playRobotSound("blip");

        // Robot enters thinking / computation state (Row 5)
        setIsTyping(true);
        setAnimState("thinking");

        const cartPayload = cartContext
            ? {
                  items: cartContext.items,
                  totalItems: cartContext.totalItems,
                  totalPrice: cartContext.totalPrice,
              }
            : {};
        const wishlistPayload = wishlistContext ? wishlistContext.items : [];

        try {
            const response = await axios.post("/api/chat/query", {
                message: query,
                cart: cartPayload,
                wishlist: wishlistPayload,
            });

            const data = response.data;
            const newAnim: RobotAnimationState = data.anim || "success";
            setAnimState(newAnim);
            if (soundEnabled) {
                playRobotSound(newAnim === "action" ? "chirp" : "success");
            }

            const botMsg: Message = {
                id: (Date.now() + 1).toString(),
                sender: "bot",
                text:
                    data.reply ||
                    `Here to help you with all your tech needs, ${userName}! 😊`,
                time: timeStr,
                action: data.action,
                chips: data.chips,
                products: data.products,
                orders: data.orders,
            };

            setMessages((prev) => [...prev, botMsg]);

            setTimeout(() => {
                setAnimState("idle");
            }, 2500);
        } catch {
            // Friendly fallback if network / server drops
            generateClientFallback(query.toLowerCase(), timeStr);
        } finally {
            setIsTyping(false);
        }
    };

    // Client-side fallback if server cannot be reached
    const generateClientFallback = (lower: string, timeStr: string) => {
        let replyText = `I'm right here with you, ${userName}! 😊 Feel free to ask about PC parts, upgrades, stock, or our islandwide delivery!`;
        let action: Message["action"] = undefined;

        if (lower.includes("build") || lower.includes("pc builder")) {
            replyText = `Looking to build a custom PC, ${userName}? 🖥️ Try our custom PC Builder tool to pick compatible components!`;
            action = {
                label: "🖥️ Open Build My PC",
                link: "/build-my-pc",
                requiresAuth: !auth?.user,
            };
        } else if (lower.includes("contact") || lower.includes("phone")) {
            replyText = `We're here for you, ${userName}! 📞 Call us at +94 (77) 123-4567 or chat on WhatsApp!`;
            action = {
                label: "💬 Chat on WhatsApp",
                link: "https://wa.me/94771234567",
            };
        }

        setAnimState("success");
        if (soundEnabled) playRobotSound("success");
        setTimeout(() => setAnimState("idle"), 1500);

        setMessages((prev) => [
            ...prev,
            {
                id: (Date.now() + 1).toString(),
                sender: "bot",
                text: replyText,
                time: timeStr,
                action,
            },
        ]);
    };

    // Render formatted markdown-like text
    const renderFormattedText = (content: string) => {
        const lines = content.split("\n");
        return lines.map((line, lineIdx) => {
            const parts = line.split(/(\*\*[^*]+\*\*)/g);
            const renderedLine = parts.map((part, partIdx) => {
                if (part.startsWith("**") && part.endsWith("**")) {
                    return (
                        <strong
                            key={partIdx}
                            className="font-semibold text-cyan-300"
                        >
                            {part.slice(2, -2)}
                        </strong>
                    );
                }
                return part;
            });

            const trimmed = line.trim();
            const isBullet = trimmed.startsWith("•") || trimmed.startsWith("-");

            return (
                <div
                    key={lineIdx}
                    className={cn(
                        lineIdx > 0 && "mt-1",
                        isBullet && "pl-1.5 flex items-start gap-1"
                    )}
                >
                    {renderedLine}
                </div>
            );
        });
    };

    // Current row number from animation state
    const currentRow = ROW_MAP[animState] ?? 0;
    const currentFrameSrc = getFrameSrc(currentRow, frameIndex);

    return (
        <>
            <style>{`
                .comlink-no-scrollbar {
                    -ms-overflow-style: none !important;
                    scrollbar-width: none !important;
                }
                .comlink-no-scrollbar::-webkit-scrollbar {
                    display: none !important;
                    width: 0 !important;
                    height: 0 !important;
                }
            `}</style>

            {/* 1. FLOATING & DRAGGABLE ROBOT COMPANION (Speech bubble removed) */}
            <motion.div
                drag
                dragMomentum={false}
                dragElastic={0.08}
                onDragStart={() => {
                    isDraggingRef.current = true;
                    dragMovedRef.current = true;
                    setIsDragging(true);
                    setAnimState("flight"); // Jet propulsion mode while flying/moving!
                    if (soundEnabled) playRobotSound("boost");
                }}
                onDragEnd={() => {
                    setAnimState("idle");
                    setTimeout(() => {
                        isDraggingRef.current = false;
                        dragMovedRef.current = false;
                        setIsDragging(false);
                    }, 100);
                }}
                onClick={handleToggleChat}
                className="fixed right-5 sm:right-7 bottom-24 sm:bottom-28 z-40 select-none flex items-center cursor-grab active:cursor-grabbing pointer-events-auto"
                style={{ touchAction: "none" }}
            >
                {/* Robot Sprite Avatar Character */}
                <motion.div
                    onHoverStart={() => setIsHovered(true)}
                    onHoverEnd={() => setIsHovered(false)}
                    animate={{
                        y: isDragging ? 0 : [0, -8, 0],
                        scale: isDragging ? 1.12 : isHovered ? 1.08 : 1,
                    }}
                    transition={{
                        y: {
                            repeat: Infinity,
                            duration: 2.8,
                            ease: "easeInOut",
                        },
                        scale: { duration: 0.2 },
                    }}
                    className="relative transform-gpu select-none transition-all"
                    title="Click to chat! (Drag me anywhere with cursor)"
                >
                    {/* Glowing Thruster / Aura underneath */}
                    <div className="absolute -bottom-2 left-1/2 -translate-x-1/2 w-16 h-4 bg-cyan-400/30 rounded-full blur-md animate-pulse pointer-events-none"></div>

                    {/* Preloaded Animated Frame Image */}
                    <img
                        src={currentFrameSrc}
                        alt="Chat Robot"
                        className="w-24 sm:w-28 md:w-32 h-auto object-contain drop-shadow-[0_10px_25px_rgba(6,182,212,0.45)] pointer-events-none"
                        draggable={false}
                    />
                </motion.div>
            </motion.div>

            {/* 2. OPEN CHATBOT WINDOW DIALOG */}
            <AnimatePresence>
                {isOpen && (
                    <motion.div
                        initial={{ opacity: 0, y: 40, scale: 0.92 }}
                        animate={{ opacity: 1, y: 0, scale: 1 }}
                        exit={{ opacity: 0, y: 30, scale: 0.9 }}
                        transition={{
                            duration: 0.28,
                            ease: [0.16, 1, 0.3, 1],
                        }}
                        className="fixed right-3 sm:right-6 bottom-4 sm:bottom-6 z-50 w-[92vw] sm:w-[410px] md:w-[430px] h-[600px] max-h-[88vh] flex flex-col bg-[#0b101b]/95 text-slate-100 rounded-2xl shadow-[0_20px_60px_rgba(0,0,0,0.85)] border border-cyan-500/30 backdrop-blur-xl overflow-hidden font-sans select-none"
                    >
                        {/* Top Ambient Glow */}
                        <div className="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-cyan-500 via-blue-500 to-indigo-500"></div>
                        <div className="absolute -top-16 -right-16 w-36 h-36 bg-cyan-500/20 rounded-full blur-3xl pointer-events-none"></div>
                        <div className="absolute top-1/2 -left-20 w-36 h-36 bg-blue-600/15 rounded-full blur-3xl pointer-events-none"></div>

                        {/* Clean Robot Header Bar (COMLINK BOT and AI Companion text removed) */}
                        <div className="relative px-4 py-3 bg-[#131c2e]/90 border-b border-cyan-500/20 flex items-center justify-between z-10">
                            <div className="flex items-center gap-3">
                                {/* Live Animated Robot Avatar in Header */}
                                <div
                                    onClick={() => {
                                        setAnimState("action");
                                        if (soundEnabled)
                                            playRobotSound("chirp");
                                        setTimeout(
                                            () => setAnimState("idle"),
                                            1500
                                        );
                                    }}
                                    className="relative group cursor-pointer"
                                    title="Click to trigger robot spin!"
                                >
                                    <div className="w-10 h-10 rounded-full bg-gradient-to-tr from-cyan-600/40 to-blue-600/30 border border-cyan-400/50 p-1 shadow-[0_0_15px_rgba(6,182,212,0.35)] flex items-center justify-center overflow-hidden transition-transform group-hover:scale-105 active:scale-95">
                                        <img
                                            src={currentFrameSrc}
                                            alt="Robot Avatar"
                                            className="w-9 h-9 object-contain drop-shadow"
                                        />
                                    </div>
                                    <span className="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-[#131c2e] rounded-full"></span>
                                </div>
                            </div>

                            {/* Window Controls */}
                            <div className="flex items-center gap-1 text-slate-400">
                                <button
                                    onClick={() =>
                                        setSoundEnabled(!soundEnabled)
                                    }
                                    className="p-1.5 hover:text-cyan-400 hover:bg-white/5 rounded-lg transition-colors cursor-pointer"
                                    title={
                                        soundEnabled
                                            ? "Mute sounds"
                                            : "Enable sounds"
                                    }
                                >
                                    {soundEnabled ? (
                                        <Volume2 className="w-4 h-4" />
                                    ) : (
                                        <VolumeX className="w-4 h-4 text-slate-500" />
                                    )}
                                </button>
                                <button
                                    onClick={handleToggleChat}
                                    className="p-1.5 hover:text-cyan-400 hover:bg-white/5 rounded-lg transition-colors cursor-pointer"
                                    title="Minimize"
                                >
                                    <Minimize2 className="w-4 h-4" />
                                </button>
                                <button
                                    onClick={handleToggleChat}
                                    className="p-1.5 hover:text-red-400 hover:bg-white/5 rounded-lg transition-colors cursor-pointer"
                                    title="Close"
                                >
                                    <X className="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        {/* Chat Messages Area */}
                        <div className="flex-1 overflow-y-auto p-4 space-y-3.5 scrollbar-thin scrollbar-thumb-cyan-500/20 scrollbar-track-transparent">
                            {messages.map((msg) => (
                                <motion.div
                                    key={msg.id}
                                    initial={{ opacity: 0, y: 10 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    transition={{ duration: 0.25 }}
                                    className={`flex flex-col ${
                                        msg.sender === "user"
                                            ? "items-end"
                                            : "items-start"
                                    }`}
                                >
                                    <div
                                        className={`max-w-[88%] rounded-2xl px-4 py-2.5 text-xs sm:text-sm leading-relaxed shadow-md ${
                                            msg.sender === "user"
                                                ? "bg-gradient-to-r from-cyan-600 to-blue-600 text-white rounded-br-xs"
                                                : "bg-[#182337] text-slate-200 border border-cyan-500/20 rounded-bl-xs"
                                        }`}
                                    >
                                        <div>{renderFormattedText(msg.text)}</div>

                                        {/* Dynamic Product Cards */}
                                        {msg.products &&
                                            msg.products.length > 0 && (
                                                <div className="mt-3 flex gap-2.5 overflow-x-auto comlink-no-scrollbar pb-1">
                                                    {msg.products.map(
                                                        (prod) => (
                                                            <Link
                                                                key={prod.id}
                                                                href={`/product/${prod.slug}`}
                                                                className="w-36 shrink-0 bg-[#0d1524] hover:bg-[#131d31] border border-cyan-500/25 hover:border-cyan-400/60 rounded-xl p-2 transition-all duration-200 group flex flex-col justify-between"
                                                            >
                                                                <div className="w-full aspect-square bg-[#070b14] rounded-lg overflow-hidden flex items-center justify-center p-1 mb-1.5 border border-slate-800">
                                                                    <img
                                                                        src={resolveProductImage(
                                                                            prod.image
                                                                        )}
                                                                        alt={
                                                                            prod.name
                                                                        }
                                                                        className="w-full h-full object-contain group-hover:scale-105 transition-transform"
                                                                        onError={(
                                                                            e
                                                                        ) => {
                                                                            (
                                                                                e.target as HTMLImageElement
                                                                            ).src =
                                                                                "/images/products/sample-keyboard.jpg";
                                                                        }}
                                                                    />
                                                                </div>
                                                                <div>
                                                                    <h5 className="text-[11px] font-semibold text-slate-200 line-clamp-2 leading-tight group-hover:text-cyan-300 transition-colors">
                                                                        {
                                                                            prod.name
                                                                        }
                                                                    </h5>
                                                                    <div className="mt-1 flex items-center justify-between">
                                                                        <span className="text-[11px] font-bold text-cyan-400">
                                                                            {
                                                                                prod.price
                                                                            }
                                                                        </span>
                                                                        <span
                                                                            className={cn(
                                                                                "text-[9px] px-1 py-0.5 rounded font-medium",
                                                                                prod.stock >
                                                                                    0
                                                                                    ? "bg-emerald-500/15 text-emerald-400 border border-emerald-500/30"
                                                                                    : "bg-rose-500/15 text-rose-400 border border-rose-500/30"
                                                                            )}
                                                                        >
                                                                            {prod.stock >
                                                                            0
                                                                                ? "In Stock"
                                                                                : "Out"}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </Link>
                                                        )
                                                    )}
                                                </div>
                                            )}

                                        {/* Dynamic Orders Cards */}
                                        {msg.orders &&
                                            msg.orders.length > 0 && (
                                                <div className="mt-2.5 space-y-2 w-full">
                                                    {msg.orders.map((ord) => (
                                                        <div
                                                            key={
                                                                ord.order_number
                                                            }
                                                            className="bg-[#0d1524] border border-cyan-500/25 rounded-xl p-2.5 text-xs flex items-center justify-between"
                                                        >
                                                            <div>
                                                                <div className="font-semibold text-cyan-300">
                                                                    #
                                                                    {
                                                                        ord.order_number
                                                                    }
                                                                </div>
                                                                <div className="text-[11px] text-slate-400">
                                                                    {ord.date} •{" "}
                                                                    {
                                                                        ord.items_count
                                                                    }{" "}
                                                                    item
                                                                    {ord.items_count >
                                                                    1
                                                                        ? "s"
                                                                        : ""}
                                                                </div>
                                                            </div>
                                                            <div className="text-right">
                                                                <div className="font-bold text-slate-100">
                                                                    {ord.total}
                                                                </div>
                                                                <span className="inline-block mt-0.5 text-[10px] px-1.5 py-0.5 rounded-full uppercase tracking-wider font-semibold bg-cyan-500/20 text-cyan-300 border border-cyan-500/40">
                                                                    {ord.status}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    ))}
                                                </div>
                                            )}

                                        {/* Action Button if attached */}
                                        {msg.action && (
                                            <div className="mt-2.5 pt-2 border-t border-cyan-500/20">
                                                <button
                                                    onClick={() => {
                                                        if (
                                                            msg.action?.onClick
                                                        ) {
                                                            msg.action.onClick();
                                                        } else if (
                                                            msg.action?.link
                                                        ) {
                                                            if (
                                                                msg.action
                                                                    .requiresAuth &&
                                                                !auth?.user
                                                            ) {
                                                                toast.info(
                                                                    "Please login to proceed."
                                                                );
                                                                router.visit(
                                                                    "/login"
                                                                );
                                                            } else if (
                                                                msg.action.link.startsWith(
                                                                    "http"
                                                                )
                                                            ) {
                                                                window.open(
                                                                    msg.action
                                                                        .link,
                                                                    "_blank"
                                                                );
                                                            } else {
                                                                router.visit(
                                                                    msg.action
                                                                        .link
                                                                );
                                                            }
                                                        }
                                                    }}
                                                    className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-cyan-500/20 hover:bg-cyan-500/30 text-cyan-300 text-xs font-bold border border-cyan-500/40 transition-all hover:scale-[1.02] active:scale-[0.98] cursor-pointer"
                                                >
                                                    {msg.action.label}
                                                    <ExternalLink className="w-3 h-3" />
                                                </button>
                                            </div>
                                        )}

                                        {/* Interactive Quick Reply Chips */}
                                        {msg.chips && msg.chips.length > 0 && (
                                            <div className="mt-2.5 flex flex-wrap gap-1.5">
                                                {msg.chips.map(
                                                    (chip, chipIdx) => (
                                                        <button
                                                            key={chipIdx}
                                                            onClick={() =>
                                                                handleSendMessage(
                                                                    chip.query
                                                                )
                                                            }
                                                            className="px-2.5 py-1 rounded-lg bg-cyan-950/60 hover:bg-cyan-900/60 text-cyan-300 hover:text-cyan-200 border border-cyan-500/30 text-[11px] font-medium transition-all hover:scale-105 active:scale-95 cursor-pointer shadow-sm"
                                                        >
                                                            {chip.label}
                                                        </button>
                                                    )
                                                )}
                                            </div>
                                        )}
                                    </div>
                                    <span className="text-[10px] text-slate-500 mt-1 px-1">
                                        {msg.time}
                                    </span>
                                </motion.div>
                            ))}

                            {/* Typing / Scanning Indicator */}
                            {isTyping && (
                                <motion.div
                                    initial={{ opacity: 0, y: 5 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    className="flex items-center gap-2 text-slate-400 text-xs bg-[#182337] border border-cyan-500/20 px-3.5 py-2 rounded-2xl rounded-bl-xs w-fit"
                                >
                                    <span className="text-cyan-400 font-semibold">
                                        Thinking
                                    </span>
                                    <span className="flex gap-1 items-center ml-1">
                                        <span className="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-bounce"></span>
                                        <span className="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-bounce [animation-delay:0.2s]"></span>
                                        <span className="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-bounce [animation-delay:0.4s]"></span>
                                    </span>
                                </motion.div>
                            )}
                            <div ref={messagesEndRef} />
                        </div>

                        {/* Quick Prompts Carousel (Scrollbar completely hidden) */}
                        <div
                            className="px-3 py-2 bg-[#101726] border-t border-cyan-500/10 flex items-center gap-1.5 overflow-x-auto comlink-no-scrollbar [&::-webkit-scrollbar]:hidden"
                            style={{
                                scrollbarWidth: "none",
                                msOverflowStyle: "none",
                            }}
                        >
                            {QUICK_ACTIONS.map((action, i) => (
                                <button
                                    key={i}
                                    onClick={() =>
                                        handleSendMessage(action.query)
                                    }
                                    className="whitespace-nowrap px-3 py-1.5 rounded-full bg-[#182337] hover:bg-cyan-500/20 text-[11px] font-medium text-slate-300 hover:text-cyan-300 border border-slate-700/60 hover:border-cyan-500/40 transition-all cursor-pointer flex-shrink-0"
                                >
                                    {action.label}
                                </button>
                            ))}
                        </div>

                        {/* Input Footer Bar */}
                        <form
                            onSubmit={(e) => {
                                e.preventDefault();
                                handleSendMessage();
                            }}
                            className="p-3 bg-[#131c2e] border-t border-cyan-500/20 flex items-center gap-2"
                        >
                            <input
                                type="text"
                                value={input}
                                onChange={(e) => setInput(e.target.value)}
                                placeholder="Ask about PCs, parts, orders, delivery..."
                                className="flex-1 bg-[#0b101b] border border-slate-700/80 focus:border-cyan-400 rounded-xl px-3.5 py-2 text-xs sm:text-sm text-white placeholder-slate-500 outline-none transition-colors"
                            />
                            <button
                                type="submit"
                                disabled={!input.trim()}
                                className="p-2 sm:px-3 sm:py-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 disabled:opacity-40 disabled:hover:from-cyan-500 disabled:hover:to-blue-600 text-white font-bold transition-all flex items-center justify-center shadow-[0_0_15px_rgba(6,182,212,0.3)] cursor-pointer"
                            >
                                <Send className="w-4 h-4" />
                            </button>
                        </form>
                    </motion.div>
                )}
            </AnimatePresence>
        </>
    );
}
