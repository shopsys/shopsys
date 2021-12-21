import { FC, useRef } from 'react';
import {
    ProductDetailCodeStyled,
    ProductDetailHeadingStyled,
    ProductDetailImageStyled,
    ProductDetailInfoStyled,
    ProductDetailPrefixStyled,
    ProductDetailShortDescriptionStyled,
    ProductDetailStyled,
} from './ProductDetail.style';
import ProductDetailAccessories from './ProductDetailAccessories';
import ProductDetailAddToCart from './ProductDetailAddToCart';
import ProductDetailAvailability from './ProductDetailStoresAvailability/ProductDetailAvailability';
import ProductDetailAvailabilityList from './ProductDetailStoresAvailability/ProductDetailAvailabilityList';
import ProductDetailGallery from './ProductDetailGallery';
import ProductDetailTabs from './ProductDetailTabs';
import { ProductDetailType } from 'types/product';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

type ProductDetailProps = {
    product: ProductDetailType;
};

/**
 * Product Detail page component
 */
const ProductDetail: FC<ProductDetailProps> = (props) => {
    const t = useTypedTranslationFunction();
    const scrollTarget = useRef<HTMLUListElement>(null);
    return (
        <>
            <Webline>
                <ProductDetailStyled>
                    <ProductDetailImageStyled>
                        <ProductDetailGallery images={props.product.images} productName={props.product.name} />
                    </ProductDetailImageStyled>
                    <ProductDetailInfoStyled>
                        <ProductDetailPrefixStyled>{props.product.namePrefix}</ProductDetailPrefixStyled>
                        <ProductDetailHeadingStyled>
                            {props.product.name} {props.product.nameSuffix}
                        </ProductDetailHeadingStyled>
                        <ProductDetailCodeStyled>
                            {t('Code')}: {props.product.catalogNumber}
                        </ProductDetailCodeStyled>
                        {props.product.shortDescription !== undefined && (
                            <ProductDetailShortDescriptionStyled>
                                {props.product.shortDescription}
                            </ProductDetailShortDescriptionStyled>
                        )}
                        <ProductDetailAddToCart {...props} />
                        <ProductDetailAvailability scrollTarget={scrollTarget} {...props} />
                    </ProductDetailInfoStyled>
                </ProductDetailStyled>
            </Webline>
            <Webline>
                <ProductDetailTabs description={props.product.description} parameters={props.product.parameters} />
            </Webline>
            <Webline>
                <ProductDetailAvailabilityList
                    ref={scrollTarget}
                    storeAvailabilities={props.product.storeAvailabilities}
                />
            </Webline>
            <Webline>
                <ProductDetailAccessories accessories={props.product.accessories} />
            </Webline>
        </>
    );
};

/* @component */
export default ProductDetail;
