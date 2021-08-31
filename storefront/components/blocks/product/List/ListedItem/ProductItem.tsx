import {
    ProductItemImageStyled,
    ProductItemInfoStyled,
    ProductItemInStyled,
    ProductItemStyled,
    ProductItemTitleStyled,
} from './ProductItem.style';
import { FC } from 'react';
import Link from 'next/link';
import { ListedProductItemType } from '../../types';
import ProductAction from '../../Action/ProductAction';
import { ProductAvailabilityStyled } from '../../Availability/ProductAvailability.style';
import ProductAvailableStoresCount from '../../Availability/ProductAvailableStoresCount';
import ProductExposedStoresCount from '../../Availability/ProductExposedStoresCount';
import ProductFlags from '../../Flags/ProductFlags';
import ProductPrice from '../../Price/ProductPrice';
import ShopsysImage from '../../../../basic/ShopsysImage/ShopsysImage';

const ProductItem: FC<ListedProductItemType> = (props) => {
    return (
        <ProductItemStyled>
            <Link href={props.detailSlug} passHref>
                <ProductItemInStyled>
                    <ProductItemImageStyled>
                        <ShopsysImage image={props.image} alt={props.name} />
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
            </Link>
        </ProductItemStyled>
    );
};

export default ProductItem;
