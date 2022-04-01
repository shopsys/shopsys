import ProductAction from './Action/ProductAction';
import { ProductAvailabilityStyled } from './Availability/ProductAvailability.style';
import ProductAvailableStoresCount from './Availability/ProductAvailableStoresCount';
import ProductExposedStoresCount from './Availability/ProductExposedStoresCount';
import ProductFlags from './Flags/ProductFlags';
import ProductPrice from './Price/ProductPrice';
import {
    SliderProductItemFlagsStyled,
    SliderProductItemImageStyled,
    SliderProductItemInfoStyled,
    SliderProductItemInStyled,
    SliderProductItemLinkStyled,
    SliderProductItemStyled,
    SliderProductItemTitleStyled,
} from './SliderProductItem.style';
import Image from 'components/Basic/Image/Image';
import NextLink from 'next/link';
import { FC } from 'react';
import { SliderProductItemType } from 'types/product';
import { GtmListNameType } from 'types/gtm';
import { onClickProductDetailGtmEvent } from 'utils/Gtm/EventHandlers';

type ProductItemProps = {
    product: SliderProductItemType;
    gtmListName: GtmListNameType;
    listIndex: number;
};

const ProductItem: FC<ProductItemProps> = (props) => {
    const testIdentifier = 'blocks-product-sliderproductitem-';

    return (
        <SliderProductItemStyled
            className="keen-slider__slide"
            data-testid={testIdentifier + props.product.catalogNumber}
        >
            <SliderProductItemInStyled>
                <NextLink href={props.product.slug} passHref>
                    <SliderProductItemLinkStyled
                        onClick={() => onClickProductDetailGtmEvent(props.product, props.gtmListName, props.listIndex)}
                    >
                        <SliderProductItemImageStyled data-testid={testIdentifier + 'image'}>
                            <Image image={props.product.image} type="list" alt={props.product.fullName} />
                            <SliderProductItemFlagsStyled>
                                <ProductFlags flags={props.product.flags} />
                            </SliderProductItemFlagsStyled>
                        </SliderProductItemImageStyled>
                        <SliderProductItemInfoStyled>
                            <SliderProductItemTitleStyled data-testid={testIdentifier + 'name'}>
                                {props.product.fullName}
                            </SliderProductItemTitleStyled>
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
                        </SliderProductItemInfoStyled>
                    </SliderProductItemLinkStyled>
                </NextLink>
                <ProductAction product={props.product} gtmListName={props.gtmListName} />
            </SliderProductItemInStyled>
        </SliderProductItemStyled>
    );
};

export default ProductItem;
