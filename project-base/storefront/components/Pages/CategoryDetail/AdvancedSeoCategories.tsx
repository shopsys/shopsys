import { LabelLink } from 'components/Basic/LabelLink/LabelLink';
import { TypeCategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import useTranslation from 'next-translate/useTranslation';

type AdvancedSeoCategoriesProps = {
    readyCategorySeoMixLinks: TypeCategoryDetailFragment['readyCategorySeoMixLinks'];
};

export const AdvancedSeoCategories: FC<AdvancedSeoCategoriesProps> = ({ readyCategorySeoMixLinks }) => {
    const { t } = useTranslation();

    return (
        <div className="flex flex-wrap items-center gap-5 mb-8">
            <h6>{t('You might be interested')}</h6>
            {readyCategorySeoMixLinks.map((item, index) => (
                <LabelLink key={index} className="bg-backgroundAccentLess text-text" href={item.slug} type="category">
                    {item.name}
                </LabelLink>
            ))}
        </div>
    );
};
