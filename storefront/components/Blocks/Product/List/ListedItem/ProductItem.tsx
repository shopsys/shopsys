import {
    ProductItemImageStyled,
    ProductItemInfoStyled,
    ProductItemInStyled,
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
            <NextLink href={props.detailSlug} passHref>
                <ProductItemInStyled>
                    <ProductItemImageStyled>
                        <Image image={props.image} alt={props.name} />
                        <ProductFlags flags={props.flags} />
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
                    <ProductAction {...props} />
                </ProductItemInStyled>
            </NextLink>
        </ProductItemStyled>
    );
};

export default ProductItem;
