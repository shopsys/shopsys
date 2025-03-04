import { PromotedCategoriesContent } from './PromotedCategoriesContent';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypePromotedCategoriesQuery } from 'graphql/requests/categories/queries/PromotedCategoriesQuery.generated';

export type PromotedCategoriesProps = {
    promotedCategoriesData: TypePromotedCategoriesQuery | null | undefined;
};

export async function PromotedCategories({ promotedCategoriesData }: PromotedCategoriesProps) {
    const t = await getTranslation();

    if (!promotedCategoriesData?.promotedCategories.length) {
        return null;
    }

    return (
        <Webline className="mb-10">
            <h3 className="mb-4">{t('Shop by category')}</h3>
            <PromotedCategoriesContent promotedCategoriesData={promotedCategoriesData} />
        </Webline>
    );
}
