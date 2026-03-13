import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { ProductCompareButton } from 'components/Blocks/Product/ButtonsAction/ProductCompareButton';
import { ProductWishlistButton } from 'components/Blocks/Product/ButtonsAction/ProductWishlistButton';
import { ProductAction } from 'components/Blocks/Product/ProductAction';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { onGtmProductClickEventHandler } from 'gtm/handlers/onGtmProductClickEventHandler';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { generateProductImageAlt } from 'utils/productAltText';
import { useComparison } from 'utils/productLists/comparison/useComparison';
import { useWishlist } from 'utils/productLists/wishlist/useWishlist';

type ProductComparisonItemProps = {
    product: TypeProductInProductListFragment;
    listIndex: number;
    toggleProductInComparison: () => void;
};

export const ProductComparisonHeadItem: FC<ProductComparisonItemProps> = ({
    product,
    listIndex,
    toggleProductInComparison,
}) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const { isProductInComparison } = useComparison();
    const { toggleProductInWishlist, isProductInWishlist } = useWishlist();
    const { canSeePrices } = useAuthorization();

    const onProductDetailRedirectHandler = (
        product: TypeListedProductFragment,
        listName: GtmProductListNameType,
        index: number,
    ) => {
        onGtmProductClickEventHandler(product, listName, index, url, !canSeePrices);
    };

    return (
        <th
            className="relative px-3 pb-3 align-top font-semibold sm:px-5 sm:pb-5"
            data-tid={TIDs.comparison_product_ + product.catalogNumber}
            id="js-table-compare-product"
        >
            <div className="flex w-[182px] flex-col gap-2 sm:w-[205px]">
                <div className="flex flex-col gap-2">
                    <div
                        className="flex h-[185px] w-full items-center justify-center pt-4 pb-3"
                        data-tid={TIDs.comparison_product_image}
                    >
                        <Image
                            alt={generateProductImageAlt(product.fullName, product.categories[0]?.name)}
                            className="max-h-full w-auto"
                            height={185}
                            src={product.mainImage?.url}
                            width={200}
                        />
                    </div>
                    <ExtendedNextLink
                        href={product.slug}
                        type="product"
                        aria-label={t('Go to product page of {{ productName }}', {
                            ns: 'accessibility',
                            productName: product.fullName,
                        })}
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
                <div className="flex w-full items-center justify-between gap-1 md:justify-normal md:gap-2.5">
                    <ProductAction
                        gtmMessageOrigin={GtmMessageOriginType.other}
                        gtmProductListName={GtmProductListNameType.product_comparison_page}
                        listIndex={listIndex}
                        product={product}
                    />
                    <ProductCompareButton
                        isProductInComparison={isProductInComparison(product.uuid)}
                        productName={product.fullName}
                        tabIndex={0}
                        toggleProductInComparison={toggleProductInComparison}
                    />
                    <ProductWishlistButton
                        isProductInWishlist={isProductInWishlist(product.uuid)}
                        productName={product.fullName}
                        tabIndex={0}
                        toggleProductInWishlist={() => toggleProductInWishlist(product.uuid)}
                    />
                </div>
            </div>

            <ProductFlags
                flags={product.flags}
                percentageDiscount={product.price.percentageDiscount}
                variant="comparison"
            />
        </th>
    );
};
