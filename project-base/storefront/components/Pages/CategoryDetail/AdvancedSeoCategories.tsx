import { Tag } from 'components/Basic/Tag/Tag';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeCategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import useTranslation from 'next-translate/useTranslation';

type AdvancedSeoCategoriesProps = {
    readyCategorySeoMixLinks: TypeCategoryDetailFragment['readyCategorySeoMixLinks'];
};

export const AdvancedSeoCategories: FC<AdvancedSeoCategoriesProps> = ({ readyCategorySeoMixLinks }) => {
    const { t } = useTranslation();

    return (
        <Webline className="flex flex-wrap items-center gap-5">
            <h6>{t('You might be interested')}</h6>

            {readyCategorySeoMixLinks.map((item, index) => (
                <Tag key={index} className="bg-background-accent-less text-default" href={item.slug} type="category">
                    {item.name}
                </Tag>
            ))}
        </Webline>
    );
};
