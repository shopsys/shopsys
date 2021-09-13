import {
    ProductItemFlagsStyled,
    ProductItemImageStyled,
    ProductItemInfoStyled,
    ProductItemInStyled,
    ProductItemLinkStyled,
    ProductItemStyled,
    ProductItemTitleStyled,
} from './ProductItem.style';
import { FC } from 'react';
import Image from '../../../../Basic/Image/Image';
import { ListedProductItemType } from '../../types';
import NextLink from 'next/link';
import ProductAction from '../../Action/ProductAction';
import { ProductAvailabilityStyled } from '../../Availability/ProductAvailability.style';
import ProductAvailableStoresCount from '../../Availability/ProductAvailableStoresCount';
import ProductExposedStoresCount from '../../Availability/ProductExposedStoresCount';
import ProductFlags from '../../Flags/ProductFlags';
import ProductPrice from '../../Price/ProductPrice';

const ProductItem: FC<ListedProductItemType> = (props) => {
    return (
        <ProductItemStyled>
            <ProductItemInStyled>
                <NextLink href={props.detailSlug} passHref>
                    <ProductItemLinkStyled>
                        <ProductItemImageStyled>
                            <Image image={props.image} alt={props.name} />
                            <ProductItemFlagsStyled>
                                <ProductFlags flags={props.flags} />
                            </ProductItemFlagsStyled>
                        </ProductItemImageStyled>
                        <ProductItemInfoStyled>
                            <ProductItemTitleStyled>{props.name}</ProductItemTitleStyled>
                            <ProductPrice {...props.price} />
                            <ProductAvailabilityStyled>
                                {props.availability}
                                <ProductAvailableStoresCount {...props} />
                                <ProductExposedStoresCount {...props} />
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
