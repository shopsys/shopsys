'use client';

import useTranslation from 'next-translate/useTranslation';

export default function AboutPage() {
    console.log('✅ client');
    const { t } = useTranslation();

    return (
        <main>
            <h3>{t('Shop by category')}</h3>
        </main>
    );
}
