import Trans from 'app/_utils/translation/Trans';
import { getTranslation } from 'app/_utils/translation/getTranslation';

export const CategoryDetailContentMessage: FC = async () => {
    const t = await getTranslation();

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
