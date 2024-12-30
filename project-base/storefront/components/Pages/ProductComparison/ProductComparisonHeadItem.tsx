import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { Image } from 'components/Basic/Image/Image';
import { ProductAction } from 'components/Blocks/Product/ProductAction';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { Button } from 'components/Forms/Button/Button';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { onGtmProductClickEventHandler } from 'gtm/handlers/onGtmProductClickEventHandler';
import useTranslation from 'next-translate/useTranslation';
import { useCallback } from 'react';
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
    const currentCustomerData = useCurrentCustomerData();

    const onProductDetailRedirectHandler = useCallback(
        (product: TypeListedProductFragment, listName: GtmProductListNameType, index: number) => {
            onGtmProductClickEventHandler(product, listName, index, url, !!currentCustomerData?.arePricesHidden);
        },
        [url],
    );

    return (
        <th className="relative px-3 pb-3 align-top font-semibold sm:px-5 sm:pb-5" id="js-table-compare-product">
            <div className="flex w-[182px] flex-col gap-2 sm:w-[205px]">
                <div className="flex flex-col gap-2">
                    <div className="flex h-[185px] w-full items-center justify-center pb-3 pt-4">
                        <Image
                            alt={product.mainImage?.name || product.fullName}
                            className="max-h-full w-auto"
                            height={185}
                            src={product.mainImage?.url}
                            width={200}
                        />
                    </div>
                    <ExtendedNextLink
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
                        <span className="line-clamp-4 min-h-[5rem] font-secondary text-sm">{product.fullName}</span>
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
                className="absolute right-3 top-0 bg-background p-2 sm:right-5"
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
