import { ProductAction } from './Action/ProductAction';
import { ProductAvailabilityStyled } from './Availability/ProductAvailability.style';
import { ProductAvailableStoresCount } from './Availability/ProductAvailableStoresCount';
import { ProductExposedStoresCount } from './Availability/ProductExposedStoresCount';
import { ProductFlags } from './Flags/ProductFlags';
import { ProductPrice } from './Price/ProductPrice';
import {
    SliderProductItemFlagsStyled,
    SliderProductItemImageStyled,
    SliderProductItemInfoStyled,
    SliderProductItemInStyled,
    SliderProductItemLinkStyled,
    SliderProductItemStyled,
    SliderProductItemTitleStyled,
} from './SliderProductItem.style';
import { Image } from 'components/Basic/Image/Image';
import { onClickProductDetailGtmEventHandler } from 'helpers/gtm/eventHandlers';
import NextLink from 'next/link';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';
import { GtmListNameType } from 'types/gtm';
import { SliderProductItemType } from 'types/product';

type SliderProductItemProps = {
    product: SliderProductItemType;
    gtmListName: GtmListNameType;
    listIndex: number;
};

export const SliderProductItem: FC<SliderProductItemProps> = (props) => {
    const testIdentifier = 'blocks-product-sliderproductitem-';
    const { url } = useShopsysSelector((state) => state.domain);

    return (
        <SliderProductItemStyled
            className="keen-slider__slide"
            data-testid={testIdentifier + props.product.catalogNumber}
        >
            <SliderProductItemInStyled>
                <NextLink href={props.product.slug} passHref>
                    <SliderProductItemLinkStyled
                        onClick={() =>
                            onClickProductDetailGtmEventHandler(props.product, props.gtmListName, props.listIndex, url)
                        }
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
                <ProductAction product={props.product} gtmListName={props.gtmListName} listIndex={props.listIndex} />
            </SliderProductItemInStyled>
        </SliderProductItemStyled>
    );
};
