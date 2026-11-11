import TextInput from "@/Components/TextInput";
import { Button } from "@/Components/ui/Button";
import AppLayout from "@/Layouts/AppLayout";
import { Head, Link, useForm } from "@inertiajs/react";
import { FormEventHandler } from "react";

export default function ResetPassword({
    token,
    email,
}: {
    token: string;
    email: string;
}) {
    const { data, setData, post, processing, errors, reset } = useForm({
        token: token,
        email: email,
        password: "",
        password_confirmation: "",
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route("password.store"), {
            onFinish: () => reset("password", "password_confirmation"),
        });
    };

    return (
        <AppLayout>
            <Head title="Reset Password - ComLink" />

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
                                    Secure Authentication
                                </span>
                                <h1 className="mt-3 font-display text-4xl sm:text-5xl font-bold text-fg tracking-wide uppercase leading-none">
                                    Set New <span className="text-accent">Password</span>
                                </h1>
                                {/* Red line + slashes */}
                                <div className="mt-4 flex items-center gap-2">
                                    <span className="h-1 w-14 rounded-full bg-accent" />
                                    <span className="text-accent font-bold tracking-tighter italic select-none">
                                        ///
                                    </span>
                                </div>
                                <p className="mt-4 text-muted text-sm leading-relaxed">
                                    Enter your new secure password below to complete the account recovery.
                                </p>
                            </div>

                            <form className="space-y-6" onSubmit={submit}>
                                {/* Email Field */}
                                <div className="space-y-1.5">
                                    <TextInput
                                        id="email"
                                        label="Email Address"
                                        type="email"
                                        name="email"
                                        value={data.email}
                                        className="mt-1 block w-full bg-bg border-border text-fg focus:border-accent"
                                        autoComplete="username"
                                        onChange={(e) =>
                                            setData("email", e.target.value)
                                        }
                                        required
                                        error={errors.email}
                                    />
                                </div>

                                {/* New Password Field */}
                                <div className="space-y-1.5">
                                    <TextInput
                                        label="New Password"
                                        id="password"
                                        type="password"
                                        name="password"
                                        placeholder="••••••••"
                                        value={data.password}
                                        className="mt-1 block w-full bg-bg border-border text-fg focus:border-accent"
                                        autoComplete="new-password"
                                        isFocused={true}
                                        onChange={(e) =>
                                            setData("password", e.target.value)
                                        }
                                        required
                                        error={errors.password}
                                    />
                                </div>

                                {/* Confirm Password Field */}
                                <div className="space-y-1.5">
                                    <TextInput
                                        label="Confirm New Password"
                                        id="password_confirmation"
                                        type="password"
                                        name="password_confirmation"
                                        placeholder="••••••••"
                                        value={data.password_confirmation}
                                        className="mt-1 block w-full bg-bg border-border text-fg focus:border-accent"
                                        autoComplete="new-password"
                                        onChange={(e) =>
                                            setData(
                                                "password_confirmation",
                                                e.target.value,
                                            )
                                        }
                                        required
                                        error={errors.password_confirmation}
                                    />
                                </div>

                                {/* Submit Button */}
                                <Button
                                    type="submit"
                                    size="lg"
                                    className="w-full uppercase tracking-wider !bg-accent hover:!bg-accent/90 !text-white shadow-lg shadow-accent/30 transition-all hover:-translate-y-0.5"
                                    disabled={processing}
                                >
                                    {processing
                                        ? "Resetting Password..."
                                        : "Reset Password"}
                                </Button>
                            </form>

                            {/* Back to Sign In Link */}
                            <div className="mt-8 pt-6 border-t border-border/50 text-center">
                                <Link
                                    href={route("login")}
                                    className="inline-flex items-center gap-2 text-sm text-muted hover:text-accent font-medium transition-colors group"
                                >
                                    <svg
                                        className="w-4 h-4 transition-transform group-hover:-translate-x-1"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M10 19l-7-7m0 0l7-7m-7 7h18"
                                        />
                                    </svg>
                                    Back to Sign In
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                {/* ───────── RIGHT: Master hero panel with battlestation background ───────── */}
                <div className="hidden lg:flex lg:w-1/2 relative items-center justify-center border-l border-border/40 overflow-hidden">
                    {/* Background battlestation image */}
                    <img
                        src="/images/login-bg.png"
                        alt="ComLink Gaming Setup"
                        className="absolute inset-0 w-full h-full object-cover select-none pointer-events-none"
                    />

                    {/* Dark cinematic overlays for contrast & cyber ambiance */}
                    <div className="absolute inset-0 bg-black/70 backdrop-blur-[1px]" />
                    <div className="absolute inset-0 bg-gradient-to-t from-bg via-transparent to-bg/75" />
                    <div className="absolute inset-0 bg-gradient-to-r from-bg/90 via-transparent to-black/50" />

                    {/* Ambient cyan glow */}
                    <div
                        aria-hidden
                        className="pointer-events-none absolute left-1/2 top-1/2 h-[420px] w-[550px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-cyan-500/15 blur-3xl"
                    />

                    {/* Subtle blueprint grid overlay */}
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
                        LOCK
                    </span>

                    {/* Center content */}
                    <div className="relative z-30 w-full max-w-xl px-8 text-center flex flex-col items-center justify-center pointer-events-none">
                        <h2 className="font-display font-black uppercase leading-none tracking-tight text-white drop-shadow-[0_10px_35px_rgba(0,0,0,0.95)]">
                            <span className="block text-3xl sm:text-4xl xl:text-5xl font-semibold tracking-wider text-white/80">
                                Secure
                            </span>
                            <span className="block text-6xl sm:text-7xl xl:text-8xl my-3 tracking-tight">
                                Your{" "}
                                <span className="bg-gradient-to-r from-cyan-400 via-cyan-300 to-cyan-500 bg-clip-text text-transparent">
                                    Account
                                </span>
                            </span>
                            <span className="block text-4xl sm:text-5xl xl:text-6xl text-white tracking-wide">
                                Level Up Security
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
