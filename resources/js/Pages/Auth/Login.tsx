import Checkbox from "@/Components/Checkbox";
import TextInput from "@/Components/TextInput";
import { Button } from "@/Components/ui/Button"; // ඔයාගේ Custom Button එක
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
    // 1. Inertia Form Hooks සැකසුම
    const { data, setData, post, processing, errors, reset } = useForm({
        email: "",
        password: "",
        remember: false as boolean,
    });

    // 2. Form Submission එක පාලනය කිරීම
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
                {/* Form Section */}
                <div className="w-full lg:w-1/2 flex items-center justify-center p-8 md:p-16 bg-bg transition-colors duration-300">
                    <div className="w-full max-w-md space-y-8">
                        <div className="text-center lg:text-left">
                            <h1 className="font-display text-4xl font-bold text-fg mb-2 tracking-wide">
                                Welcome Back
                            </h1>
                            <p className="text-muted text-sm">
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
                                    className="mt-1 block w-full bg-card border-border text-fg"
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
                                    className="mt-1 block w-full bg-card border-border text-fg"
                                    autoComplete="current-password"
                                    onChange={(e) =>
                                        setData("password", e.target.value)
                                    }
                                    required
                                    error={errors.password}
                                />

                                <div className="flex items-center justify-between pt-1">
                                    {/* Remember Me Checkbox */}
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

                                    {/* Forgot Password Link */}
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
                                className="w-full uppercase tracking-wider"
                                disabled={processing}
                            >
                                {processing ? "Signing In..." : "Sign In"}
                            </Button>
                        </form>

                        {/* Social Login Dividers */}
                        {/* <div className="relative py-2">
                            <div className="absolute inset-0 flex items-center">
                                <span className="w-full border-t border-border" />
                            </div>
                            <div className="relative flex justify-center text-xs uppercase tracking-wider">
                                <span className="bg-bg px-3 text-muted">
                                    Or continue with
                                </span>
                            </div>
                        </div> */}

                        {/* Social Buttons */}
                        {/* <div className="grid grid-cols-2 gap-4">
                            <Button
                                variant="outline"
                                className="w-full text-sm font-medium border-border text-fg bg-bg hover:bg-muted/10"
                            >
                                Google
                            </Button>
                            <Button
                                variant="outline"
                                className="w-full text-sm font-medium border-border text-fg hover:bg-muted/10"
                            >
                                Apple
                            </Button>
                        </div> */}

                        {/* Switch to Register */}
                        <p className="text-center text-sm text-muted">
                            Don't have an account?{" "}
                            <Link
                                href={route("register")}
                                className="font-semibold text-muted/70 hover:text-accent transition-colors hover:underline"
                            >
                                Sign up
                            </Link>
                        </p>
                    </div>
                </div>

                {/* Right Side Image Section (Premium Look) */}
                <div className="hidden lg:block lg:w-1/2 relative bg-muted/10 border-l border-border/40">
                    <img
                        src="https://images.unsplash.com/photo-1552374196-1ab2a1c593e8?auto=format&fit=crop&q=80&w=1000"
                        alt="Fashion Model"
                        className="absolute inset-0 w-full h-full object-cover"
                    />
                    <div className="absolute inset-0 bg-black/30 backdrop-blur-[1px]" />
                    <div className="absolute bottom-16 left-16 right-16 text-white z-10">
                        <h2 className="font-display text-4xl font-bold mb-4 tracking-wider text-white">
                            ELEVATE YOUR STYLE.
                        </h2>
                        <p className="text-base text-white/80 leading-relaxed font-sans font-light">
                            Join the VICKY STORE community and get access to
                            exclusive drops, personalized recommendations, and
                            more.
                        </p>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
