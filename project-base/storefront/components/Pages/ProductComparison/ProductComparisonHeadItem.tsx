import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { Image } from 'components/Basic/Image/Image';
import { ProductAction } from 'components/Blocks/Product/ProductAction';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { Button } from 'components/Forms/Button/Button';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { onGtmProductClickEventHandler } from 'gtm/handlers/onGtmProductClickEventHandler';
import useTranslation from 'next-translate/useTranslation';
import { useCallback } from 'react';
import { generateProductImageAlt } from 'utils/productAltText';
import { useComparisonTable } from 'utils/productLists/comparison/useComparisonTable';

type ProductComparisonItemProps = {
    product: TypeProductInProductListFragment;
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
    const { canSeePrices } = useAuthorization();

    const onProductDetailRedirectHandler = useCallback(
        (product: TypeListedProductFragment, listName: GtmProductListNameType, index: number) => {
            onGtmProductClickEventHandler(product, listName, index, url, !canSeePrices);
        },
        [url],
    );

    return (
        <th className="relative px-3 pb-3 align-top font-semibold sm:px-5 sm:pb-5" id="js-table-compare-product">
            <div className="flex w-[182px] flex-col gap-2 sm:w-[205px]">
                <div className="flex flex-col gap-2">
                    <div className="flex h-[185px] w-full items-center justify-center pt-4 pb-3">
                        <Image
                            alt={generateProductImageAlt(product.fullName, product.categories[0]?.name)}
                            className="max-h-full w-auto"
                            height={185}
                            src={product.mainImage?.url}
                            width={200}
                        />
                    </div>
                    <ExtendedNextLink
                        aria-label={t('Go to product page of {{ productName }}', { productName: product.fullName })}
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
                        <span className="font-secondary line-clamp-4 min-h-[5rem] text-sm">{product.fullName}</span>
                    </ExtendedNextLink>
                    <span className="text-xs">
                        {t('Code')}: {product.catalogNumber}
                    </span>
                </div>
                <ProductAction
                    gtmMessageOrigin={GtmMessageOriginType.other}
                    gtmProductListName={GtmProductListNameType.product_comparison_page}
                    listIndex={listIndex}
                    product={product}
                />
            </div>
            <Button
                aria-label={t('Remove product {{ productName }} from comparison', { productName: product.fullName })}
                className="bg-background-default absolute top-0 right-3 p-2 sm:right-5"
                title={t('Remove product from comparison')}
                variant="inverted"
                onClick={() => {
                    toggleProductInComparison();
                    calcMaxMarginLeft();
                }}
            >
                <RemoveIcon className="size-3" />
            </Button>

            <ProductFlags
                flags={product.flags}
                percentageDiscount={product.price.percentageDiscount}
                variant="comparison"
            />
        </th>
    );
};
