import {
    SliderProductItemFlagsStyled,
    SliderProductItemImageStyled,
    SliderProductItemInfoStyled,
    SliderProductItemInStyled,
    SliderProductItemLinkStyled,
    SliderProductItemStyled,
    SliderProductItemTitleStyled,
} from './SliderProductItem.style';
import { FC } from 'react';
import Image from 'components/Basic/Image/Image';
import NextLink from 'next/link';
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
            <SliderProductItemInStyled>
                <NextLink href={props.detailSlug} passHref>
                    <SliderProductItemLinkStyled>
                        <SliderProductItemImageStyled>
                            <Image image={props.image} alt={props.name} />
                            <SliderProductItemFlagsStyled>
                                <ProductFlags flags={props.flags} />
                            </SliderProductItemFlagsStyled>
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
                    </SliderProductItemLinkStyled>
                </NextLink>
                <ProductAction {...props} />
            </SliderProductItemInStyled>
        </SliderProductItemStyled>
    );
};

export default ProductItem;
