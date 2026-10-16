import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import { Transition } from '@headlessui/react';
import { Link, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { toast } from 'sonner';
import { Check, Mail, MapPin, Phone, User as UserIcon } from 'lucide-react';

export default function UpdateProfileInformation({
    mustVerifyEmail,
    status,
    address,
    className = '',
}: {
    mustVerifyEmail: boolean;
    status?: string;
    address?: any;
    className?: string;
}) {
    const user = usePage().props.auth.user as any;

    const { data, setData, patch, errors, processing, recentlySuccessful } =
        useForm({
            first_name: user?.first_name || '',
            last_name: user?.last_name || '',
            email: user?.email || '',
            phone: user?.phone || '',
            secondary_phone: user?.secondary_phone || '',
            address_line_1: address?.address_line_1 || '',
            address_line_2: address?.address_line_2 || '',
            city: address?.city || '',
            postal_code: address?.postal_code || '',
            state: address?.state || '',
            country: address?.country || 'Sri Lanka',
        });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        patch(route('profile.update'), {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Profile and address details updated successfully!');
            },
        });
    };

    return (
        <section className={className}>
            <form onSubmit={submit} className="space-y-6">
                {/* First Name & Last Name Grid */}
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <InputLabel htmlFor="first_name" value="First Name" />
                        <TextInput
                            id="first_name"
                            className="mt-1.5"
                            value={data.first_name}
                            onChange={(e) => setData('first_name', e.target.value)}
                            required
                            placeholder="John"
                            autoComplete="given-name"
                        />
                        <InputError className="mt-1.5" message={errors.first_name} />
                    </div>

                    <div>
                        <InputLabel htmlFor="last_name" value="Last Name" />
                        <TextInput
                            id="last_name"
                            className="mt-1.5"
                            value={data.last_name}
                            onChange={(e) => setData('last_name', e.target.value)}
                            placeholder="Doe"
                            autoComplete="family-name"
                        />
                        <InputError className="mt-1.5" message={errors.last_name} />
                    </div>
                </div>

                {/* Email Address */}
                <div>
                    <InputLabel htmlFor="email" value="Email Address" />
                    <TextInput
                        id="email"
                        type="email"
                        className="mt-1.5"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        required
                        placeholder="john@example.com"
                        autoComplete="email"
                    />
                    <InputError className="mt-1.5" message={errors.email} />
                </div>

                {/* Phone Numbers Grid */}
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <InputLabel htmlFor="phone" value="Primary Phone" />
                        <TextInput
                            id="phone"
                            type="tel"
                            className="mt-1.5"
                            value={data.phone}
                            onChange={(e) => setData('phone', e.target.value)}
                            placeholder="077 123 4567"
                            autoComplete="tel"
                        />
                        <InputError className="mt-1.5" message={errors.phone} />
                    </div>

                    <div>
                        <InputLabel htmlFor="secondary_phone" value="Secondary Phone (Optional)" />
                        <TextInput
                            id="secondary_phone"
                            type="tel"
                            className="mt-1.5"
                            value={data.secondary_phone}
                            onChange={(e) => setData('secondary_phone', e.target.value)}
                            placeholder="071 987 6543"
                            autoComplete="tel"
                        />
                        <InputError className="mt-1.5" message={errors.secondary_phone} />
                    </div>
                </div>

                {/* Address Details Section */}
                <div className="pt-6 border-t border-border space-y-4">
                    <div className="flex items-center gap-2">
                        <MapPin className="w-4 h-4 text-accent" />
                        <h3 className="text-sm font-bold uppercase tracking-wider text-fg font-display">
                            Delivery & Shipping Address
                        </h3>
                    </div>
                    <p className="text-xs text-muted">
                        Your default shipping address for orders, express courier dispatch, and billing.
                    </p>

                    {/* Street Address Line 1 */}
                    <div>
                        <InputLabel htmlFor="address_line_1" value="Street Address (Line 1)" />
                        <TextInput
                            id="address_line_1"
                            className="mt-1.5"
                            value={data.address_line_1}
                            onChange={(e) => setData('address_line_1', e.target.value)}
                            placeholder="e.g. 123 Galle Road, Bambalapitiya"
                            autoComplete="address-line1"
                        />
                        <InputError className="mt-1.5" message={errors.address_line_1} />
                    </div>

                    {/* Street Address Line 2 */}
                    <div>
                        <InputLabel htmlFor="address_line_2" value="Apartment, Suite, Unit, etc. (Optional)" />
                        <TextInput
                            id="address_line_2"
                            className="mt-1.5"
                            value={data.address_line_2}
                            onChange={(e) => setData('address_line_2', e.target.value)}
                            placeholder="e.g. Apt 4B, 3rd Floor"
                            autoComplete="address-line2"
                        />
                        <InputError className="mt-1.5" message={errors.address_line_2} />
                    </div>

                    {/* City, State & Postal Code Grid */}
                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <InputLabel htmlFor="city" value="City / Town" />
                            <TextInput
                                id="city"
                                className="mt-1.5"
                                value={data.city}
                                onChange={(e) => setData('city', e.target.value)}
                                placeholder="Colombo"
                                autoComplete="address-level2"
                            />
                            <InputError className="mt-1.5" message={errors.city} />
                        </div>

                        <div>
                            <InputLabel htmlFor="state" value="State / Province" />
                            <TextInput
                                id="state"
                                className="mt-1.5"
                                value={data.state}
                                onChange={(e) => setData('state', e.target.value)}
                                placeholder="Western Province"
                                autoComplete="address-level1"
                            />
                            <InputError className="mt-1.5" message={errors.state} />
                        </div>

                        <div>
                            <InputLabel htmlFor="postal_code" value="Postal Code" />
                            <TextInput
                                id="postal_code"
                                className="mt-1.5"
                                value={data.postal_code}
                                onChange={(e) => setData('postal_code', e.target.value)}
                                placeholder="00300"
                                autoComplete="postal-code"
                            />
                            <InputError className="mt-1.5" message={errors.postal_code} />
                        </div>
                    </div>

                    {/* Country */}
                    <div>
                        <InputLabel htmlFor="country" value="Country" />
                        <TextInput
                            id="country"
                            className="mt-1.5"
                            value={data.country}
                            onChange={(e) => setData('country', e.target.value)}
                            placeholder="Sri Lanka"
                            autoComplete="country-name"
                        />
                        <InputError className="mt-1.5" message={errors.country} />
                    </div>
                </div>

                {/* Email Verification Notice */}
                {mustVerifyEmail && user?.email_verified_at === null && (
                    <div className="p-4 rounded-xl bg-amber-500/10 border border-amber-500/25">
                        <p className="text-xs text-amber-500">
                            Your email address is unverified.{' '}
                            <Link
                                href={route('verification.send')}
                                method="post"
                                as="button"
                                className="font-bold underline hover:text-amber-400 focus:outline-none"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        {status === 'verification-link-sent' && (
                            <div className="mt-2 text-xs font-semibold text-emerald-400">
                                A new verification link has been sent to your email address.
                            </div>
                        )}
                    </div>
                )}

                {/* Submit Action */}
                <div className="flex items-center gap-4 pt-2">
                    <button
                        type="submit"
                        disabled={processing}
                        className="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-accent text-accent-fg font-bold text-xs uppercase tracking-wider shadow-md hover:opacity-90 active:scale-95 transition-all cursor-pointer disabled:opacity-50"
                    >
                        Save Profile
                    </button>

                    <Transition
                        show={recentlySuccessful}
                        enter="transition ease-in-out duration-300"
                        enterFrom="opacity-0 translate-x-2"
                        leave="transition ease-in-out duration-300"
                        leaveTo="opacity-0"
                    >
                        <span className="flex items-center gap-1.5 text-xs font-semibold text-emerald-400">
                            <Check className="w-4 h-4" />
                            Saved successfully!
                        </span>
                    </Transition>
                </div>
            </form>
        </section>
    );
}
