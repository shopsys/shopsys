import {
    ProductItemFlagsStyled,
    ProductItemImageStyled,
    ProductItemInfoStyled,
    ProductItemInStyled,
    ProductItemLinkStyled,
    ProductItemStyled,
    ProductItemTitleStyled,
} from './ProductItem.style';
import { Image } from 'components/Basic/Image/Image';
import { ProductAction } from 'components/Blocks/Product/Action/ProductAction';
import { ProductAvailabilityStyled } from 'components/Blocks/Product/Availability/ProductAvailability.style';
import { ProductAvailableStoresCount } from 'components/Blocks/Product/Availability/ProductAvailableStoresCount';
import { ProductExposedStoresCount } from 'components/Blocks/Product/Availability/ProductExposedStoresCount';
import { ProductFlags } from 'components/Blocks/Product/Flags/ProductFlags';
import { ProductPrice } from 'components/Blocks/Product/Price/ProductPrice';
import { onClickProductDetailGtmEventHandler } from 'helpers/gtm/eventHandlers';
import NextLink from 'next/link';
import { FC, useCallback } from 'react';
import { useShopsysSelector } from 'redux/main';
import { GtmListNameType } from 'types/gtm';
import { ListedProductType } from 'types/product';

type ProductItemProps = {
    product: ListedProductType;
    listIndex: number;
    gtmListName: GtmListNameType;
};

const TEST_IDENTIFIER = 'blocks-product-list-listeditem';

export const ProductItem: FC<ProductItemProps> = ({ product, listIndex, gtmListName }) => {
    const { url } = useShopsysSelector((state) => state.domain);

    const onProductDetailRedirectHandler = useCallback(
        async (product: ListedProductType, listName: GtmListNameType, index: number) => {
            await onClickProductDetailGtmEventHandler(product, listName, index, url);
        },
        [url],
    );

    return (
        <ProductItemStyled data-testid={TEST_IDENTIFIER + product.catalogNumber}>
            <ProductItemInStyled>
                <NextLink href={product.slug} passHref>
                    <ProductItemLinkStyled
                        onClick={() => onProductDetailRedirectHandler(product, gtmListName, listIndex)}
                    >
                        <ProductItemImageStyled>
                            <Image image={product.image} type="list" alt={product.fullName} />
                            <ProductItemFlagsStyled>
                                <ProductFlags flags={product.flags} />
                            </ProductItemFlagsStyled>
                        </ProductItemImageStyled>
                        <ProductItemInfoStyled>
                            <ProductItemTitleStyled>{product.fullName}</ProductItemTitleStyled>
                            <ProductPrice productPrice={product.price} />
                            <ProductAvailabilityStyled>
                                {product.availability.name}
                                <ProductAvailableStoresCount
                                    isMainVariant={product.isMainVariant}
                                    availableStoresCount={product.availableStoresCount}
                                />
                                <ProductExposedStoresCount
                                    isMainVariant={product.isMainVariant}
                                    exposedStoresCount={product.exposedStoresCount}
                                />
                            </ProductAvailabilityStyled>
                        </ProductItemInfoStyled>
                    </ProductItemLinkStyled>
                </NextLink>
                <ProductAction product={product} gtmListName={gtmListName} listIndex={listIndex} />
            </ProductItemInStyled>
        </ProductItemStyled>
    );
};
