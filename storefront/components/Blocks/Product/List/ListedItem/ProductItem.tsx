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
import Image from 'components/Basic/Image/Image';
import { ListedProductType } from 'connectors/products/types';
import NextLink from 'next/link';
import ProductAction from 'components/Blocks/Product/Action/ProductAction';
import { ProductAvailabilityStyled } from 'components/Blocks/Product/Availability/ProductAvailability.style';
import ProductAvailableStoresCount from 'components/Blocks/Product/Availability/ProductAvailableStoresCount';
import ProductExposedStoresCount from 'components/Blocks/Product/Availability/ProductExposedStoresCount';
import ProductFlags from 'components/Blocks/Product/Flags/ProductFlags';
import ProductPrice from 'components/Blocks/Product/Price/ProductPrice';

const ProductItem: FC<ListedProductType> = (props) => {
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
