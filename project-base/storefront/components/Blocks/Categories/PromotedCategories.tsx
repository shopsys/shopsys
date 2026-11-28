import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { getLinkClassName } from 'components/Basic/Link/linkUtils';
import { SkeletonModulePromotedCategories } from 'components/Blocks/Skeleton/SkeletonModulePromotedCategories';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { usePromotedCategoriesQuery } from 'graphql/requests/categories/queries/PromotedCategoriesQuery.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { twMergeCustom } from 'utils/twMerge';
import { PromotedCategoriesContent } from './PromotedCategoriesContent';

export const PromotedCategories: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [catalogUrl] = getInternationalizedStaticUrls(['/catalog'], url);
    const [{ data: promotedCategoriesData, fetching: arePromotedCategoriesFetching }] = usePromotedCategoriesQuery();

    if (arePromotedCategoriesFetching) {
        return (
            <Webline>
                <SkeletonModulePromotedCategories />
            </Webline>
        );
    }

    if (!promotedCategoriesData?.promotedCategories.length) {
        return null;
    }

    return (
        <Webline>
            <div className="mb-3 flex items-center justify-between gap-4">
                <h2 className="h3">{t('Shop by category')}</h2>

                <ExtendedNextLink
                    className={twMergeCustom(
                        getLinkClassName({ variant: 'navigation', tone: 'accent' }),
                        'group vl:flex hidden shrink-0 items-center gap-2 font-semibold text-sm',
                    )}
                    href={catalogUrl}
                    type="catalog"
                >
                    {t('All categories')}
                    <ArrowSecondaryIcon className="size-3 -rotate-90 transition-transform group-hover:translate-x-0.5" />
                </ExtendedNextLink>
            </div>

            <PromotedCategoriesContent promotedCategoriesData={promotedCategoriesData} />
        </Webline>
    );
};
