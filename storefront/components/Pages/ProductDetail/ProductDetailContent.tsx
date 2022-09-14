import { ProductDetailAccessories } from './ProductDetailAccessories/ProductDetailAccessories';
import { ProductDetailAddToCart } from './ProductDetailAddToCart/ProductDetailAddToCart';
import {
    ProductDetailCodeStyled,
    ProductDetailHeadingStyled,
    ProductDetailImageStyled,
    ProductDetailInfoStyled,
    ProductDetailPrefixStyled,
    ProductDetailShortDescriptionStyled,
    ProductDetailStyled,
} from './ProductDetailContent.style';
import { ProductDetailGallery } from './ProductDetailGallery';
import { ProductDetailAvailability } from './ProductDetailStoresAvailability/ProductDetailAvailability/ProductDetailAvailability';
import { ProductDetailAvailabilityList } from './ProductDetailStoresAvailability/ProductDetailAvailabilityList/ProductDetailAvailabilityList';
import { ProductDetailTabs } from './ProductDetailTabs';
import { ProductMetadata } from 'components/Basic/Head/ProductMetadata/ProductMetadata';
import { Webline } from 'components/Layout/Webline/Webline';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { useGtmProductDetailView } from 'hooks/gtm/useGtmProductDetailView';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/router';
import { FC, Fragment, useRef } from 'react';
import { ProductDetailType } from 'types/product';

type ProductDetailContentProps = {
    product: ProductDetailType;
    fetching: boolean;
};

const TEST_IDENTIFIER = 'pages-productdetail-';

export const ProductDetailContent: FC<ProductDetailContentProps> = ({ product, fetching }) => {
    const t = useTypedTranslationFunction();
    const scrollTarget = useRef<HTMLUListElement>(null);
    const router = useRouter();
    useGtmProductDetailView(product, getUrlWithoutGetParameters(router.asPath), fetching);

    return (
        // the key helps to re-mount the component when navigating between different products, which prevents the components from keeping an unwanted state
        <Fragment key={product.uuid}>
            <ProductMetadata product={product} />
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
            <Webline testIdentifier={TEST_IDENTIFIER + 'description'}>
                <ProductDetailTabs description={product.description} parameters={product.parameters} />
            </Webline>
            <Webline testIdentifier={TEST_IDENTIFIER + 'availability'}>
                <ProductDetailAvailabilityList ref={scrollTarget} storeAvailabilities={product.storeAvailabilities} />
            </Webline>
            <Webline testIdentifier={TEST_IDENTIFIER + 'accessories'}>
                <ProductDetailAccessories accessories={product.accessories} />
            </Webline>
        </Fragment>
    );
};
