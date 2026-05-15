import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import UserAvatar from '@/Components/UserAvatar';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useLocale } from '@/hooks/useLocale';
import type { PageProps } from '@/types';

type Profile = {
    name: string;
    email: string;
    phone_number: string | null;
    gender: string | null;
    national_id: string | null;
    profile_photo_url: string | null;
    initials: string;
    roles: string[];
    status: string;
    last_login_at: string | null;
};

const inputCls =
    'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder-slate-500';
const labelCls = 'block text-xs font-medium text-gray-600 dark:text-slate-400';

function Field({ label, error, children }: { label: string; error?: string; children: React.ReactNode }) {
    return (
        <div>
            <label className={labelCls}>{label}</label>
            <div className="mt-1">{children}</div>
            {error && <p className="mt-1 text-xs text-red-600 dark:text-red-400">{error}</p>}
        </div>
    );
}

export default function Edit({
    mustVerifyEmail,
    status,
    profile,
}: PageProps<{ mustVerifyEmail: boolean; status?: string; profile: Profile }>) {
    const { t } = useLocale();
    const [photoPreview, setPhotoPreview] = useState<string | null>(null);

    const form = useForm<{
        name: string;
        email: string;
        phone_number: string;
        gender: string;
        national_id: string;
        profile_photo: File | null;
    }>({
        name: profile.name,
        email: profile.email,
        phone_number: profile.phone_number ?? '',
        gender: profile.gender ?? '',
        national_id: profile.national_id ?? '',
        profile_photo: null,
    });

    const genderOptions = [
        { value: '', label: t('profile.selectGender') },
        { value: 'male', label: t('profile.genderMale') },
        { value: 'female', label: t('profile.genderFemale') },
        { value: 'other', label: t('profile.genderOther') },
        { value: 'not_specified', label: t('profile.genderNotSpecified') },
    ];

    function handlePhotoChange(e: React.ChangeEvent<HTMLInputElement>) {
        const file = e.target.files?.[0];
        if (!file) return;
        form.setData('profile_photo', file);
        const reader = new FileReader();
        reader.onload = (event) => setPhotoPreview(event.target?.result as string);
        reader.readAsDataURL(file);
    }

    function removeSelectedPhoto() {
        form.setData('profile_photo', null);
        setPhotoPreview(null);
    }

    function submit(e: React.FormEvent) {
        e.preventDefault();
        const payload = new FormData();
        payload.append('_method', 'PATCH');
        payload.append('name', form.data.name);
        payload.append('email', form.data.email);
        payload.append('phone_number', form.data.phone_number);
        payload.append('gender', form.data.gender);
        payload.append('national_id', form.data.national_id);
        if (form.data.profile_photo) {
            payload.append('profile_photo', form.data.profile_photo);
        }

        router.post(route('profile.update'), payload, {
            preserveScroll: true,
            onSuccess: () => {
                form.setData('profile_photo', null);
                setPhotoPreview(null);
            },
        });
    }

    const currentPhoto = photoPreview ?? profile.profile_photo_url;

    return (
        <AuthenticatedLayout
            header={<PageHeader title={t('profile.title')} description={t('profile.description')} />}
        >
            <Head title={t('profile.title')} />

            <div className="grid gap-6 xl:grid-cols-[360px_minmax(0,1fr)]">
                <div className="space-y-6">
                    <section className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div className="flex flex-col items-center text-center">
                            <UserAvatar src={currentPhoto} name={form.data.name || profile.name} size={96} />
                            <h2 className="mt-4 text-lg font-semibold text-gray-900 dark:text-slate-100">{profile.name}</h2>
                            <p className="text-sm text-gray-500 dark:text-slate-400">{profile.email}</p>
                            <div className="mt-3 flex flex-wrap justify-center gap-1">
                                {profile.roles.map((role) => (
                                    <span key={role} className="rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
                                        {role}
                                    </span>
                                ))}
                            </div>
                        </div>
                        <div className="mt-6 border-t border-gray-100 pt-5 dark:border-slate-800">
                            <p className={labelCls}>{t('profile.profilePhoto')}</p>
                            <div className="mt-2 flex flex-wrap gap-2">
                                <label className="cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300 dark:hover:bg-slate-800">
                                    {profile.profile_photo_url ? t('profile.changePhoto') : t('profile.uploadPhoto')}
                                    <input type="file" accept="image/jpg,image/jpeg,image/png,image/webp" className="sr-only" onChange={handlePhotoChange} />
                                </label>
                                {photoPreview && (
                                    <button type="button" onClick={removeSelectedPhoto} className="rounded-lg px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/30">
                                        {t('profile.removeSelectedPhoto')}
                                    </button>
                                )}
                            </div>
                            {form.errors.profile_photo && <p className="mt-2 text-xs text-red-600 dark:text-red-400">{form.errors.profile_photo}</p>}
                        </div>
                    </section>

                    <section className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <h3 className="text-sm font-semibold text-gray-900 dark:text-slate-100">{t('profile.accountSummary')}</h3>
                        <dl className="mt-4 space-y-3 text-sm">
                            <div className="flex justify-between gap-4">
                                <dt className="text-gray-500 dark:text-slate-400">{t('profile.status')}</dt>
                                <dd className="font-medium text-gray-900 dark:text-slate-100">{profile.status}</dd>
                            </div>
                            <div className="flex justify-between gap-4">
                                <dt className="text-gray-500 dark:text-slate-400">{t('profile.lastLogin')}</dt>
                                <dd className="font-medium text-gray-900 dark:text-slate-100">{profile.last_login_at ?? t('profile.notAvailable')}</dd>
                            </div>
                        </dl>
                    </section>
                </div>

                <div className="space-y-6">
                    <form onSubmit={submit} className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div>
                            <h2 className="text-lg font-semibold text-gray-900 dark:text-slate-100">{t('profile.personalInformation')}</h2>
                            <p className="mt-1 text-sm text-gray-500 dark:text-slate-400">{t('profile.personalInformationHelp')}</p>
                        </div>

                        <div className="mt-6 grid gap-4 md:grid-cols-2">
                            <Field label={t('profile.name')} error={form.errors.name}>
                                <input className={inputCls} value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} />
                            </Field>
                            <Field label={t('profile.email')} error={form.errors.email}>
                                <input type="email" className={inputCls} value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} />
                            </Field>
                            <Field label={t('profile.phoneNumber')} error={form.errors.phone_number}>
                                <input className={inputCls} placeholder={t('profile.enterPhoneNumber')} value={form.data.phone_number} onChange={(e) => form.setData('phone_number', e.target.value)} />
                            </Field>
                            <Field label={t('profile.gender')} error={form.errors.gender}>
                                <select className={inputCls} value={form.data.gender} onChange={(e) => form.setData('gender', e.target.value)}>
                                    {genderOptions.map((option) => (
                                        <option key={option.value} value={option.value}>{option.label}</option>
                                    ))}
                                </select>
                            </Field>
                            <Field label={t('profile.nationalId')} error={form.errors.national_id}>
                                <input className={inputCls} value={form.data.national_id} onChange={(e) => form.setData('national_id', e.target.value)} />
                            </Field>
                        </div>

                        {mustVerifyEmail && status === 'verification-link-sent' && (
                            <p className="mt-4 text-sm text-green-600 dark:text-green-400">{t('profile.verificationLinkSent')}</p>
                        )}

                        <div className="mt-6 flex justify-end border-t border-gray-100 pt-5 dark:border-slate-800">
                            <button type="submit" disabled={form.processing} className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60">
                                {form.processing ? t('common.saving') : t('profile.updateProfile')}
                            </button>
                        </div>
                    </form>

                    <section className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <UpdatePasswordForm />
                    </section>

                    <section className="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-200">
                        <h3 className="font-semibold">{t('profile.accountDeletionDisabled')}</h3>
                        <p className="mt-1">{t('profile.selfDeleteProtected')}</p>
                        <Link href={route('dashboard')} className="mt-3 inline-flex text-sm font-medium text-amber-800 underline dark:text-amber-200">
                            {t('common.back')}
                        </Link>
                    </section>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
