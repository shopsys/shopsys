import { Tag } from 'components/Basic/Tag/Tag';
import { Webline } from 'components/Layout/Webline/Webline';
import { TIDs } from 'cypress/tids';
import { TypeCategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type AdvancedSeoCategoriesProps = {
    readyCategorySeoMixLinks: TypeCategoryDetailFragment['readyCategorySeoMixLinks'];
};

export const AdvancedSeoCategories: FC<AdvancedSeoCategoriesProps> = ({ readyCategorySeoMixLinks }) => {
    const { t } = useTranslation();

    return (
        <Webline className="flex flex-wrap items-center gap-5" tid={TIDs.seo_categories}>
            <span className="h6">{t('You might be interested')}</span>

            {readyCategorySeoMixLinks.map((item) => (
                <Tag
                    key={item.slug}
                    className="min-w-0 max-w-full shrink whitespace-normal break-words bg-background-accent-less text-center text-text-default"
                    href={item.slug}
                    type="category"
                >
                    {item.name}
                </Tag>
            ))}
        </Webline>
    );
};
