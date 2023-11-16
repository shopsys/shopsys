import { ProductComparisonContent } from './ProductComparisonContent';
import { InfoIcon } from 'components/Basic/Icon/IconsSvg';
import { SkeletonModuleComparison } from 'components/Blocks/Skeleton/SkeletonModuleComparison';
import { Webline } from 'components/Layout/Webline/Webline';
import { useGtmSliderProductListViewEvent } from 'gtm/hooks/productList/useGtmSliderProductListViewEvent';
import { GtmProductListNameType } from 'gtm/types/enums';
import { useComparison } from 'hooks/productLists/comparison/useComparison';
import useTranslation from 'next-translate/useTranslation';

export const ProductComparison: FC = () => {
    const { t } = useTranslation();
    const { comparison, fetching } = useComparison();
    const title = `${t('Product comparison')}${comparison?.products.length ? ` (${comparison.products.length})` : ''}`;

    useGtmSliderProductListViewEvent(comparison?.products, GtmProductListNameType.product_comparison_page);

    return (
        <Webline>
            <h1 className="mb-8">{title}</h1>

            {fetching && <SkeletonModuleComparison />}

            {comparison?.products && !fetching && <ProductComparisonContent comparedProducts={comparison.products} />}

            {!comparison?.products && !fetching && (
                <div className="flex items-center">
                    <InfoIcon className="mr-4 w-8" />
                    <div className="h3">{t('Comparison does not contain any products yet.')}</div>
                </div>
            )}
        </Webline>
    );
};
