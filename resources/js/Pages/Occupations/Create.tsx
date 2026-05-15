import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputLabel from '@/Components/InputLabel';
import PageHeader from '@/Components/PageHeader';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import { useLocale } from '@/hooks/useLocale';

export default function OccupationsCreate() {
    const { t } = useLocale();
    const form = useForm({
        isco_code: '',
        isco_major_group_code: '',
        isco_sub_major_group_code: '',
        isco_minor_group_code: '',
        isco_unit_group_code: '',
        name_en: '',
        name_am: '',
        skill_level: '',
        skill_specialization: '',
        description_en: '',
        description_am: '',
        sort_order: '0',
        is_active: true,
    });

    const inputCls =
        'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100';

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        form.post(route('occupations.store'));
    }

    return (
        <AuthenticatedLayout header={<PageHeader title={t('occupations.createOccupation')} />}>
            <Head title={t('occupations.createOccupation')} />
            <form
                className="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900"
                onSubmit={submit}
            >
                <div className="grid gap-4 md:grid-cols-2">
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="isco_code" value={t('occupations.iscoCode')} />
                        <input
                            id="isco_code"
                            className={inputCls}
                            value={form.data.isco_code}
                            placeholder="e.g. 2512"
                            onChange={(e) => form.setData('isco_code', e.target.value.toUpperCase())}
                        />
                        {form.errors.isco_code && <p className="text-xs text-red-600">{form.errors.isco_code}</p>}
                    </div>
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="skill_level" value={t('occupations.skillLevel')} />
                        <input
                            id="skill_level"
                            className={inputCls}
                            value={form.data.skill_level}
                            onChange={(e) => form.setData('skill_level', e.target.value)}
                        />
                    </div>
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="isco_major_group_code" value={t('occupations.majorGroup')} />
                        <input
                            id="isco_major_group_code"
                            maxLength={1}
                            className={inputCls}
                            value={form.data.isco_major_group_code}
                            onChange={(e) => form.setData('isco_major_group_code', e.target.value)}
                        />
                        {form.errors.isco_major_group_code && (
                            <p className="text-xs text-red-600">{form.errors.isco_major_group_code}</p>
                        )}
                    </div>
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="isco_sub_major_group_code" value={t('occupations.subMajorGroup')} />
                        <input
                            id="isco_sub_major_group_code"
                            maxLength={2}
                            className={inputCls}
                            value={form.data.isco_sub_major_group_code}
                            onChange={(e) => form.setData('isco_sub_major_group_code', e.target.value)}
                        />
                    </div>
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="isco_minor_group_code" value={t('occupations.minorGroup')} />
                        <input
                            id="isco_minor_group_code"
                            maxLength={3}
                            className={inputCls}
                            value={form.data.isco_minor_group_code}
                            onChange={(e) => form.setData('isco_minor_group_code', e.target.value)}
                        />
                    </div>
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="isco_unit_group_code" value={t('occupations.unitGroup')} />
                        <input
                            id="isco_unit_group_code"
                            maxLength={4}
                            className={inputCls}
                            value={form.data.isco_unit_group_code}
                            onChange={(e) => form.setData('isco_unit_group_code', e.target.value)}
                        />
                    </div>
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="name_en" value={t('occupations.nameEn')} />
                        <input
                            id="name_en"
                            className={inputCls}
                            value={form.data.name_en}
                            onChange={(e) => form.setData('name_en', e.target.value)}
                        />
                        {form.errors.name_en && <p className="text-xs text-red-600">{form.errors.name_en}</p>}
                    </div>
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="name_am" value={t('occupations.nameAm')} />
                        <input
                            id="name_am"
                            className={inputCls}
                            value={form.data.name_am}
                            onChange={(e) => form.setData('name_am', e.target.value)}
                        />
                    </div>
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="skill_specialization" value={t('occupations.skillSpecialization')} />
                        <input
                            id="skill_specialization"
                            className={inputCls}
                            value={form.data.skill_specialization}
                            onChange={(e) => form.setData('skill_specialization', e.target.value)}
                        />
                    </div>
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="sort_order" value={t('occupations.sortOrder')} />
                        <input
                            id="sort_order"
                            type="number"
                            min="0"
                            className={inputCls}
                            value={form.data.sort_order}
                            onChange={(e) => form.setData('sort_order', e.target.value)}
                        />
                    </div>
                </div>
                <div className="space-y-1.5">
                    <InputLabel htmlFor="description_en" value={t('occupations.descriptionEn')} />
                    <textarea
                        id="description_en"
                        className={`${inputCls} min-h-28`}
                        value={form.data.description_en}
                        onChange={(e) => form.setData('description_en', e.target.value)}
                    />
                </div>
                <div className="space-y-1.5">
                    <InputLabel htmlFor="description_am" value={t('occupations.descriptionAm')} />
                    <textarea
                        id="description_am"
                        className={`${inputCls} min-h-28`}
                        value={form.data.description_am}
                        onChange={(e) => form.setData('description_am', e.target.value)}
                    />
                </div>
                <label className="flex items-center gap-2 text-sm text-gray-700 dark:text-slate-300">
                    <input
                        type="checkbox"
                        checked={form.data.is_active}
                        onChange={(e) => form.setData('is_active', e.target.checked)}
                    />
                    {t('occupations.activeOccupation')}
                </label>
                <div className="flex gap-3">
                    <button
                        className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                        type="submit"
                        disabled={form.processing}
                    >
                        {t('common.save')}
                    </button>
                    <Link
                        href={route('occupations.index')}
                        className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-slate-700 dark:text-slate-200"
                    >
                        {t('common.cancel')}
                    </Link>
                </div>
            </form>
        </AuthenticatedLayout>
    );
}
