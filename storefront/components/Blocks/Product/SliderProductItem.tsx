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

const TEST_IDENTIFIER = 'blocks-product-sliderproductitem-';

export const SliderProductItem: FC<SliderProductItemProps> = ({ product, gtmListName, listIndex }) => {
    const { url } = useShopsysSelector((state) => state.domain);

    return (
        <SliderProductItemStyled className="keen-slider__slide" data-testid={TEST_IDENTIFIER + product.catalogNumber}>
            <SliderProductItemInStyled>
                <NextLink href={product.slug} passHref>
                    <SliderProductItemLinkStyled
                        onClick={() => onClickProductDetailGtmEventHandler(product, gtmListName, listIndex, url)}
                    >
                        <SliderProductItemImageStyled data-testid={TEST_IDENTIFIER + 'image'}>
                            <Image image={product.image} type="list" alt={product.fullName} />
                            <SliderProductItemFlagsStyled>
                                <ProductFlags flags={product.flags} />
                            </SliderProductItemFlagsStyled>
                        </SliderProductItemImageStyled>
                        <SliderProductItemInfoStyled>
                            <SliderProductItemTitleStyled data-testid={TEST_IDENTIFIER + 'name'}>
                                {product.fullName}
                            </SliderProductItemTitleStyled>
                            <ProductPrice productPrice={product.price} />
                            <ProductAvailabilityStyled>
                                {product.availability.name}
                                <ProductAvailableStoresCount
                                    isMainVariant={product.isMainVariant}
                                    availableStoresCount={product.availableStoresCount}
                                />
                                <ProductExposedStoresCount
                                    isMainVariant={product.isMainVariant}
                                    exposedStoresCount={product.exposedStoresCount}
                                />
                            </ProductAvailabilityStyled>
                        </SliderProductItemInfoStyled>
                    </SliderProductItemLinkStyled>
                </NextLink>
                <ProductAction product={product} gtmListName={gtmListName} listIndex={listIndex} />
            </SliderProductItemInStyled>
        </SliderProductItemStyled>
    );
};
