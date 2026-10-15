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
                {/* Left Side Image Section (Premium Look) */}
                <div className="hidden lg:block lg:w-1/2 relative bg-muted/10 border-r border-border/40">
                    <img
                        src="https://images.unsplash.com/photo-1536766820879-059fec98ec0a?auto=format&fit=crop&q=80&w=1000"
                        alt="Fashion Model"
                        className="absolute inset-0 w-full h-full object-cover"
                    />
                    <div className="absolute inset-0 bg-black/20" />
                    <div className="absolute bottom-16 left-16 right-16 text-white z-10">
                        <h2 className="font-display text-4xl font-bold mb-4 tracking-wider text-white">
                            JOIN THE CLUB.
                        </h2>
                        <p className="text-base text-white/80 leading-relaxed font-sans font-light">
                            Create an account to track your orders, save your
                            favorite items, and checkout faster.
                        </p>
                    </div>
                </div>

                {/* Form Section */}
                <div className="w-full lg:w-1/2 flex items-center justify-center p-8 md:p-16 bg-bg transition-colors duration-300">
                    <div className="w-full max-w-md space-y-8">
                        <div className="text-center lg:text-left">
                            <h1 className="font-display text-4xl font-bold text-fg mb-2 tracking-wide">
                                Create Account
                            </h1>
                            <p className="text-muted text-sm">
                                Fill in the details below to get started.
                            </p>
                        </div>

                        <form className="space-y-5" onSubmit={submit}>
                            {/* First Name & Last Name Grid */}
                            <div className="grid grid-cols-2 gap-4">
                                <div className="space-y-1.5">
                                    <TextInput
                                        id="first_name"
                                        name="first_name"
                                        label="First Name"
                                        placeholder="John"
                                        value={data.first_name}
                                        className="mt-1 block w-full bg-card border-border text-fg"
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
                                        className="mt-1 block w-full bg-card border-border text-fg"
                                        onChange={(e) =>
                                            setData("last_name", e.target.value)
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
                                    className="mt-1 block w-full bg-card border-border text-fg"
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
                                    className="mt-1 block w-full bg-card border-border text-fg"
                                    onChange={(e) =>
                                        setData("password", e.target.value)
                                    }
                                    error={errors.password}
                                    required
                                />
                            </div>

                            {/* Confirm Password Field (Breeze backend එකට අනිවාර්යයි) */}
                            <div className="space-y-1.5">
                                <TextInput
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    label="Confirm Password"
                                    placeholder="Repeat your password"
                                    value={data.password_confirmation}
                                    className="mt-1 block w-full bg-card border-border text-fg"
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
                                        className="text-link-main hover:text-link-hover font-medium hover:underline transition-colors duration-200"
                                    >
                                        Terms of Service
                                    </Link>{" "}
                                    and{" "}
                                    <Link
                                        href="/privacy"
                                        className="text-link-main hover:text-link-hover font-medium hover:underline transition-colors duration-200"
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
                                className="w-full uppercase tracking-wider"
                                disabled={processing}
                            >
                                {processing
                                    ? "Creating Account..."
                                    : "Create Account"}
                            </Button>
                        </form>

                        {/* Social Dividers */}
                        {/* <div className="relative py-1">
                            <div className="absolute inset-0 flex items-center">
                                <span className="w-full border-t border-border" />
                            </div>
                            <div className="relative flex justify-center text-xs uppercase tracking-wider">
                                <span className="bg-bg px-3 text-muted">
                                    Or sign up with
                                </span>
                            </div>
                        </div> */}

                        {/* Social Buttons */}
                        {/* <div className="grid grid-cols-2 gap-4">
                            <Button
                                variant="outline"
                                className="w-full text-sm font-medium border-border text-fg hover:bg-muted/10"
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

                        {/* Switch to Login */}
                        <p className="text-center text-sm text-muted">
                            Already have an account?{" "}
                            <Link
                                href={route("login")}
                                className="font-semibold text-link-main hover:text-link-hover transition-colors duration-200 hover:underline"
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
