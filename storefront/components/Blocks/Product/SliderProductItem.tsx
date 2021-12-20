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
import { SliderProductItemType } from 'types/product';

const ProductItem: FC<SliderProductItemType> = (props) => {
    const testIdentifier = 'blocks-product-sliderproductitem-';

    return (
        <SliderProductItemStyled className="keen-slider__slide" data-testid={testIdentifier + props.catalogNumber}>
            <SliderProductItemInStyled>
                <NextLink href={props.slug} passHref>
                    <SliderProductItemLinkStyled>
                        <SliderProductItemImageStyled data-testid={testIdentifier + 'image'}>
                            <Image image={props.image} alt={props.name} />
                            <SliderProductItemFlagsStyled>
                                <ProductFlags flags={props.flags} />
                            </SliderProductItemFlagsStyled>
                        </SliderProductItemImageStyled>
                        <SliderProductItemInfoStyled>
                            <SliderProductItemTitleStyled data-testid={testIdentifier + 'name'}>
                                {props.name}
                            </SliderProductItemTitleStyled>
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
                        </SliderProductItemInfoStyled>
                    </SliderProductItemLinkStyled>
                </NextLink>
                <ProductAction {...props} />
            </SliderProductItemInStyled>
        </SliderProductItemStyled>
    );
};

export default ProductItem;
