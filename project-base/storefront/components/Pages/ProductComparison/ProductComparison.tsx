import { ProductComparisonContent } from './ProductComparisonContent';
import { CompareIcon } from 'components/Basic/Icon/CompareIcon';
import { LastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { SkeletonModuleComparison } from 'components/Blocks/Skeleton/SkeletonModuleComparison';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useGtmSliderProductListViewEvent } from 'gtm/utils/pageViewEvents/productList/useGtmSliderProductListViewEvent';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useComparison } from 'utils/productLists/comparison/useComparison';

export const ProductComparison: FC = () => {
    const { t } = useTranslation();
    const { comparison, isProductListFetching } = useComparison();
    const title = `${t('Product comparison')}${comparison?.products.length ? ` (${comparison.products.length})` : ''}`;

    useGtmSliderProductListViewEvent(comparison?.products, GtmProductListNameType.product_comparison_page);

    return (
        <VerticalStack gap="md">
            <Webline>
                {isProductListFetching && <SkeletonModuleComparison />}

                {comparison?.products && !isProductListFetching && (
                    <>
                        <h1 className="mb-4">{title}</h1>

                        <ProductComparisonContent comparedProducts={comparison.products} />
                    </>
                )}

                {!comparison?.products && !isProductListFetching && (
                    <PageHero
                        actionHref="/"
                        actionSkeletonType="homepage"
                        actionTitle={t('Find products')}
                        icon={CompareIcon}
                        title={t('Comparison')}
                        description={t(
                            'Your comparison list is empty! Start comparing products to see their features side by side.',
                        )}
                    />
                )}
            </Webline>

            <LastVisitedProducts />
        </VerticalStack>
    );
};
