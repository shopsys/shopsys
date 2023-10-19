import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { CategoryDetailFragmentApi } from 'graphql/generated';
import useTranslation from 'next-translate/useTranslation';

type AdvancedSeoCategoriesProps = {
    readyCategorySeoMixLinks: CategoryDetailFragmentApi['readyCategorySeoMixLinks'];
};

const simpleNavigationItemTwClass = 'lg:justify-center text-center';

export const AdvancedSeoCategories: FC<AdvancedSeoCategoriesProps> = ({ readyCategorySeoMixLinks }) => {
    const { t } = useTranslation();

    return (
        <>
            <div className="mb-3 break-words font-bold text-dark lg:text-lg">{t('Favorite categories')}</div>
            <SimpleNavigation
                className="mb-5"
                itemClassName={simpleNavigationItemTwClass}
                listedItems={readyCategorySeoMixLinks}
            />
        </>
    );
};
