import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { CategoryDetailFragmentApi } from 'graphql/generated';
import useTranslation from 'next-translate/useTranslation';

type AdvancedSeoCategoriesProps = {
    readyCategorySeoMixLinks: CategoryDetailFragmentApi['readyCategorySeoMixLinks'];
};

export const AdvancedSeoCategories: FC<AdvancedSeoCategoriesProps> = ({ readyCategorySeoMixLinks }) => {
    const { t } = useTranslation();

    if (!readyCategorySeoMixLinks.length) {
        return null;
    }

    return (
        <>
            <div className="mb-3 break-words font-bold text-dark lg:text-lg">{t('Favorite categories')}</div>
            <SimpleNavigation listedItems={readyCategorySeoMixLinks} className="mb-5" />
        </>
    );
};
