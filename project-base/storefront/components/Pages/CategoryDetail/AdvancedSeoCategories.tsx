import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { TypeCategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import useTranslation from 'next-translate/useTranslation';

type AdvancedSeoCategoriesProps = {
    readyCategorySeoMixLinks: TypeCategoryDetailFragment['readyCategorySeoMixLinks'];
};

const simpleNavigationItemTwClass = 'lg:justify-center text-center';

export const AdvancedSeoCategories: FC<AdvancedSeoCategoriesProps> = ({ readyCategorySeoMixLinks }) => {
    const { t } = useTranslation();

    return (
        <>
            <div className="mb-3 mt-6 break-words font-bold lg:text-lg">{t('Favorite categories')}</div>
            <SimpleNavigation
                itemClassName={simpleNavigationItemTwClass}
                linkTypeOverride="category"
                listedItems={readyCategorySeoMixLinks}
            />
        </>
    );
};
