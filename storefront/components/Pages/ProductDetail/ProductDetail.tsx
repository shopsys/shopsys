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
    const testIdentifier = 'pages-productdetail-';

    const t = useTypedTranslationFunction();
    const scrollTarget = useRef<HTMLUListElement>(null);

    return (
        <>
            <Webline>
                <ProductDetailStyled>
                    <ProductDetailImageStyled data-testid={testIdentifier + 'gallery'}>
                        <ProductDetailGallery
                            flags={props.product.flags}
                            images={props.product.images}
                            productName={props.product.name}
                        />
                    </ProductDetailImageStyled>
                    <ProductDetailInfoStyled>
                        <ProductDetailPrefixStyled data-testid={testIdentifier + 'prefix'}>
                            {props.product.namePrefix}
                        </ProductDetailPrefixStyled>
                        <ProductDetailHeadingStyled data-testid={testIdentifier + 'name'}>
                            {props.product.name} {props.product.nameSuffix}
                        </ProductDetailHeadingStyled>
                        <ProductDetailCodeStyled data-testid={testIdentifier + 'code'}>
                            {t('Code')}: {props.product.catalogNumber}
                        </ProductDetailCodeStyled>
                        <ProductDetailShortDescriptionStyled data-testid={testIdentifier + 'short-description'}>
                            {props.product.shortDescription}
                        </ProductDetailShortDescriptionStyled>
                        <ProductDetailAddToCart {...props} />
                        <ProductDetailAvailability scrollTarget={scrollTarget} {...props} />
                    </ProductDetailInfoStyled>
                </ProductDetailStyled>
            </Webline>
            <Webline data-testid={testIdentifier + 'description'}>
                <ProductDetailTabs description={props.product.description} parameters={props.product.parameters} />
            </Webline>
            <Webline data-testid={testIdentifier + 'availability'}>
                <ProductDetailAvailabilityList
                    ref={scrollTarget}
                    storeAvailabilities={props.product.storeAvailabilities}
                />
            </Webline>
            <Webline data-testid={testIdentifier + 'accessories'}>
                <ProductDetailAccessories accessories={props.product.accessories} />
            </Webline>
        </>
    );
};

/* @component */
export default ProductDetail;
