import Checkbox from "@/Components/Checkbox";
import TextInput from "@/Components/TextInput";
import { Button } from "@/Components/ui/Button";
import AppLayout from "@/Layouts/AppLayout";
import { Head, Link, useForm } from "@inertiajs/react";
import { FormEventHandler } from "react";

export default function Login({
    status,
    canResetPassword,
}: {
    status?: string;
    canResetPassword: boolean;
}) {
    // 1. Inertia Form Hooks
    const { data, setData, post, processing, errors, reset } = useForm({
        email: "",
        password: "",
        remember: false as boolean,
    });

    // 2. Form Submission
    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route("login"), {
            onFinish: () => reset("password"),
        });
    };

    return (
        <AppLayout>
            <Head title="Log In" />

            <div className="min-h-[calc(100vh-80px)] flex bg-bg">
                {/* ───────── LEFT: Form ───────── */}
                <div className="relative w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-10 md:p-16 bg-bg overflow-hidden transition-colors duration-300">
                    {/* Decorative: soft cyan glow + subtle blueprint grid */}
                    <div
                        aria-hidden
                        className="pointer-events-none absolute -top-32 -left-32 h-96 w-96 rounded-full bg-cyan-500/10 blur-3xl"
                    />
                    <div
                        aria-hidden
                        className="pointer-events-none absolute -bottom-40 -right-24 h-96 w-96 rounded-full bg-cyan-500/5 blur-3xl"
                    />
                    <div
                        aria-hidden="true"
                        className="pointer-events-none absolute inset-0 opacity-[0.05]"
                        style={{
                            backgroundImage:
                                "linear-gradient(rgba(34,211,238,0.08) 1px, transparent 1px), linear-gradient(90deg, rgba(34,211,238,0.08) 1px, transparent 1px)",
                            backgroundSize: "44px 44px",
                        }}
                    />

                    <div className="relative z-10 w-full max-w-md">
                        {/* Card */}
                        <div className="relative rounded-2xl border border-border/60 bg-card/70 backdrop-blur-sm p-8 sm:p-10 shadow-[0_20px_60px_-20px_rgba(0,0,0,0.5)]">
                            {/* Red top accent bar */}
                            <span
                                aria-hidden
                                className="absolute -top-px left-8 h-[3px] w-20 rounded-b bg-accent"
                            />

                            <div className="mb-8">
                                <span className="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.25em] text-accent">
                                    <span className="h-px w-6 bg-accent" />
                                    Member Access
                                </span>
                                <h1 className="mt-3 font-display text-4xl sm:text-5xl font-bold text-fg tracking-wide uppercase leading-none">
                                    Welcome{" "}
                                    <span className="text-accent">Back</span>
                                </h1>
                                {/* red line + slashes (matches hero style) */}
                                <div className="mt-4 flex items-center gap-2">
                                    <span className="h-1 w-14 rounded-full bg-accent" />
                                    <span className="text-accent font-bold tracking-tighter italic select-none">
                                        ///
                                    </span>
                                </div>
                                <p className="mt-4 text-muted text-sm">
                                    Please enter your details to sign in.
                                </p>
                            </div>

                            {status && (
                                <div className="mb-4 text-sm font-medium text-green-600 bg-green-50 dark:bg-green-950/20 p-3 rounded">
                                    {status}
                                </div>
                            )}

                            <form className="space-y-6" onSubmit={submit}>
                                {/* Email Field */}
                                <div className="space-y-1.5">
                                    <TextInput
                                        id="email"
                                        label="Email Address"
                                        type="email"
                                        name="email"
                                        placeholder="Enter your email"
                                        value={data.email}
                                        className="mt-1 block w-full bg-bg border-border text-fg focus:border-accent"
                                        autoComplete="username"
                                        isFocused={true}
                                        onChange={(e) =>
                                            setData("email", e.target.value)
                                        }
                                        required
                                        error={errors.email}
                                    />
                                </div>

                                {/* Password Field */}
                                <div className="space-y-1.5">
                                    <TextInput
                                        label="Password"
                                        id="password"
                                        type="password"
                                        name="password"
                                        placeholder="••••••••"
                                        value={data.password}
                                        className="mt-1 block w-full bg-bg border-border text-fg focus:border-accent"
                                        autoComplete="current-password"
                                        onChange={(e) =>
                                            setData("password", e.target.value)
                                        }
                                        required
                                        error={errors.password}
                                    />

                                    <div className="flex items-center justify-between pt-1">
                                        {/* Remember Me */}
                                        <label className="flex items-center cursor-pointer select-none">
                                            <Checkbox
                                                name="remember"
                                                checked={data.remember}
                                                onChange={(e) =>
                                                    setData(
                                                        "remember",
                                                        e.target.checked,
                                                    )
                                                }
                                            />
                                            <span className="ms-2 text-xs text-muted hover:text-fg transition-colors">
                                                Remember me
                                            </span>
                                        </label>

                                        {/* Forgot Password */}
                                        {canResetPassword && (
                                            <Link
                                                href={route("password.request")}
                                                className="text-xs text-muted hover:text-accent font-medium transition-colors hover:underline"
                                            >
                                                Forgot password?
                                            </Link>
                                        )}
                                    </div>
                                </div>

                                {/* Submit Button */}
                                <Button
                                    type="submit"
                                    size="lg"
                                    className="w-full uppercase tracking-wider !bg-accent hover:!bg-accent/90 !text-white shadow-lg shadow-accent/30 transition-all hover:-translate-y-0.5"
                                    disabled={processing}
                                >
                                    {processing ? "Signing In..." : "Sign In"}
                                </Button>
                            </form>

                            {/* Trust row */}
                            {/* <div className="mt-8 grid grid-cols-3 gap-2 border-t border-border/50 pt-6 text-center">
                                {[
                                    ["100%", "Genuine Parts"],
                                    ["Fast", "Delivery"],
                                    ["Secure", "Checkout"],
                                ].map(([top, bottom]) => (
                                    <div key={bottom}>
                                        <div className="font-display text-sm font-bold tracking-wide text-fg uppercase">
                                            {top}
                                        </div>
                                        <div className="text-[10px] uppercase tracking-wider text-muted">
                                            {bottom}
                                        </div>
                                    </div>
                                ))}
                            </div> */}
                        </div>

                        {/* Switch to Register */}
                        <p className="mt-6 text-center text-sm text-muted">
                            Don't have an account?{" "}
                            <Link
                                href={route("register")}
                                className="font-semibold text-accent hover:underline transition-colors"
                            >
                                Sign up
                            </Link>
                        </p>
                    </div>
                </div>

                {/* ───────── RIGHT: Master hero panel with battlestation background ───────── */}
                <div
                    className="hidden lg:flex lg:w-1/2 relative items-center justify-center border-l border-border/40 overflow-hidden"
                >
                    {/* Background battlestation image (instant fallback base while video loads) */}
                    <img
                        src="/images/login-bg.png"
                        alt="ComLink Gaming Setup"
                        className="absolute inset-0 w-full h-full object-cover select-none pointer-events-none"
                    />

                    {/* Ambient background video on top of the image */}
                    <video
                        autoPlay
                        loop
                        muted
                        playsInline
                        poster="/images/login-bg.png"
                        className="absolute inset-0 w-full h-full object-cover select-none pointer-events-none"
                    >
                        <source src="/videos/login_video.mp4" type="video/mp4" />
                    </video>

                    {/* Dark cinematic overlays for contrast & cyber ambiance */}
                    <div className="absolute inset-0 bg-black/65 backdrop-blur-[1px]" />
                    <div className="absolute inset-0 bg-gradient-to-t from-bg via-transparent to-bg/75" />
                    <div className="absolute inset-0 bg-gradient-to-r from-bg/90 via-transparent to-black/50" />

                    {/* Ambient cyan glow */}
                    <div
                        aria-hidden
                        className="pointer-events-none absolute left-1/2 top-1/2 h-[420px] w-[550px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-cyan-500/15 blur-3xl"
                    />

                    {/* Subtle blueprint grid overlay matching About & Contact */}
                    <div
                        aria-hidden
                        className="absolute inset-0 opacity-20 pointer-events-none"
                        style={{
                            backgroundImage:
                                "linear-gradient(rgba(34,211,238,0.08) 1px, transparent 1px), linear-gradient(90deg, rgba(34,211,238,0.08) 1px, transparent 1px)",
                            backgroundSize: "44px 44px",
                        }}
                    />

                    {/* Watermark */}
                    <span
                        aria-hidden
                        className="pointer-events-none absolute -right-6 top-1/2 -translate-y-1/2 select-none font-display text-[14rem] xl:text-[18rem] font-bold leading-none text-transparent [-webkit-text-stroke:1px_rgba(255,255,255,0.05)]"
                    >
                        PC
                    </span>

                    {/* Center content: Large, prominent, centered heading */}
                    <div className="relative z-30 w-full max-w-xl px-8 text-center flex flex-col items-center justify-center pointer-events-none">
                        <h2 className="font-display font-black uppercase leading-none tracking-tight text-white drop-shadow-[0_10px_35px_rgba(0,0,0,0.95)]">
                            <span className="block text-3xl sm:text-4xl xl:text-5xl font-semibold tracking-wider text-white/80">
                                Ready To
                            </span>
                            <span className="block text-6xl sm:text-7xl xl:text-8xl my-3 tracking-tight">
                                Level{" "}
                                <span className="bg-gradient-to-r from-cyan-400 via-cyan-300 to-cyan-500 bg-clip-text text-transparent">
                                    Up
                                </span>
                            </span>
                            <span className="block text-4xl sm:text-5xl xl:text-6xl text-white tracking-wide">
                                Your Rig?
                            </span>
                        </h2>

                        {/* Centered Cyan line + /// */}
                        <div className="mt-6 flex items-center justify-center gap-3">
                            <span className="h-1.5 w-16 rounded-full bg-cyan-500 shadow-lg shadow-cyan-500/50" />
                            <span className="font-black italic tracking-tighter text-cyan-400 select-none text-xl">
                                ///
                            </span>
                            <span className="h-1.5 w-16 rounded-full bg-cyan-500 shadow-lg shadow-cyan-500/50" />
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}