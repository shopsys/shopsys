'use client';

import { useTranslation } from 'components/providers/TranslationProvider';

export default function Loading() {
    const { t } = useTranslation();

    return <p>{t('Loading')}</p>;
}
