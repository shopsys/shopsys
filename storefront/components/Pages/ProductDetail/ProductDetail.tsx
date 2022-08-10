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
import ProductDetailGallery from './ProductDetailGallery';
import ProductDetailAvailability from './ProductDetailStoresAvailability/ProductDetailAvailability';
import ProductDetailAvailabilityList from './ProductDetailStoresAvailability/ProductDetailAvailabilityList';
import ProductDetailTabs from './ProductDetailTabs';
import Webline from 'components/Layout/Webline';
import { useGtmProductDetailView } from 'hooks/gtm/useGtmProductDetailView';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useRouter } from 'next/router';
import { FC, useRef } from 'react';
import { ProductDetailType } from 'types/product';
import { getUrlWithoutGetParameters } from 'utils/getUrlWithoutGetParameters';

type ProductDetailProps = {
    product: ProductDetailType;
    fetching: boolean;
};

const TEST_IDENTIFIER = 'pages-productdetail-';
/**
 * Product Detail page component
 */
const ProductDetail: FC<ProductDetailProps> = ({ product, fetching }) => {
    const t = useTypedTranslationFunction();
    const scrollTarget = useRef<HTMLUListElement>(null);
    const router = useRouter();
    useGtmProductDetailView(product, getUrlWithoutGetParameters(router.asPath), fetching);

    return (
        <>
            <Webline>
                <ProductDetailStyled>
                    <ProductDetailImageStyled data-testid={TEST_IDENTIFIER + 'gallery'}>
                        <ProductDetailGallery
                            flags={product.flags}
                            images={product.images}
                            productName={product.name}
                        />
                    </ProductDetailImageStyled>
                    <ProductDetailInfoStyled>
                        <ProductDetailPrefixStyled data-testid={TEST_IDENTIFIER + 'prefix'}>
                            {product.namePrefix}
                        </ProductDetailPrefixStyled>
                        <ProductDetailHeadingStyled data-testid={TEST_IDENTIFIER + 'name'}>
                            {product.name} {product.nameSuffix}
                        </ProductDetailHeadingStyled>
                        <ProductDetailCodeStyled data-testid={TEST_IDENTIFIER + 'code'}>
                            {t('Code')}: {product.catalogNumber}
                        </ProductDetailCodeStyled>
                        <ProductDetailShortDescriptionStyled data-testid={TEST_IDENTIFIER + 'short-description'}>
                            {product.shortDescription}
                        </ProductDetailShortDescriptionStyled>
                        <ProductDetailAddToCart product={product} />
                        <ProductDetailAvailability scrollTarget={scrollTarget} product={product} />
                    </ProductDetailInfoStyled>
                </ProductDetailStyled>
            </Webline>
            <Webline data-testid={TEST_IDENTIFIER + 'description'}>
                <ProductDetailTabs description={product.description} parameters={product.parameters} />
            </Webline>
            <Webline data-testid={TEST_IDENTIFIER + 'availability'}>
                <ProductDetailAvailabilityList ref={scrollTarget} storeAvailabilities={product.storeAvailabilities} />
            </Webline>
            <Webline data-testid={TEST_IDENTIFIER + 'accessories'}>
                <ProductDetailAccessories accessories={product.accessories} />
            </Webline>
        </>
    );
};

/* @component */
export default ProductDetail;
