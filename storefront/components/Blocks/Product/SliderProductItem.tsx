import {
    SliderProductItemImageStyled,
    SliderProductItemInfoStyled,
    SliderProductItemInStyled,
    SliderProductItemStyled,
    SliderProductItemTitleStyled,
} from './SliderProductItem.style';
import { FC } from 'react';
import Image from '../../Basic/Image/Image';
import Link from 'next/link';
import ProductAction from './Action/ProductAction';
import { ProductAvailabilityStyled } from './Availability/ProductAvailability.style';
import ProductAvailableStoresCount from './Availability/ProductAvailableStoresCount';
import ProductExposedStoresCount from './Availability/ProductExposedStoresCount';
import ProductFlags from './Flags/ProductFlags';
import ProductPrice from './Price/ProductPrice';
import { SliderProductItemType } from './types';

const ProductItem: FC<SliderProductItemType> = (props) => {
    return (
        <SliderProductItemStyled className="keen-slider__slide">
            <Link href={props.detailSlug} passHref>
                <SliderProductItemInStyled>
                    <SliderProductItemImageStyled>
                        <Image image={props.image} alt={props.name} />
                        <ProductFlags flags={props.flags} />
                    </SliderProductItemImageStyled>
                    <SliderProductItemInfoStyled>
                        <SliderProductItemTitleStyled>{props.name}</SliderProductItemTitleStyled>
                        <ProductPrice {...props.price} />
                        <ProductAvailabilityStyled>
                            {props.availability}
                            <ProductAvailableStoresCount {...props} />
                            <ProductExposedStoresCount {...props} />
                        </ProductAvailabilityStyled>
                    </SliderProductItemInfoStyled>
                    <ProductAction {...props} />
                </SliderProductItemInStyled>
            </Link>
        </SliderProductItemStyled>
    );
};

export default ProductItem;
