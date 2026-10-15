import Checkbox from "@/Components/Checkbox";
import TextInput from "@/Components/TextInput";
import { Button } from "@/Components/ui/Button";
import AppLayout from "@/Layouts/AppLayout";
import { Head, Link, useForm } from "@inertiajs/react";
import { FormEventHandler } from "react";

export default function Register() {
    // 1. User මොඩල් එකට ගැළපෙන විදිහට attributes වෙන් කිරීම
    const { data, setData, post, processing, errors, reset } = useForm({
        first_name: "",
        last_name: "",
        email: "",
        password: "",
        password_confirmation: "",
        terms: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route("register"), {
            onFinish: () => reset("password", "password_confirmation"),
        });
    };

    return (
        <AppLayout>
            <Head title="Create Account" />

            <div className="min-h-[calc(100vh-80px)] flex bg-bg">
                {/* ───────── LEFT: Master hero panel with battlestation background ───────── */}
                <div className="hidden lg:flex lg:w-1/2 relative items-center justify-center border-r border-border/40 overflow-hidden">
                    {/* Background battlestation image */}
                    <img
                        src="/images/login-bg.png"
                        alt="ComLink Gaming Setup"
                        className="absolute inset-0 w-full h-full object-cover select-none pointer-events-none"
                    />

                    {/* Dark cinematic overlays for contrast & cyber ambiance */}
                    <div className="absolute inset-0 bg-black/60 backdrop-blur-[1px]" />
                    <div className="absolute inset-0 bg-gradient-to-t from-bg via-transparent to-bg/75" />
                    <div className="absolute inset-0 bg-gradient-to-l from-bg/90 via-transparent to-black/50" />

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
                        className="pointer-events-none absolute -left-6 top-1/2 -translate-y-1/2 select-none font-display text-[14rem] xl:text-[18rem] font-bold leading-none text-transparent [-webkit-text-stroke:1px_rgba(255,255,255,0.05)]"
                    >
                        PC
                    </span>

                    {/* Center content: Large, prominent, centered heading */}
                    <div className="relative z-10 w-full max-w-xl px-8 text-center flex flex-col items-center justify-center">
                        <h2 className="font-display font-black uppercase leading-none tracking-tight text-white drop-shadow-[0_10px_35px_rgba(0,0,0,0.95)]">
                            <span className="block text-3xl sm:text-4xl xl:text-5xl font-semibold tracking-wider text-white/80">
                                Your Next
                            </span>
                            <span className="block text-6xl sm:text-7xl xl:text-8xl my-3 tracking-tight">
                                PC{" "}
                                <span className="bg-gradient-to-r from-cyan-400 via-cyan-300 to-cyan-500 bg-clip-text text-transparent">
                                    Build
                                </span>
                            </span>
                            <span className="block text-4xl sm:text-5xl xl:text-6xl text-white tracking-wide">
                                Starts Here
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

                {/* ───────── RIGHT: Form ───────── */}
                <div className="relative w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-8 md:p-12 xl:p-16 bg-bg overflow-hidden transition-colors duration-300">
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

                    <div className="relative z-10 w-full max-w-lg xl:max-w-xl">
                        {/* Card */}
                        <div className="relative rounded-2xl border border-border/60 bg-card/70 backdrop-blur-sm p-8 sm:p-10 shadow-[0_20px_60px_-20px_rgba(0,0,0,0.5)]">
                            {/* Cyan top accent bar */}
                            <span
                                aria-hidden
                                className="absolute -top-px left-8 h-[3px] w-20 rounded-b bg-accent"
                            />

                            <div className="mb-6">
                                <span className="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.25em] text-accent">
                                    <span className="h-px w-6 bg-accent" />
                                    New Member
                                </span>
                                <h1 className="mt-3 font-display text-4xl sm:text-5xl font-bold text-fg tracking-wide uppercase leading-none">
                                    Create <span className="text-accent">Account</span>
                                </h1>
                                <div className="mt-4 flex items-center gap-2">
                                    <span className="h-1 w-14 rounded-full bg-accent" />
                                    <span className="text-accent font-bold tracking-tighter italic select-none">
                                        ///
                                    </span>
                                </div>
                                <p className="mt-4 text-muted text-sm">
                                    Fill in the details below to get started.
                                </p>
                            </div>

                            <form className="space-y-4" onSubmit={submit}>
                                {/* First Name & Last Name Grid */}
                                <div className="grid grid-cols-2 gap-3.5">
                                    <div className="space-y-1.5">
                                        <TextInput
                                            id="first_name"
                                            name="first_name"
                                            label="First Name"
                                            placeholder="John"
                                            value={data.first_name}
                                            className="mt-1 block w-full bg-bg border-border text-fg focus:border-accent"
                                            isFocused={true}
                                            onChange={(e) =>
                                                setData(
                                                    "first_name",
                                                    e.target.value,
                                                )
                                            }
                                            error={errors.first_name}
                                            required
                                        />
                                    </div>

                                    <div className="space-y-1.5">
                                        <TextInput
                                            id="last_name"
                                            name="last_name"
                                            label="Last Name"
                                            placeholder="Doe"
                                            value={data.last_name}
                                            className="mt-1 block w-full bg-bg border-border text-fg focus:border-accent"
                                            onChange={(e) =>
                                                setData(
                                                    "last_name",
                                                    e.target.value,
                                                )
                                            }
                                            error={errors.last_name}
                                            required
                                        />
                                    </div>
                                </div>

                                {/* Email Field */}
                                <div className="space-y-1.5">
                                    <TextInput
                                        id="email"
                                        type="email"
                                        name="email"
                                        label="Email Address"
                                        placeholder="you@example.com"
                                        value={data.email}
                                        className="mt-1 block w-full bg-bg border-border text-fg focus:border-accent"
                                        autoComplete="username"
                                        onChange={(e) =>
                                            setData("email", e.target.value)
                                        }
                                        error={errors.email}
                                        required
                                    />
                                </div>

                                {/* Password Field */}
                                <div className="space-y-1.5">
                                    <TextInput
                                        id="password"
                                        type="password"
                                        name="password"
                                        label="Password"
                                        placeholder="Create a password"
                                        value={data.password}
                                        className="mt-1 block w-full bg-bg border-border text-fg focus:border-accent"
                                        autoComplete="new-password"
                                        onChange={(e) =>
                                            setData("password", e.target.value)
                                        }
                                        error={errors.password}
                                        required
                                    />
                                </div>

                                {/* Confirm Password Field */}
                                <div className="space-y-1.5">
                                    <TextInput
                                        id="password_confirmation"
                                        type="password"
                                        name="password_confirmation"
                                        label="Confirm Password"
                                        placeholder="Repeat your password"
                                        value={data.password_confirmation}
                                        className="mt-1 block w-full bg-bg border-border text-fg focus:border-accent"
                                        autoComplete="new-password"
                                        onChange={(e) =>
                                            setData(
                                                "password_confirmation",
                                                e.target.value,
                                            )
                                        }
                                        error={errors.password_confirmation}
                                        required
                                    />
                                </div>

                                {/* Terms Checkbox */}
                                <div className="flex items-start gap-2 pt-1 select-none">
                                    <Checkbox
                                        id="terms"
                                        name="terms"
                                        checked={data.terms}
                                        onChange={(e) =>
                                            setData("terms", e.target.checked)
                                        }
                                        required
                                    />
                                    <label
                                        htmlFor="terms"
                                        className="text-xs text-muted cursor-pointer leading-tight"
                                    >
                                        I agree to the{" "}
                                        <Link
                                            href="/terms"
                                            className="text-accent hover:underline font-medium transition-colors"
                                        >
                                            Terms of Service
                                        </Link>{" "}
                                        and{" "}
                                        <Link
                                            href="/privacy"
                                            className="text-accent hover:underline font-medium transition-colors"
                                        >
                                            Privacy Policy
                                        </Link>
                                        .
                                    </label>
                                </div>

                                {/* Submit Button */}
                                <Button
                                    type="submit"
                                    size="lg"
                                    className="w-full uppercase tracking-wider !bg-accent hover:!bg-accent/90 !text-white shadow-lg shadow-accent/30 transition-all hover:-translate-y-0.5"
                                    disabled={processing}
                                >
                                    {processing
                                        ? "Creating Account..."
                                        : "Create Account"}
                                </Button>
                            </form>
                        </div>

                        {/* Switch to Login */}
                        <p className="mt-6 text-center text-sm text-muted">
                            Already have an account?{" "}
                            <Link
                                href={route("login")}
                                className="font-semibold text-accent hover:underline transition-colors"
                            >
                                Sign in
                            </Link>
                        </p>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
