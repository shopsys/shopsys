import { Tag } from 'components/Basic/Tag/Tag';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeCategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { memo } from 'react';

type AdvancedSeoCategoriesProps = {
    readyCategorySeoMixLinks: TypeCategoryDetailFragment['readyCategorySeoMixLinks'];
};

const AdvancedSeoCategoriesComp: FC<AdvancedSeoCategoriesProps> = ({ readyCategorySeoMixLinks }) => {
    const { t } = useTranslation();

    return (
        <Webline className="flex flex-wrap items-center gap-5">
            <span className="h6">{t('You might be interested')}</span>

            {readyCategorySeoMixLinks.map((item, index) => (
                <Tag
                    key={index}
                    className="bg-background-accent-less text-text-default"
                    href={item.slug}
                    type="category"
                >
                    {item.name}
                </Tag>
            ))}
        </Webline>
    );
};

export const AdvancedSeoCategories = memo(AdvancedSeoCategoriesComp);
