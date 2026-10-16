import AppLayout from "@/Layouts/AppLayout";
import { PageProps } from "@/types";
import { Head, Link, router, usePage } from "@inertiajs/react";
import {
    User as UserIcon,
    Shield,
    Package,
    Mail,
    Phone,
    Cpu,
    BadgeCheck,
    AlertTriangle,
    Camera,
    Check,
    X,
    Trash2,
    Loader2,
} from "lucide-react";
import React, { useRef, useState } from "react";
import { toast } from "sonner";
import DeleteUserForm from "./Partials/DeleteUserForm";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm";
import UpdateProfileInformationForm from "./Partials/UpdateProfileInformationForm";

export default function Edit({
    mustVerifyEmail,
    status,
    address,
}: PageProps<{
    mustVerifyEmail: boolean;
    status?: string;
    address?: any;
}>) {
    const user = usePage().props.auth.user as any;
    const fullName =
        user?.name ||
        `${user?.first_name || ""} ${user?.last_name || ""}`.trim() ||
        "Valued Customer";

    const fileInputRef = useRef<HTMLInputElement>(null);
    const [avatarFile, setAvatarFile] = useState<File | null>(null);
    const [avatarPreview, setAvatarPreview] = useState<string | null>(null);
    const [isUploading, setIsUploading] = useState(false);

    // Get current avatar url
    const currentAvatarUrl = avatarPreview
        ? avatarPreview
        : user?.avatar_url
          ? user.avatar_url.startsWith("http") ||
            user.avatar_url.startsWith("blob:")
              ? user.avatar_url
              : user.avatar_url.startsWith("/storage/")
                ? user.avatar_url
                : `/storage/${user.avatar_url}`
          : `https://ui-avatars.com/api/?name=${encodeURIComponent(
                fullName,
            )}&color=04202b&background=22d3ee&size=128`;

    // Handle file selection
    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (!file) return;

        // Validation: must be an image
        if (!file.type.startsWith("image/")) {
            toast.error("Please select a valid image file (PNG, JPG, WEBP).");
            return;
        }

        // Validation: max 5MB
        if (file.size > 5 * 1024 * 1024) {
            toast.error("Image file size must be less than 5MB.");
            return;
        }

        setAvatarFile(file);
        setAvatarPreview(URL.createObjectURL(file));
    };

    // Save uploaded avatar
    const handleSaveAvatar = () => {
        if (!avatarFile) return;

        setIsUploading(true);
        const formData = new FormData();
        formData.append("avatar", avatarFile);

        router.post(route("profile.avatar.update"), formData, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                setIsUploading(false);
                setAvatarFile(null);
                setAvatarPreview(null);
                toast.success("Profile photo updated successfully!");
            },
            onError: (errors: any) => {
                setIsUploading(false);
                toast.error(errors?.avatar || "Failed to update profile photo.");
            },
        });
    };

    // Cancel selected preview
    const handleCancelPreview = () => {
        if (avatarPreview) {
            URL.revokeObjectURL(avatarPreview);
        }
        setAvatarFile(null);
        setAvatarPreview(null);
        if (fileInputRef.current) {
            fileInputRef.current.value = "";
        }
    };

    // Remove existing custom avatar
    const handleRemoveAvatar = () => {
        if (
            !confirm(
                "Are you sure you want to remove your custom profile photo?",
            )
        ) {
            return;
        }

        setIsUploading(true);
        router.delete(route("profile.avatar.destroy"), {
            preserveScroll: true,
            onSuccess: () => {
                setIsUploading(false);
                setAvatarFile(null);
                setAvatarPreview(null);
                toast.success("Profile photo removed.");
            },
            onError: () => {
                setIsUploading(false);
                toast.error("Failed to remove profile photo.");
            },
        });
    };

    return (
        <AppLayout>
            <Head title="My Profile - Vicky Store / ComLink" />

            {/* Hidden native file input */}
            <input
                type="file"
                ref={fileInputRef}
                onChange={handleFileChange}
                accept="image/png, image/jpeg, image/jpg, image/webp"
                className="hidden"
            />

            <div className="min-h-screen bg-bg text-fg py-8 sm:py-12">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Breadcrumbs */}
                    <nav className="flex items-center gap-2 text-xs sm:text-sm text-muted mb-6">
                        <Link
                            href="/"
                            className="hover:text-accent transition-colors"
                        >
                            Home
                        </Link>
                        <span>/</span>
                        <span className="text-muted">Account</span>
                        <span>/</span>
                        <span className="text-accent font-semibold">
                            My Profile
                        </span>
                    </nav>

                    {/* User Profile Header Card */}
                    <div className="rounded-3xl border border-border bg-card p-6 sm:p-8 mb-8 shadow-sm">
                        <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
                            {/* Avatar & Basic Info */}
                            <div className="flex flex-col sm:flex-row sm:items-center gap-5">
                                {/* Interactive Avatar Box */}
                                <div className="flex flex-col items-center sm:items-start gap-2.5">
                                    <div className="relative group">
                                        <div
                                            onClick={() =>
                                                !isUploading &&
                                                fileInputRef.current?.click()
                                            }
                                            className="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden border-2 border-accent shadow-md cursor-pointer relative bg-bg/50 transition-transform active:scale-95"
                                            title="Click to change profile photo"
                                        >
                                            <img
                                                src={currentAvatarUrl}
                                                alt={fullName}
                                                className="w-full h-full object-cover"
                                            />

                                            {/* Hover Overlay */}
                                            <div className="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white gap-1">
                                                <Camera className="w-5 h-5 text-accent" />
                                                <span className="text-[10px] font-bold uppercase tracking-wider">
                                                    Upload
                                                </span>
                                            </div>

                                            {/* Uploading Spinner Overlay */}
                                            {isUploading && (
                                                <div className="absolute inset-0 bg-black/75 flex flex-col items-center justify-center text-accent gap-1 z-10">
                                                    <Loader2 className="w-6 h-6 animate-spin" />
                                                    <span className="text-[9px] font-bold uppercase tracking-wider text-white">
                                                        Saving...
                                                    </span>
                                                </div>
                                            )}
                                        </div>

                                        {/* Camera Trigger Badge Button */}
                                        <button
                                            type="button"
                                            onClick={() =>
                                                !isUploading &&
                                                fileInputRef.current?.click()
                                            }
                                            className="absolute -bottom-1.5 -right-1.5 p-1.5 rounded-full bg-accent text-accent-fg shadow-md hover:scale-110 active:scale-95 transition-transform cursor-pointer"
                                            title="Upload new image"
                                            aria-label="Upload new image"
                                        >
                                            <Camera className="w-3.5 h-3.5" />
                                        </button>
                                    </div>

                                    {/* Action buttons if image is staged for upload */}
                                    {avatarFile && (
                                        <div className="flex items-center gap-1.5">
                                            <button
                                                type="button"
                                                onClick={handleSaveAvatar}
                                                disabled={isUploading}
                                                className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-accent text-accent-fg font-bold text-[10px] uppercase tracking-wider hover:opacity-90 transition-opacity shadow-xs cursor-pointer disabled:opacity-50"
                                            >
                                                <Check className="w-3 h-3" />
                                                Save
                                            </button>
                                            <button
                                                type="button"
                                                onClick={handleCancelPreview}
                                                disabled={isUploading}
                                                className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-border bg-card text-muted hover:text-fg font-bold text-[10px] uppercase tracking-wider transition-colors cursor-pointer"
                                            >
                                                <X className="w-3 h-3" />
                                                Cancel
                                            </button>
                                        </div>
                                    )}

                                    {/* Remove button if custom avatar exists & no staged file */}
                                    {!avatarFile && user?.avatar_url && (
                                        <button
                                            type="button"
                                            onClick={handleRemoveAvatar}
                                            disabled={isUploading}
                                            className="inline-flex items-center gap-1 text-[11px] font-medium text-red-500 hover:text-red-400 transition-colors cursor-pointer"
                                        >
                                            <Trash2 className="w-3 h-3" />
                                            Remove photo
                                        </button>
                                    )}
                                </div>

                                {/* User Meta Info */}
                                <div className="min-w-0 text-center sm:text-left">
                                    <div className="flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                                        <h1 className="text-xl sm:text-2xl md:text-3xl font-extrabold font-display uppercase tracking-tight text-fg truncate">
                                            {fullName}
                                        </h1>
                                        {/* {user?.customer_id && (
                                            <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-accent/10 text-accent border border-accent/25">
                                                {user.customer_id}
                                            </span>
                                        )} */}
                                    </div>

                                    <div className="flex flex-wrap items-center justify-center sm:justify-start gap-4 text-xs sm:text-sm text-muted mt-2">
                                        <span className="flex items-center gap-1.5 truncate">
                                            <Mail className="w-3.5 h-3.5 text-accent shrink-0" />
                                            {user?.email}
                                        </span>
                                        {user?.phone && (
                                            <span className="flex items-center gap-1.5">
                                                <Phone className="w-3.5 h-3.5 text-accent shrink-0" />
                                                {user.phone}
                                            </span>
                                        )}
                                    </div>

                                    <p className="text-[11px] text-muted/70 mt-1">
                                        Click on the photo to upload JPG, PNG,
                                        or WEBP (max 5MB).
                                    </p>
                                </div>
                            </div>

                            {/* Quick Navigation Action Buttons */}
                            <div className="flex flex-wrap items-center justify-center md:justify-end gap-3 self-center md:self-auto border-t md:border-t-0 border-border/60 pt-4 md:pt-0">
                                <Link
                                    href="/orders"
                                    className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-card border border-border text-fg hover:border-accent hover:text-accent font-bold text-xs uppercase tracking-wider shadow-xs transition-all active:scale-95 cursor-pointer"
                                >
                                    <Package className="w-4 h-4 text-accent" />
                                    My Orders
                                </Link>

                                <Link
                                    href="/build-my-pc"
                                    className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-accent text-accent-fg font-bold text-xs uppercase tracking-wider shadow-md hover:opacity-90 transition-all active:scale-95 cursor-pointer"
                                >
                                    <Cpu className="w-4 h-4" />
                                    Build My PC
                                </Link>
                            </div>
                        </div>
                    </div>

                    {/* Forms Container */}
                    <div className="space-y-8">
                        {/* Profile Information Card */}
                        <div className="rounded-3xl border border-border bg-card p-6 sm:p-8 shadow-sm">
                            <div className="flex items-center gap-3 pb-4 mb-6 border-b border-border">
                                <div className="w-10 h-10 rounded-xl bg-accent/10 text-accent flex items-center justify-center shrink-0">
                                    <UserIcon className="w-5 h-5" />
                                </div>
                                <div>
                                    <h2 className="text-lg font-bold font-display uppercase tracking-wide text-fg">
                                        Profile Information
                                    </h2>
                                    <p className="text-xs text-muted">
                                        Update your personal details, contact
                                        numbers, and login email address.
                                    </p>
                                </div>
                            </div>

                            <UpdateProfileInformationForm
                                mustVerifyEmail={mustVerifyEmail}
                                status={status}
                                address={address}
                            />
                        </div>

                        {/* Password & Security Card */}
                        <div className="rounded-3xl border border-border bg-card p-6 sm:p-8 shadow-sm">
                            <div className="flex items-center gap-3 pb-4 mb-6 border-b border-border">
                                <div className="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center shrink-0">
                                    <Shield className="w-5 h-5" />
                                </div>
                                <div>
                                    <h2 className="text-lg font-bold font-display uppercase tracking-wide text-fg">
                                        Security & Password
                                    </h2>
                                    <p className="text-xs text-muted">
                                        Ensure your account is protected with a
                                        secure and unique password.
                                    </p>
                                </div>
                            </div>

                            <UpdatePasswordForm />
                        </div>

                        {/* Danger Zone: Account Deletion */}
                        <div className="rounded-3xl border border-red-500/25 bg-red-500/5 p-6 sm:p-8 shadow-sm">
                            <div className="flex items-center gap-3 pb-4 mb-6 border-b border-red-500/20">
                                <div className="w-10 h-10 rounded-xl bg-red-500/10 text-red-500 flex items-center justify-center shrink-0">
                                    <AlertTriangle className="w-5 h-5" />
                                </div>
                                <div>
                                    <h2 className="text-lg font-bold font-display uppercase tracking-wide text-red-500">
                                        Danger Zone
                                    </h2>
                                    <p className="text-xs text-muted">
                                        Permanently delete your store account
                                        and associated personal data.
                                    </p>
                                </div>
                            </div>

                            <DeleteUserForm />
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
