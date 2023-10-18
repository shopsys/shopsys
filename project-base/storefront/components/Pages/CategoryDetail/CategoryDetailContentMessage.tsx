import Trans from 'next-translate/Trans';
import useTranslation from 'next-translate/useTranslation';

export const CategoryDetailContentMessage: FC = () => {
    const { t } = useTranslation();

    return (
        <div className="p-12 text-center">
            <div className="mb-5">
                <strong>{t('No results match the filter')}</strong>
            </div>
            <div>
                <Trans components={{ 0: <br /> }} i18nKey="ProductsNoResults" />
            </div>
        </div>
    );
};
