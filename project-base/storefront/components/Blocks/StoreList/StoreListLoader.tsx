import useTranslation from 'utils/i18n/useTranslationWrapper';

export const StoreListLoader: FC = () => {
    const { t } = useTranslation();

    return <div className="mt-3 text-center text-sm text-text-less">{t('Loading more stores')}</div>;
};
