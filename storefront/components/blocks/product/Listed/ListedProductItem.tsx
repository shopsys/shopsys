import {
    ListedProductItemImageStyled,
    ListedProductItemInfoStyled,
    ListedProductItemInStyled,
    ListedProductItemStyled,
    ListedProductItemTitleStyled,
} from './ListedProductItem.style';
import { FC } from 'react';
import Link from 'next/link';
import { ListedProductItemType } from '../types';
import ProductAction from '../Action/ProductAction';
import { ProductAvailabilityStyled } from '../Availability/ProductAvailability.style';
import ProductAvailableStoresCount from '../Availability/ProductAvailableStoresCount';
import ProductExposedStoresCount from '../Availability/ProductExposedStoresCount';
import ProductFlags from '../Flags/ProductFlags';
import ProductPrice from '../Price/ProductPrice';
import ShopsysImage from '../../../basic/ShopsysImage/ShopsysImage';

const ProductItem: FC<ListedProductItemType> = (props) => {
    return (
        <ListedProductItemStyled className="keen-slider__slide">
            <Link href={'/' + props.detailSlug} passHref>
                <ListedProductItemInStyled>
                    <ListedProductItemImageStyled>
                        <ShopsysImage image={props.image} alt={props.name} />
                        <ProductFlags flags={props.flags} />
                    </ListedProductItemImageStyled>
                    <ListedProductItemInfoStyled>
                        <ListedProductItemTitleStyled>{props.name}</ListedProductItemTitleStyled>
                        <ProductPrice {...props.price} />
                        <ProductAvailabilityStyled>
                            {props.availability}
                            <ProductAvailableStoresCount {...props} />
                            <ProductExposedStoresCount {...props} />
                        </ProductAvailabilityStyled>
                    </ListedProductItemInfoStyled>
                    <ProductAction {...props} />
                </ListedProductItemInStyled>
            </Link>
        </ListedProductItemStyled>
    );
};

export default ProductItem;
