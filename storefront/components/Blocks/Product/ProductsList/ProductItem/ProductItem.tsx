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
import NextLink from 'next/link';
import { FC, useCallback } from 'react';
import { useShopsysSelector } from 'redux/main';
import { GtmListNameType } from 'types/gtm';
import { ListedProductType } from 'types/product';
import { onClickProductDetailGtmEventHandler } from 'utils/Gtm/EventHandlers';

type ProductItemProps = {
    product: ListedProductType;
    listIndex: number;
    gtmListName: GtmListNameType;
};

export const ProductItem: FC<ProductItemProps> = (props) => {
    const testIdentifier = 'blocks-product-list-listeditem-' + props.product.catalogNumber;
    const { url } = useShopsysSelector((state) => state.domain);

    const onProductDetailRedirectHandler = useCallback(
        async (product: ListedProductType, listName: GtmListNameType, index: number) => {
            await onClickProductDetailGtmEventHandler(product, listName, index, url);
        },
        [url],
    );

    return (
        <ProductItemStyled data-testid={testIdentifier}>
            <ProductItemInStyled>
                <NextLink href={props.product.slug} passHref>
                    <ProductItemLinkStyled
                        onClick={() =>
                            onProductDetailRedirectHandler(props.product, props.gtmListName, props.listIndex)
                        }
                    >
                        <ProductItemImageStyled>
                            <Image image={props.product.image} type="list" alt={props.product.fullName} />
                            <ProductItemFlagsStyled>
                                <ProductFlags flags={props.product.flags} />
                            </ProductItemFlagsStyled>
                        </ProductItemImageStyled>
                        <ProductItemInfoStyled>
                            <ProductItemTitleStyled>{props.product.fullName}</ProductItemTitleStyled>
                            <ProductPrice {...props.product.price} />
                            <ProductAvailabilityStyled>
                                {props.product.availability.name}
                                <ProductAvailableStoresCount
                                    isMainVariant={props.product.isMainVariant}
                                    availableStoresCount={props.product.availableStoresCount}
                                />
                                <ProductExposedStoresCount
                                    isMainVariant={props.product.isMainVariant}
                                    exposedStoresCount={props.product.exposedStoresCount}
                                />
                            </ProductAvailabilityStyled>
                        </ProductItemInfoStyled>
                    </ProductItemLinkStyled>
                </NextLink>
                <ProductAction product={props.product} gtmListName={props.gtmListName} listIndex={props.listIndex} />
            </ProductItemInStyled>
        </ProductItemStyled>
    );
};
