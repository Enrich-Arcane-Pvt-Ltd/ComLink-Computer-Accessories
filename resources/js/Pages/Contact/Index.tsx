import TextArea from "@/Components/TextArea";
import TextInput from "@/Components/TextInput";
import AppLayout from "@/Layouts/AppLayout";
import { cn } from "@/utils/cn";
import { Head, Link, useForm, usePage } from "@inertiajs/react";
import {
    ArrowRight,
    Check,
    Clock,
    Cpu,
    Mail,
    MapPin,
    MessageSquare,
    Phone,
    RotateCcw,
    Send,
    ShieldCheck,
    Truck,
    Wrench,
} from "lucide-react";
import React, { useEffect, useState, type MouseEvent, type ReactNode } from "react";
import { toast } from "sonner";

/* -------------------------------------------------------------------------- */
/*  Helpers & Spotlight Card Component                                        */
/* -------------------------------------------------------------------------- */

function SpotlightCard({
    children,
    className,
}: {
    children: ReactNode;
    className?: string;
}) {
    const onMove = (e: MouseEvent<HTMLDivElement>) => {
        const rect = e.currentTarget.getBoundingClientRect();
        e.currentTarget.style.setProperty("--mx", `${e.clientX - rect.left}px`);
        e.currentTarget.style.setProperty("--my", `${e.clientY - rect.top}px`);
    };

    return (
        <div
            onMouseMove={onMove}
            className={cn(
                "group relative overflow-hidden rounded-2xl border border-border bg-card p-6 md:p-8 transition-all duration-300 hover:border-accent/60 hover:shadow-lg",
                className,
            )}
        >
            <div
                aria-hidden="true"
                className="pointer-events-none absolute inset-0 opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                style={{
                    background:
                        "radial-gradient(360px circle at var(--mx, 50%) var(--my, 50%), rgba(34,211,238,0.12), transparent 60%)",
                }}
            />
            <div className="relative">{children}</div>
        </div>
    );
}

const quickTopics = [
    "Custom PC Build",
    "Hardware & Compatibility",
    "Warranty & RMA",
    "Islandwide Delivery",
    "Corporate & Wholesale",
];

/* -------------------------------------------------------------------------- */
/*  Main Component                                                            */
/* -------------------------------------------------------------------------- */

export default function Contact() {
    const { flash } = usePage().props as any;

    const { data, setData, post, processing, errors, reset, clearErrors } =
        useForm({
            name: "",
            email: "",
            phone: "",
            subject: "",
            message: "",
        });

    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success);
            reset();
        }
    }, [flash]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        clearErrors();
        post(route("contact.store"), {
            preserveScroll: true,
        });
    };

    const handleTopicClick = (topic: string) => {
        setData("subject", topic);
    };

    return (
        <AppLayout>
            <Head title="Contact Us | ComLink Computer Accessories" />

            <div className="min-h-screen bg-bg text-fg">
                {/* ------------------------------------------------------------------ */}
                {/*  Hero Header with Tech Blueprint Grid                              */}
                {/* ------------------------------------------------------------------ */}
                <section className="relative overflow-hidden bg-primary-950 px-4 py-16 text-center text-white md:py-24">
                    {/* Tech blueprint grid pattern */}
                    <div
                        aria-hidden="true"
                        className="absolute inset-0 pointer-events-none"
                        style={{
                            backgroundImage:
                                "linear-gradient(rgba(34,211,238,0.07) 1px, transparent 1px), linear-gradient(90deg, rgba(34,211,238,0.07) 1px, transparent 1px)",
                            backgroundSize: "44px 44px",
                            maskImage:
                                "radial-gradient(ellipse at center, black 25%, transparent 75%)",
                            WebkitMaskImage:
                                "radial-gradient(ellipse at center, black 25%, transparent 75%)",
                        }}
                    />

                    {/* Cyan glow sphere */}
                    <div
                        aria-hidden="true"
                        className="pointer-events-none absolute left-1/2 top-1/2 h-[340px] w-[680px] -translate-x-1/2 -translate-y-1/2 rounded-full opacity-25 blur-3xl"
                        style={{
                            background:
                                "radial-gradient(circle, rgba(6,182,212,0.65) 0%, transparent 70%)",
                        }}
                    />

                    <div className="relative mx-auto max-w-3xl">
                        {/* Breadcrumbs */}
                        <nav
                            aria-label="Breadcrumb"
                            className="mb-5 flex items-center justify-center gap-2 text-xs uppercase tracking-widest text-white/60"
                        >
                            <Link href="/" className="transition-colors hover:text-accent-300">
                                Home
                            </Link>
                            <span>/</span>
                            <span className="text-accent-300">Contact Us</span>
                        </nav>

                        <span className="mb-3 inline-flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.3em] text-accent-300">
                            <span className="h-px w-8 bg-accent-300" />
                            Connecting Your Tech
                            <span className="h-px w-8 bg-accent-300" />
                        </span>

                        <h1 className="mb-4 text-4xl font-extrabold font-display uppercase tracking-tight md:text-6xl text-white">
                            Get In Touch With <span className="text-accent-400">ComLink</span>
                        </h1>

                        <p className="mx-auto max-w-xl text-sm leading-relaxed text-white/75 md:text-base">
                            Need advice on a custom gaming build, component compatibility, or warranty support?
                            Our computer hardware specialists are here to help.
                        </p>
                    </div>
                </section>

                {/* ------------------------------------------------------------------ */}
                {/*  3 Quick Action Support Pillars                                    */}
                {/* ------------------------------------------------------------------ */}
                <section className="relative -mt-8 z-10 container mx-auto px-4 md:px-6 mb-12">
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-5">
                        {/* 1. Custom PC & Hardware Advice */}
                        <div className="rounded-2xl border border-border bg-card p-6 shadow-md transition-all hover:-translate-y-1 hover:border-accent/60">
                            <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-accent/15 text-accent">
                                <Cpu className="h-6 w-6" />
                            </div>
                            <h3 className="text-base font-bold text-fg mb-1">
                                PC Build & Hardware Consultation
                            </h3>
                            <p className="text-xs text-muted mb-4 leading-relaxed">
                                Talk directly with our system engineers for part recommendations and compatibility checks.
                            </p>
                            <Link
                                href="/shop"
                                className="inline-flex items-center gap-1.5 text-xs font-bold text-accent hover:underline"
                            >
                                Browse PC Components
                                <ArrowRight className="h-3.5 w-3.5" />
                            </Link>
                        </div>

                        {/* 2. Technical Support & Warranty */}
                        <div className="rounded-2xl border border-border bg-card p-6 shadow-md transition-all hover:-translate-y-1 hover:border-accent/60">
                            <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-accent/15 text-accent">
                                <Wrench className="h-6 w-6" />
                            </div>
                            <h3 className="text-base font-bold text-fg mb-1">
                                Technical Support & RMA
                            </h3>
                            <p className="text-xs text-muted mb-4 leading-relaxed">
                                Need help with driver installation, BIOS updates, or authorized warranty claims?
                            </p>
                            <a
                                href="mailto:support@comlink.lk"
                                className="inline-flex items-center gap-1.5 text-xs font-bold text-accent hover:underline"
                            >
                                support@comlink.lk
                                <ArrowRight className="h-3.5 w-3.5" />
                            </a>
                        </div>

                        {/* 3. WhatsApp Quick Inquiries */}
                        <div className="rounded-2xl border border-border bg-card p-6 shadow-md transition-all hover:-translate-y-1 hover:border-accent/60">
                            <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-500">
                                <MessageSquare className="h-6 w-6" />
                            </div>
                            <h3 className="text-base font-bold text-fg mb-1">
                                WhatsApp Quick Chat
                            </h3>
                            <p className="text-xs text-muted mb-4 leading-relaxed">
                                Real-time stock checks, instant quotations, and delivery tracking updates on your phone.
                            </p>
                            <a
                                href="https://wa.me/94771234567"
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-500 hover:underline"
                            >
                                Chat on WhatsApp (+94 77 123 4567)
                                <ArrowRight className="h-3.5 w-3.5" />
                            </a>
                        </div>
                    </div>
                </section>

                {/* ------------------------------------------------------------------ */}
                {/*  Main Content: Store Info & Contact Form                           */}
                {/* ------------------------------------------------------------------ */}
                <div className="container mx-auto px-4 md:px-6 py-8 md:py-12">
                    <div className="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
                        {/* ---------------------------------------------------------- */}
                        {/*  Left Column: Store Details & Interactive Map (5 Cols)     */}
                        {/* ---------------------------------------------------------- */}
                        <div className="lg:col-span-5 space-y-6">
                            <SpotlightCard className="space-y-6">
                                <div>
                                    <span className="text-xs font-semibold uppercase tracking-widest text-accent mb-1 block">
                                        Store Information
                                    </span>
                                    <h2 className="font-display text-2xl font-bold text-fg">
                                        Visit Our Stores
                                    </h2>
                                    <p className="text-xs text-muted mt-1 leading-relaxed">
                                        Drop by to inspect products in person, test gaming gear, or collect your online orders.
                                    </p>
                                </div>

                                <div className="space-y-5 pt-2">
                                    {/* Location 1: Colombo Flagship */}
                                    <div className="flex gap-4">
                                        <div className="w-11 h-11 rounded-xl bg-accent/15 border border-accent/20 flex items-center justify-center text-accent shrink-0">
                                            <MapPin className="h-5 w-5" />
                                        </div>
                                        <div>
                                            <h4 className="font-bold text-fg text-sm">
                                                Colombo Flagship Store & Service Hub
                                            </h4>
                                            <p className="text-xs text-muted leading-relaxed mt-0.5">
                                                No. 45, Galle Road, Colombo 04, Sri Lanka.
                                            </p>
                                            <p className="text-[11px] text-accent mt-0.5 font-medium">
                                                (Opposite Majestic City junction)
                                            </p>
                                        </div>
                                    </div>

                                    {/* Location 2: Kandy Branch */}
                                    <div className="flex gap-4">
                                        <div className="w-11 h-11 rounded-xl bg-card border border-border flex items-center justify-center text-muted shrink-0">
                                            <MapPin className="h-5 w-5" />
                                        </div>
                                        <div>
                                            <h4 className="font-bold text-fg text-sm">
                                                Kandy Branch & Pickup Center
                                            </h4>
                                            <p className="text-xs text-muted leading-relaxed mt-0.5">
                                                No. 12, Dalada Veediya, Kandy, Sri Lanka.
                                            </p>
                                        </div>
                                    </div>

                                    {/* Phone Numbers */}
                                    <div className="flex gap-4">
                                        <div className="w-11 h-11 rounded-xl bg-accent/15 border border-accent/20 flex items-center justify-center text-accent shrink-0">
                                            <Phone className="h-5 w-5" />
                                        </div>
                                        <div>
                                            <h4 className="font-bold text-fg text-sm">
                                                Hotlines & Inquiries
                                            </h4>
                                            <p className="text-xs text-muted mt-0.5">
                                                Sales & WhatsApp:{" "}
                                                <a href="tel:+94771234567" className="text-fg font-medium hover:text-accent">
                                                    +94 (77) 123-4567
                                                </a>
                                            </p>
                                            <p className="text-xs text-muted mt-0.5">
                                                Service & Tech Desk:{" "}
                                                <a href="tel:+94117654321" className="text-fg font-medium hover:text-accent">
                                                    +94 (11) 765-4321
                                                </a>
                                            </p>
                                        </div>
                                    </div>

                                    {/* Email Address */}
                                    <div className="flex gap-4">
                                        <div className="w-11 h-11 rounded-xl bg-accent/15 border border-accent/20 flex items-center justify-center text-accent shrink-0">
                                            <Mail className="h-5 w-5" />
                                        </div>
                                        <div>
                                            <h4 className="font-bold text-fg text-sm">
                                                Email Inquiries
                                            </h4>
                                            <p className="text-xs text-muted mt-0.5">
                                                General:{" "}
                                                <a href="mailto:info@comlink.lk" className="text-fg font-medium hover:text-accent">
                                                    info@comlink.lk
                                                </a>
                                            </p>
                                            <p className="text-xs text-muted mt-0.5">
                                                Warranty & RMA:{" "}
                                                <a href="mailto:support@comlink.lk" className="text-fg font-medium hover:text-accent">
                                                    support@comlink.lk
                                                </a>
                                            </p>
                                        </div>
                                    </div>

                                    {/* Opening Hours */}
                                    <div className="flex gap-4">
                                        <div className="w-11 h-11 rounded-xl bg-accent/15 border border-accent/20 flex items-center justify-center text-accent shrink-0">
                                            <Clock className="h-5 w-5" />
                                        </div>
                                        <div>
                                            <h4 className="font-bold text-fg text-sm">
                                                Store Operating Hours
                                            </h4>
                                            <p className="text-xs text-muted mt-0.5">
                                                Monday – Saturday: <strong>9:00 AM – 7:30 PM</strong>
                                            </p>
                                            <p className="text-xs text-muted mt-0.5">
                                                Sunday & Poya Days: <strong>9:30 AM – 4:00 PM</strong>
                                            </p>
                                            <p className="text-[11px] text-emerald-500 font-semibold mt-1 flex items-center gap-1">
                                                <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block" />
                                                Online Shop & Delivery: 24/7 Active
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </SpotlightCard>

                            {/* Live Interactive Google Map Embed */}
                            <div className="rounded-2xl border border-border bg-card overflow-hidden shadow-sm">
                                <div className="p-3 border-b border-border bg-muted/10 flex items-center justify-between text-xs">
                                    <span className="font-semibold text-fg flex items-center gap-1.5">
                                        <MapPin className="w-3.5 h-3.5 text-accent" />
                                        Colombo 04 Location Map
                                    </span>
                                    <a
                                        href="https://maps.google.com/?q=Colombo+04,+Sri+Lanka"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="text-accent hover:underline text-[11px] font-medium"
                                    >
                                        Open in Google Maps
                                    </a>
                                </div>
                                <div className="w-full h-64 bg-muted/10">
                                    <iframe
                                        title="ComLink Computer Accessories Store Location"
                                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15843.834047814467!2d79.851978!3d6.892015!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae25960010901ab%3A0x6a3fb965c71c4c1!2sBambalapitiya%2C%20Colombo%2000400!5e0!3m2!1sen!2slk!4v1700000000000!5m2!1sen!2slk"
                                        width="100%"
                                        height="100%"
                                        style={{ border: 0 }}
                                        allowFullScreen={false}
                                        loading="lazy"
                                        referrerPolicy="no-referrer-when-downgrade"
                                        className="w-full h-full grayscale-[20%] contrast-110"
                                    />
                                </div>
                            </div>
                        </div>

                        {/* ---------------------------------------------------------- */}
                        {/*  Right Column: Send Us A Message Form (7 Cols)             */}
                        {/* ---------------------------------------------------------- */}
                        <div className="lg:col-span-7">
                            <SpotlightCard className="p-8 md:p-10">
                                <div className="mb-8">
                                    <span className="text-xs font-semibold uppercase tracking-widest text-accent mb-1 block">
                                        Inquiries & Quotes
                                    </span>
                                    <h2 className="font-display text-2xl md:text-3xl font-bold text-fg">
                                        Send Us A Message
                                    </h2>
                                    <p className="text-sm text-muted mt-1 leading-relaxed">
                                        Fill out the form below with your hardware requirement or question.
                                        Our tech team will respond promptly within 2-4 working hours.
                                    </p>
                                </div>

                                {/* Quick Topic Pill Selector */}
                                <div className="mb-6">
                                    <label className="block text-xs font-semibold uppercase tracking-wider text-muted mb-2">
                                        Select Inquiry Topic:
                                    </label>
                                    <div className="flex flex-wrap gap-2">
                                        {quickTopics.map((topic) => {
                                            const isSelected = data.subject === topic;
                                            return (
                                                <button
                                                    key={topic}
                                                    type="button"
                                                    onClick={() => handleTopicClick(topic)}
                                                    className={cn(
                                                        "text-xs px-3 py-1.5 rounded-lg border transition-all text-left font-medium",
                                                        isSelected
                                                            ? "bg-accent text-white dark:text-slate-950 border-accent font-semibold shadow-sm"
                                                            : "bg-bg border-border text-muted hover:text-fg hover:border-accent/50",
                                                    )}
                                                >
                                                    {topic}
                                                </button>
                                            );
                                        })}
                                    </div>
                                </div>

                                <form onSubmit={handleSubmit} className="space-y-6">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        {/* Name */}
                                        <TextInput
                                            id="name"
                                            type="text"
                                            name="name"
                                            label="Your Name"
                                            placeholder="e.g. Kasun Perera"
                                            value={data.name}
                                            onChange={(e) => setData("name", e.target.value)}
                                            error={errors.name}
                                            required
                                        />

                                        {/* Email */}
                                        <TextInput
                                            id="email"
                                            type="email"
                                            name="email"
                                            label="Email Address"
                                            placeholder="you@example.com"
                                            value={data.email}
                                            onChange={(e) => setData("email", e.target.value)}
                                            error={errors.email}
                                            required
                                        />
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        {/* Phone */}
                                        <TextInput
                                            id="phone"
                                            type="text"
                                            name="phone"
                                            label="Phone / WhatsApp Number"
                                            placeholder="+94 77 XXXXXXX"
                                            value={data.phone}
                                            onChange={(e) => setData("phone", e.target.value)}
                                            error={errors.phone}
                                            required
                                        />

                                        {/* Subject */}
                                        <TextInput
                                            id="subject"
                                            type="text"
                                            name="subject"
                                            label="Subject"
                                            placeholder="e.g. RTX 4070 Gaming PC Quote"
                                            value={data.subject}
                                            onChange={(e) => setData("subject", e.target.value)}
                                            error={errors.subject}
                                            required
                                        />
                                    </div>

                                    {/* Message */}
                                    <TextArea
                                        id="message"
                                        label="Your Message or Component Specs"
                                        placeholder="Let us know what computer accessories, custom PC specifications, or assistance you require..."
                                        value={data.message}
                                        onChange={(e) => setData("message", e.target.value)}
                                        error={errors.message}
                                        required
                                        className="min-h-[160px]"
                                    />

                                    {/* Submit Button */}
                                    <div className="pt-2">
                                        <button
                                            type="submit"
                                            disabled={processing}
                                            className="w-full sm:w-auto px-8 py-3.5 bg-accent hover:opacity-90 text-white dark:text-slate-950 font-bold uppercase tracking-wider text-xs md:text-sm rounded-xl transition-all shadow-md inline-flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            {processing ? (
                                                <>
                                                    <RotateCcw className="h-4 w-4 animate-spin" />
                                                    <span>Sending Message...</span>
                                                </>
                                            ) : (
                                                <>
                                                    <Send className="h-4 w-4" />
                                                    <span>Submit Inquiry</span>
                                                </>
                                            )}
                                        </button>
                                    </div>
                                </form>
                            </SpotlightCard>
                        </div>
                    </div>
                </div>

                {/* ------------------------------------------------------------------ */}
                {/*  Trust & Value Proposition Bar                                     */}
                {/* ------------------------------------------------------------------ */}
                <section className="border-t border-border bg-card py-12 mt-12">
                    <div className="container mx-auto px-4 md:px-6">
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div className="flex items-center gap-4 p-4 rounded-2xl bg-bg border border-border">
                                <div className="w-12 h-12 rounded-xl bg-accent/15 text-accent flex items-center justify-center shrink-0">
                                    <ShieldCheck className="w-6 h-6" />
                                </div>
                                <div>
                                    <h4 className="font-semibold text-sm text-fg">100% Genuine Brands</h4>
                                    <p className="text-xs text-muted mt-0.5">Direct authorized distributor warranty</p>
                                </div>
                            </div>
                            <div className="flex items-center gap-4 p-4 rounded-2xl bg-bg border border-border">
                                <div className="w-12 h-12 rounded-xl bg-accent/15 text-accent flex items-center justify-center shrink-0">
                                    <Truck className="w-6 h-6" />
                                </div>
                                <div>
                                    <h4 className="font-semibold text-sm text-fg">Islandwide Delivery</h4>
                                    <p className="text-xs text-muted mt-0.5">Fast tracked courier delivery</p>
                                </div>
                            </div>
                            <div className="flex items-center gap-4 p-4 rounded-2xl bg-bg border border-border">
                                <div className="w-12 h-12 rounded-xl bg-accent/15 text-accent flex items-center justify-center shrink-0">
                                    <Wrench className="w-6 h-6" />
                                </div>
                                <div>
                                    <h4 className="font-semibold text-sm text-fg">Expert PC Technicians</h4>
                                    <p className="text-xs text-muted mt-0.5">Stress testing & cable management</p>
                                </div>
                            </div>
                            <div className="flex items-center gap-4 p-4 rounded-2xl bg-bg border border-border">
                                <div className="w-12 h-12 rounded-xl bg-accent/15 text-accent flex items-center justify-center shrink-0">
                                    <MessageSquare className="w-6 h-6" />
                                </div>
                                <div>
                                    <h4 className="font-semibold text-sm text-fg">Quick WhatsApp Advice</h4>
                                    <p className="text-xs text-muted mt-0.5">Friendly real-time pre-sales support</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </AppLayout>
    );
}
