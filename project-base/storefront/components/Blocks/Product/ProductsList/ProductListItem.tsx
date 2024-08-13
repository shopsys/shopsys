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
    size?: 'small' | 'medium' | 'large' | 'extraLarge';
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
            size = 'extraLarge',
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
                    'group relative flex select-none flex-col justify-between gap-2.5 p-5 text-left rounded-xl h-ful transition',
                    'bg-backgroundMore',
                    'hover:bg-backgroundMost',
                    className,
                )}
            >
                {visibleItemsConfig.wishlistRemoveButton && (
                    <button
                        title={t('Remove from wishlist')}
                        className={twJoin(
                            'absolute left-3 z-above flex h-5 w-5 cursor-pointer items-center justify-center rounded-full p-0 transition',
                            'border-none bg-backgroundAccentLess outline-none text-text',
                            'hover:bg-backgroundAccent hover:text-textInverted',
                        )}
                        onClick={toggleProductInWishlist}
                    >
                        <RemoveBoldIcon className="mx-auto w-2 basis-2" />
                    </button>
                )}

                <ExtendedNextLink
                    className="flex h-full select-none flex-col justify-between no-underline hover:no-underline text-text hover:text-text"
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
                    <div className="flex flex-col gap-2">
                        <ProductListItemImage product={product} size={size} visibleItemsConfig={visibleItemsConfig} />

                        <div className="font-semibold font-secondary mb-4">{product.fullName}</div>
                    </div>

                    <div>
                        {visibleItemsConfig.price && !(product.isMainVariant && product.isSellingDenied) && (
                            <ProductPrice
                                isPriceFromVisible={visibleItemsConfig.priceFromWord}
                                productPrice={product.price}
                            />
                        )}

                        {visibleItemsConfig.storeAvailability && (
                            <div className="flex flex-col justify-between text-sm text-text h-16">
                                <div>{product.availability.name}</div>
                                <ProductAvailableStoresCount
                                    availableStoresCount={product.availableStoresCount}
                                    isMainVariant={product.isMainVariant}
                                />
                            </div>
                        )}
                    </div>
                </ExtendedNextLink>

                <div className="flex w-full items-center gap-2">
                    {visibleItemsConfig.addToCart && (
                        <ProductAction
                            gtmMessageOrigin={gtmMessageOrigin}
                            gtmProductListName={gtmProductListName}
                            listIndex={listIndex}
                            product={product}
                        />
                    )}

                    {visibleItemsConfig.productListButtons && (
                        <div className="flex justify-end gap-2">
                            <ProductCompareButton
                                isProductInComparison={isProductInComparison}
                                toggleProductInComparison={toggleProductInComparison}
                            />
                            <ProductWishlistButton
                                isProductInWishlist={isProductInWishlist}
                                toggleProductInWishlist={toggleProductInWishlist}
                            />
                        </div>
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
