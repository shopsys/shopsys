import { ProductComparisonContent } from './ProductComparisonContent';
import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { SkeletonModuleComparison } from 'components/Blocks/Skeleton/SkeletonModuleComparison';
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
        <Webline>
            <h1 className="mb-8">{title}</h1>

            {isProductListFetching && <SkeletonModuleComparison />}

            {comparison?.products && !isProductListFetching && (
                <ProductComparisonContent comparedProducts={comparison.products} />
            )}

            {!comparison?.products && !isProductListFetching && (
                <div className="flex items-center">
                    <InfoIcon className="mr-4 w-8" />
                    <div className="h3">{t('Comparison does not contain any products yet.')}</div>
                </div>
            )}
        </Webline>
    );
};
