import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import Trans from 'next-translate/Trans';

export const CategoryDetailContentMessage: FC = () => {
    const t = useTypedTranslationFunction();

    return (
        <div className="p-12 text-center">
            <div className="mb-5">
                <strong>{t('No results match the filter')}</strong>
            </div>
            <div>
                <Trans i18nKey="ProductsNoResults" components={{ 0: <br /> }} />
            </div>
        </div>
    );
};
