import { ProductComparisonContent } from './ProductComparisonContent';
import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { LastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { SkeletonModuleComparison } from 'components/Blocks/Skeleton/SkeletonModuleComparison';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useGtmSliderProductListViewEvent } from 'gtm/utils/pageViewEvents/productList/useGtmSliderProductListViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { useComparison } from 'utils/productLists/comparison/useComparison';

export const ProductComparison: FC = () => {
    const { t } = useTranslation();
    const { comparison, isProductListFetching } = useComparison();
    const title = `${t('Product comparison')}${comparison?.products.length ? ` (${comparison.products.length})` : ''}`;

    useGtmSliderProductListViewEvent(comparison?.products, GtmProductListNameType.product_comparison_page);

    return (
        <VerticalStack gap="md">
            <Webline>
                <h1 className="mb-4">{title}</h1>

                {isProductListFetching && <SkeletonModuleComparison />}

                {comparison?.products && !isProductListFetching && (
                    <ProductComparisonContent comparedProducts={comparison.products} />
                )}

                {!comparison?.products && !isProductListFetching && (
                    <div className="my-28 flex items-center justify-center">
                        <InfoIcon className="mr-4 w-8" />
                        <div className="h3">{t('Comparison does not contain any products yet.')}</div>
                    </div>
                )}
            </Webline>

            <LastVisitedProducts />
        </VerticalStack>
    );
};
