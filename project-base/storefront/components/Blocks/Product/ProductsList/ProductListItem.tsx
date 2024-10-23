import { ProductListItemImage } from './ProductListItemImage';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { RemoveBoldIcon } from 'components/Basic/Icon/RemoveBoldIcon';
import { ProductCompareButton } from 'components/Blocks/Product/ButtonsAction/ProductCompareButton';
import { ProductWishlistButton } from 'components/Blocks/Product/ButtonsAction/ProductWishlistButton';
import { ProductAction } from 'components/Blocks/Product/ProductAction';
import { ProductAvailableStoresCount } from 'components/Blocks/Product/ProductAvailableStoresCount';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TIDs } from 'cypress/tids';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { onGtmProductClickEventHandler } from 'gtm/handlers/onGtmProductClickEventHandler';
import useTranslation from 'next-translate/useTranslation';
import { forwardRef } from 'react';
import { twJoin } from 'tailwind-merge';
import { FunctionComponentProps } from 'types/globals';
import { twMergeCustom } from 'utils/twMerge';

export type ProductVisibleItemsConfigType = {
    addToCart?: boolean;
    productListButtons?: boolean;
    storeAvailability?: boolean;
    price?: boolean;
    flags?: boolean;
    wishlistRemoveButton?: boolean;
    priceFromWord?: boolean;
};

export type ProductItemProps = {
    product: TypeListedProductFragment;
    listIndex: number;
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    isProductInComparison: boolean;
    isProductInWishlist: boolean;
    toggleProductInComparison: () => void;
    toggleProductInWishlist: () => void;
    visibleItemsConfig?: ProductVisibleItemsConfigType;
    size?: 'extraSmall' | 'small' | 'medium' | 'large' | 'extraLarge';
    onClick?: (product: TypeListedProductFragment, index: number) => void;
} & FunctionComponentProps;

export const ProductListItem = forwardRef<HTMLLIElement, ProductItemProps>(
    (
        {
            product,
            listIndex,
            gtmProductListName,
            gtmMessageOrigin,
            isProductInComparison,
            isProductInWishlist,
            toggleProductInComparison,
            toggleProductInWishlist,
            className,
            visibleItemsConfig = PREDEFINED_VISIBLE_ITEMS_CONFIGS.largeItem,
            size = 'large',
            onClick,
        },
        ref,
    ) => {
        const { url } = useDomainConfig();
        const { t } = useTranslation();
        const currentCustomerData = useCurrentCustomerData();

        return (
            <li
                ref={ref}
                tid={TIDs.blocks_product_list_listeditem_ + product.catalogNumber}
                className={twMergeCustom(
                    'group relative flex select-none flex-col justify-between gap-2.5 rounded-xl border border-backgroundMore bg-backgroundMore px-2.5 py-5 text-left transition sm:px-5',
                    'hover:border-borderAccentLess hover:bg-background',
                    className,
                )}
            >
                {visibleItemsConfig.wishlistRemoveButton && (
                    <button
                        title={t('Remove from wishlist')}
                        className={twJoin(
                            'absolute left-3 flex h-5 w-5 cursor-pointer items-center justify-center rounded-full p-0 transition',
                            'border-none bg-backgroundAccentLess text-text outline-none',
                            'hover:bg-backgroundAccent hover:text-textInverted',
                        )}
                        onClick={toggleProductInWishlist}
                    >
                        <RemoveBoldIcon className="mx-auto w-2 basis-2" />
                    </button>
                )}

                <ExtendedNextLink
                    className="flex h-full select-none flex-col justify-between gap-2.5 text-text no-underline hover:text-link hover:no-underline"
                    draggable={false}
                    href={product.slug}
                    type={product.isMainVariant ? 'productMainVariant' : 'product'}
                    onClick={() => {
                        onGtmProductClickEventHandler(
                            product,
                            gtmProductListName,
                            listIndex,
                            url,
                            !!currentCustomerData?.arePricesHidden,
                        );
                        onClick?.(product, listIndex);
                    }}
                >
                    <ProductListItemImage product={product} size={size} visibleItemsConfig={visibleItemsConfig} />

                    <div className="line-clamp-3 min-h-[3.75rem] font-secondary text-sm font-semibold group-hover:text-link group-hover:underline">
                        {product.fullName}
                    </div>

                    {visibleItemsConfig.price && !(product.isMainVariant && product.isSellingDenied) && (
                        <ProductPrice
                            isPriceFromVisible={visibleItemsConfig.priceFromWord}
                            productPrice={product.price}
                        />
                    )}

                    {visibleItemsConfig.storeAvailability && !product.isInquiryType && (
                        <div className="min-h-10">
                            <ProductAvailableStoresCount
                                availableStoresCount={product.availableStoresCount}
                                isMainVariant={product.isMainVariant}
                                name={product.availability.name}
                            />
                        </div>
                    )}
                </ExtendedNextLink>

                <div className="flex w-full items-center justify-between gap-1 sm:justify-normal sm:gap-2.5">
                    {visibleItemsConfig.addToCart && (
                        <ProductAction
                            gtmMessageOrigin={gtmMessageOrigin}
                            gtmProductListName={gtmProductListName}
                            listIndex={listIndex}
                            product={product}
                        />
                    )}

                    {visibleItemsConfig.productListButtons && (
                        <>
                            <ProductCompareButton
                                isProductInComparison={isProductInComparison}
                                toggleProductInComparison={toggleProductInComparison}
                            />
                            <ProductWishlistButton
                                isProductInWishlist={isProductInWishlist}
                                toggleProductInWishlist={toggleProductInWishlist}
                            />
                        </>
                    )}
                </div>
            </li>
        );
    },
);

ProductListItem.displayName = 'ProductItem';

export const PREDEFINED_VISIBLE_ITEMS_CONFIGS = {
    largeItem: {
        productListButtons: true,
        addToCart: true,
        flags: true,
        price: true,
        storeAvailability: true,
        priceFromWord: true,
    } as ProductVisibleItemsConfigType,
    mediumItem: {
        flags: true,
        price: true,
        storeAvailability: true,
        priceFromWord: true,
    } as ProductVisibleItemsConfigType,
} as const;
