import {
    ProductItemFlagsStyled,
    ProductItemImageStyled,
    ProductItemInfoStyled,
    ProductItemInStyled,
    ProductItemLinkStyled,
    ProductItemStyled,
    ProductItemTitleStyled,
} from './ProductItem.style';
import Image from 'components/Basic/Image/Image';
import ProductAction from 'components/Blocks/Product/Action/ProductAction';
import { ProductAvailabilityStyled } from 'components/Blocks/Product/Availability/ProductAvailability.style';
import ProductAvailableStoresCount from 'components/Blocks/Product/Availability/ProductAvailableStoresCount';
import ProductExposedStoresCount from 'components/Blocks/Product/Availability/ProductExposedStoresCount';
import ProductFlags from 'components/Blocks/Product/Flags/ProductFlags';
import ProductPrice from 'components/Blocks/Product/Price/ProductPrice';
import NextLink from 'next/link';
import { FC } from 'react';
import { ListedProductType } from 'types/product';

const ProductItem: FC<ListedProductType> = (props) => {
    const testIdentifier = 'blocks-product-list-listeditem-' + props.catalogNumber;

    return (
        <ProductItemStyled data-testid={testIdentifier}>
            <ProductItemInStyled>
                <NextLink href={props.slug} passHref>
                    <ProductItemLinkStyled>
                        <ProductItemImageStyled>
                            <Image image={props.image} type="list" alt={props.name} />
                            <ProductItemFlagsStyled>
                                <ProductFlags flags={props.flags} />
                            </ProductItemFlagsStyled>
                        </ProductItemImageStyled>
                        <ProductItemInfoStyled>
                            <ProductItemTitleStyled>{props.name}</ProductItemTitleStyled>
                            <ProductPrice {...props.price} />
                            <ProductAvailabilityStyled>
                                {props.availability}
                                <ProductAvailableStoresCount
                                    isMainVariant={props.isMainVariant}
                                    availableStoresCount={props.availableStoresCount}
                                />
                                <ProductExposedStoresCount
                                    isMainVariant={props.isMainVariant}
                                    exposedStoresCount={props.exposedStoresCount}
                                />
                            </ProductAvailabilityStyled>
                        </ProductItemInfoStyled>
                    </ProductItemLinkStyled>
                </NextLink>
                <ProductAction {...props} />
            </ProductItemInStyled>
        </ProductItemStyled>
    );
};

export default ProductItem;
