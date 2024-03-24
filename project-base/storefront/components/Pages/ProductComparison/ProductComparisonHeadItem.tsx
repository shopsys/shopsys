import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { Image } from 'components/Basic/Image/Image';
import { ProductAction } from 'components/Blocks/Product/ProductAction';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { ProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { ListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { onGtmProductClickEventHandler } from 'gtm/helpers/eventHandlers';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { useComparisonTable } from 'hooks/productLists/comparison/useComparisonTable';
import useTranslation from 'next-translate/useTranslation';
import { useCallback } from 'react';

type ProductComparisonItemProps = {
    product: ProductInProductListFragment;
    productsCompareCount: number;
    listIndex: number;
    toggleProductInComparison: () => void;
};

export const ProductComparisonHeadItem: FC<ProductComparisonItemProps> = ({
    product,
    productsCompareCount,
    listIndex,
    toggleProductInComparison,
}) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const { calcMaxMarginLeft } = useComparisonTable(productsCompareCount);

    const onProductDetailRedirectHandler = useCallback(
        (product: ListedProductFragment, listName: GtmProductListNameType, index: number) => {
            onGtmProductClickEventHandler(product, listName, index, url);
        },
        [url],
    );

    return (
        <th className="relative px-3 pb-3 align-top sm:px-5 sm:pb-5" id="js-table-compare-product">
            <div className="flex h-[365px] w-[182px] flex-col gap-2 sm:w-[205px]">
                <div className="flex flex-col items-center ">
                    <div className="flex h-[185px] w-full items-center justify-center pt-4 pb-3">
                        <Image
                            alt={product.mainImage?.name || product.fullName}
                            className="max-h-full w-auto"
                            height={185}
                            src={product.mainImage?.url}
                            width={200}
                        />
                    </div>
                    <ExtendedNextLink
                        className="text-primary no-underline hover:no-underline"
                        href={product.slug}
                        type="product"
                        onClick={() =>
                            onProductDetailRedirectHandler(
                                product,
                                GtmProductListNameType.product_comparison_page,
                                listIndex,
                            )
                        }
                    >
                        {product.fullName}
                    </ExtendedNextLink>
                    <p className="mb-2 text-xs">
                        {t('Code')}: {product.catalogNumber}
                    </p>
                </div>
                <div className="mt-auto">
                    <ProductAction
                        gtmMessageOrigin={GtmMessageOriginType.other}
                        gtmProductListName={GtmProductListNameType.product_comparison_page}
                        listIndex={listIndex}
                        product={product}
                    />
                </div>
            </div>
            <div
                className="absolute top-1 right-1 flex h-10 w-10 cursor-pointer items-center justify-center rounded bg-white transition-colors hover:bg-greyVeryLight"
                onClick={() => {
                    toggleProductInComparison();
                    calcMaxMarginLeft();
                }}
            >
                <RemoveIcon className="w-4 text-grey" />
            </div>

            {product.flags.length > 0 && (
                <div className="absolute left-0 top-0 mt-7 flex flex-col items-start">
                    <ProductFlags flags={product.flags} />
                </div>
            )}
        </th>
    );
};
