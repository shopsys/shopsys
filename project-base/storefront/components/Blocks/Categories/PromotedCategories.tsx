import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { SkeletonModulePromotedCategories } from 'components/Blocks/Skeleton/SkeletonModulePromotedCategories';
import { Webline } from 'components/Layout/Webline/Webline';
import { usePromotedCategoriesQuery } from 'graphql/requests/categories/queries/PromotedCategoriesQuery.generated';
import useTranslation from 'next-translate/useTranslation';

export const PromotedCategories: FC = () => {
    const { t } = useTranslation();
    const [{ data: promotedCategoriesData, fetching: arePromotedCategoriesFetching }] = usePromotedCategoriesQuery();

    const weblineTwClasses = 'mb-6';

    if (arePromotedCategoriesFetching) {
        return (
            <Webline className={weblineTwClasses}>
                <SkeletonModulePromotedCategories />
            </Webline>
        );
    }

    if (!promotedCategoriesData?.promotedCategories.length) {
        return null;
    }

    return (
        <Webline className={weblineTwClasses}>
            <h2 className="mb-3">{t('Promoted categories')}</h2>
            <SimpleNavigation listedItems={promotedCategoriesData.promotedCategories} />
        </Webline>
    );
};
