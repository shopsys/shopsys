import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { RemoveBoldIcon } from 'components/Basic/Icon/IconsSvg';
import { Image } from 'components/Basic/Image/Image';
import { ProductCompareButton } from 'components/Blocks/Product/ButtonsAction/ProductCompareButton';
import { ProductWishlistButton } from 'components/Blocks/Product/ButtonsAction/ProductWishlistButton';
import { ProductAction } from 'components/Blocks/Product/ProductAction';
import { ProductAvailableStoresCount } from 'components/Blocks/Product/ProductAvailableStoresCount';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { ListedProductFragmentApi } from 'graphql/generated';
import { onGtmProductClickEventHandler } from 'gtm/helpers/eventHandlers';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { twMergeCustom } from 'helpers/twMerge';
import { useDomainConfig } from 'hooks/useDomainConfig';
import useTranslation from 'next-translate/useTranslation';
import { forwardRef } from 'react';
import { FunctionComponentProps } from 'types/globals';

type ProductItemProps = {
    product: ListedProductFragmentApi;
    listIndex: number;
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    isProductInComparison: boolean;
    toggleProductInComparison: () => void;
    isProductInWishlist: boolean;
    toggleProductInWishlist: () => void;
} & FunctionComponentProps;

const getDataTestId = (catalogNumber: string) => 'blocks-product-list-listeditem-' + catalogNumber;

export const ProductListItem = forwardRef<HTMLLIElement, ProductItemProps>(
    (
        {
            product,
            listIndex,
            gtmProductListName,
            gtmMessageOrigin,
            isProductInComparison,
            toggleProductInComparison,
            isProductInWishlist,
            toggleProductInWishlist,
            className,
        },
        ref,
    ) => {
        const { url } = useDomainConfig();
        const { t } = useTranslation();

        return (
            <li
                data-testid={getDataTestId(product.catalogNumber)}
                ref={ref}
                className={twMergeCustom(
                    'relative flex flex-col justify-between gap-3 border-b border-greyLighter p-3 text-left lg:hover:z-above lg:hover:bg-white lg:hover:shadow-xl',
                    className,
                )}
            >
                {gtmProductListName === GtmProductListNameType.wishlist && (
                    <button
                        className="absolute right-3 z-above flex h-5 w-5 cursor-pointer items-center justify-center rounded-full border-none bg-whitesmoke p-0 outline-none transition hover:bg-blueLight"
                        data-testid={getDataTestId(product.catalogNumber) + '-wishlist-remove'}
                        title={t('Remove from wishlist')}
                        onClick={toggleProductInWishlist}
                    >
                        <RemoveBoldIcon className="mx-auto w-2 basis-2" />
                    </button>
                )}

                <ExtendedNextLink
                    className="flex h-full flex-col gap-3 no-underline hover:no-underline"
                    href={product.slug}
                    type={product.isMainVariant ? 'productMainVariant' : 'product'}
                    onClick={() => onGtmProductClickEventHandler(product, gtmProductListName, listIndex, url)}
                >
                    <div className="relative">
                        <Image
                            alt={product.mainImage?.name || product.fullName}
                            className="mx-auto h-40 w-auto"
                            height={160}
                            src={product.mainImage?.url}
                            width={320}
                        />
                        {!!product.flags.length && (
                            <div className="absolute top-3 left-4 flex flex-col">
                                <ProductFlags flags={product.flags} />
                            </div>
                        )}
                    </div>

                    <div
                        className="h-10 overflow-hidden text-lg font-bold leading-5 text-dark"
                        data-testid={getDataTestId(product.catalogNumber) + '-name'}
                    >
                        {product.fullName}
                    </div>

                    <ProductPrice productPrice={product.price} />

                    <div className="flex flex-col gap-1 text-sm text-black">
                        <div>{product.availability.name}</div>
                        <ProductAvailableStoresCount
                            availableStoresCount={product.availableStoresCount}
                            isMainVariant={product.isMainVariant}
                        />
                    </div>
                </ExtendedNextLink>

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

                <ProductAction
                    gtmMessageOrigin={gtmMessageOrigin}
                    gtmProductListName={gtmProductListName}
                    listIndex={listIndex}
                    product={product}
                />
            </li>
        );
    },
);

ProductListItem.displayName = 'ProductItem';
