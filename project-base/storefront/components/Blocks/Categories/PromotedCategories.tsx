import { PromotedCategoriesContent } from './PromotedCategoriesContent';
import { SkeletonModulePromotedCategories } from 'components/Blocks/Skeleton/SkeletonModulePromotedCategories';
import { Webline } from 'components/Layout/Webline/Webline';
import { usePromotedCategoriesQuery } from 'graphql/requests/categories/queries/PromotedCategoriesQuery.generated';
import useTranslation from 'next-translate/useTranslation';

export const PromotedCategories: FC = () => {
    const { t } = useTranslation();
    const [{ data: promotedCategoriesData, fetching: arePromotedCategoriesFetching }] = usePromotedCategoriesQuery();

    if (arePromotedCategoriesFetching) {
        return (
            <Webline className="mb-10">
                <SkeletonModulePromotedCategories />
            </Webline>
        );
    }

    if (!promotedCategoriesData?.promotedCategories.length) {
        return null;
    }

    return (
        <Webline className="mb-10">
            <h3 className="mb-4">{t('Shop by category')}</h3>
            <PromotedCategoriesContent promotedCategoriesData={promotedCategoriesData} />
        </Webline>
    );
};
