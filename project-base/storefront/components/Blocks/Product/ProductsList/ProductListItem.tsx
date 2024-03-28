import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { ProductAvailableStoresCount } from 'components/Blocks/Product/ProductAvailableStoresCount';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { onGtmProductClickEventHandler } from 'gtm/handlers/onGtmProductClickEventHandler';
import { twMergeCustom } from 'helpers/twMerge';
import { forwardRef } from 'react';
import { FunctionComponentProps } from 'types/globals';

type ProductItemProps = {
    product: TypeListedProductFragment;
    listIndex: number;
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
} & FunctionComponentProps;

export const ProductListItem = forwardRef<HTMLLIElement, ProductItemProps>(
    ({ product, listIndex, gtmProductListName, className }, ref) => {
        const { url } = useDomainConfig();

        return (
            <li
                ref={ref}
                tid={TIDs.blocks_product_list_listeditem_ + product.catalogNumber}
                className={twMergeCustom(
                    'relative flex select-none flex-col justify-between gap-3 border-b border-greyLighter p-3 text-left lg:hover:z-above lg:hover:bg-white lg:hover:shadow-xl',
                    className,
                )}
            >
                <ExtendedNextLink
                    className="flex h-full select-none flex-col gap-3 no-underline hover:no-underline"
                    draggable={false}
                    href={product.slug}
                    type={product.isMainVariant ? 'productMainVariant' : 'product'}
                    onClick={() => onGtmProductClickEventHandler(product, gtmProductListName, listIndex, url)}
                >
                    <div className="relative flex h-56 items-center justify-center">
                        <Image
                            alt={product.mainImage?.name || product.fullName}
                            className="max-h-full object-contain"
                            draggable={false}
                            height={250}
                            src={product.mainImage?.url}
                            width={250}
                        />

                        {!!product.flags.length && (
                            <div className="absolute top-3 left-4 flex flex-col">
                                <ProductFlags flags={product.flags} />
                            </div>
                        )}
                    </div>

                    <div className="h-10 overflow-hidden text-lg font-bold leading-5 text-dark">{product.fullName}</div>

                    <ProductPrice productPrice={product.price} />

                    <div className="flex flex-col gap-1 text-sm text-black">
                        <div>{product.availability.name}</div>
                        <ProductAvailableStoresCount
                            availableStoresCount={product.availableStoresCount}
                            isMainVariant={product.isMainVariant}
                        />
                    </div>
                </ExtendedNextLink>

                <div className="flex justify-end gap-2" />
            </li>
        );
    },
);

ProductListItem.displayName = 'ProductItem';
