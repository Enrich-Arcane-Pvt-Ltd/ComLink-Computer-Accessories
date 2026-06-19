import TextArea from "@/Components/TextArea";
import TextInput from "@/Components/TextInput";
import { Button } from "@/Components/ui/Button";
import AppLayout from "@/Layouts/AppLayout";
import { Head, useForm, usePage } from "@inertiajs/react";
import { Mail, MapPin, Phone } from "lucide-react";
import React, { useEffect } from "react";
import { toast } from "sonner";

export default function Contact() {
    // Laravel පැත්තෙන් එන flash messages (session) කියවා ගැනීම
    const { flash } = usePage().props as any;

    // Inertia useForm Hook එක (Login එකේ තිබ්බ විදිහටම)
    const { data, setData, post, processing, errors, reset, clearErrors } =
        useForm({
            name: "",
            email: "",
            subject: "",
            message: "",
            phone: "",
        });

    // Backend එකෙන් success message එකක් ආවොත් Toast එකක් පෙන්වා Form එක reset කිරීම
    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success);
            reset(); // Form එකේ ඩේටා Clear කිරීම
        }
    }, [flash]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        clearErrors();
        post(route("contact.store"), {
            preserveScroll: true, // පිටුව උඩට පනින්නේ නැතුව තියාගන්න
        });
    };

    return (
        <AppLayout>
            <Head title="Contact Us" />

            <div className="bg-bg min-h-screen">
                {/* Page Header */}
                <div className="bg-muted/10 py-16 text-center">
                    <h1 className="font-display text-4xl font-bold text-fg mb-4 uppercase tracking-wide">
                        CONTACT US
                    </h1>
                    <p className="text-muted max-w-2xl mx-auto px-4">
                        Have a question or feedback? We'd love to hear from you.
                        Fill out the form below or use our contact details.
                    </p>
                </div>

                <div className="container mx-auto px-4 md:px-6 py-16">
                    <div className="flex flex-col lg:flex-row gap-12">
                        {/* Contact Info */}
                        <div className="lg:w-1/3 space-y-8">
                            <div>
                                <h2 className="font-display text-2xl font-bold text-fg mb-6">
                                    Get In Touch
                                </h2>
                                <p className="text-muted leading-relaxed">
                                    We're here to help. Reach out to us via any
                                    of these channels or fill out the form.
                                </p>
                            </div>

                            <div className="space-y-6">
                                <div className="flex gap-4">
                                    <div className="w-12 h-12 bg-muted/10 border border-border flex items-center justify-center text-primary-bg flex-shrink-0">
                                        <MapPin className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <h4 className="font-bold text-fg text-sm uppercase tracking-wider mb-1">
                                            Our Location
                                        </h4>
                                        <p className="text-sm text-muted leading-relaxed">
                                            123 Fashion Avenue,
                                            <br />
                                            Suite 456, New York, NY 10001
                                        </p>
                                    </div>
                                </div>

                                <div className="flex gap-4">
                                    <div className="w-12 h-12 bg-muted/10 border border-border flex items-center justify-center text-primary-bg flex-shrink-0">
                                        <Phone className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <h4 className="font-bold text-fg text-sm uppercase tracking-wider mb-1">
                                            Phone Number
                                        </h4>
                                        <p className="text-sm text-muted">
                                            +94 (77) 123-4567
                                        </p>
                                        <p className="text-sm text-muted">
                                            +94 (11) 765-4321
                                        </p>
                                    </div>
                                </div>

                                <div className="flex gap-4">
                                    <div className="w-12 h-12 bg-muted/10 border border-border flex items-center justify-center text-primary-bg flex-shrink-0">
                                        <Mail className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <h4 className="font-bold text-fg text-sm uppercase tracking-wider mb-1">
                                            Email Address
                                        </h4>
                                        <p className="text-sm text-muted">
                                            support@vickystore.com
                                        </p>
                                    </div>
                                </div>
                            </div>
                            {/* Map Placeholder */}
                            <div className="w-full h-64 bg-muted/20 border border-border flex items-center justify-center">
                                <span className="text-muted font-medium">
                                    Interactive Map Placeholder
                                </span>
                            </div>
                        </div>

                        {/* Contact Form */}
                        <div className="lg:w-2/3 bg-card p-8 md:p-12 border border-border shadow-sm">
                            <h2 className="font-display text-2xl font-bold text-fg mb-6">
                                Send Us A Message
                            </h2>
                            <form onSubmit={handleSubmit} className="space-y-6">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {/* Name Input */}
                                    <div className="space-y-1.5">
                                        <TextInput
                                            id="name"
                                            type="text"
                                            name="name"
                                            label="Your Name"
                                            placeholder="John Doe"
                                            value={data.name}
                                            // className="mt-1 block w-full bg-card border-border text-fg"
                                            onChange={(e) =>
                                                setData("name", e.target.value)
                                            }
                                            error={errors.name}
                                            required
                                        />
                                    </div>

                                    {/* Email Input */}
                                    <div className="space-y-1.5">
                                        <TextInput
                                            id="email"
                                            type="email"
                                            name="email"
                                            label="Email Address"
                                            placeholder="you@example.com"
                                            value={data.email}
                                            // className="mt-1 block w-full bg-card border-border text-fg"
                                            onChange={(e) =>
                                                setData("email", e.target.value)
                                            }
                                            error={errors.email}
                                            required
                                        />
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {/* Phone Input */}
                                    <div className="space-y-1.5">
                                        <TextInput
                                            id="phone"
                                            type="text"
                                            name="phone"
                                            label="Phone / WhatsApp"
                                            placeholder="+947XXXXXXXX"
                                            value={data.phone}
                                            // className="mt-1 block w-full bg-card border-border text-fg"
                                            onChange={(e) =>
                                                setData("phone", e.target.value)
                                            }
                                            error={errors.phone}
                                            required
                                        />
                                    </div>

                                    {/* Subject Input */}
                                    <div className="space-y-1.5">
                                        <TextInput
                                            id="subject"
                                            type="text"
                                            name="subject"
                                            label="Subject"
                                            placeholder="How can we help?"
                                            value={data.subject}
                                            // className="mt-1 block w-full bg-card border-border text-fg"
                                            onChange={(e) =>
                                                setData(
                                                    "subject",
                                                    e.target.value,
                                                )
                                            }
                                            error={errors.subject}
                                            required
                                        />
                                    </div>
                                </div>

                                {/* Message Input */}
                                <div className="space-y-1.5">
                                    <TextArea
                                        id="message"
                                        label="Message"
                                        placeholder="Write your message here..."
                                        // className="mt-1 block w-full bg-card border-border text-fg"
                                        value={data.message}
                                        onChange={(e) =>
                                            setData("message", e.target.value)
                                        }
                                        error={errors.message}
                                        required // <-- required pass කරපු ගමන් ලේබල් එක ළඟ රතු තරුව වැටේවි
                                    />
                                </div>

                                {/* Submit Button */}
                                <Button
                                    type="submit"
                                    size="lg"
                                    disabled={processing}
                                    className="bg-primary-600 hover:bg-primary-900"
                                >
                                    {processing ? "Sending..." : "Send Message"}
                                </Button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
