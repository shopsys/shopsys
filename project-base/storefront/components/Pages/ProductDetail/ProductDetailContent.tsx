import {
    ProductDetail,
    ProductDetailCode,
    ProductDetailHeading,
    ProductDetailImage,
    ProductDetailInfo,
    ProductDetailPrefix,
} from './ProductDetaiElements';
import { ProductDetailAccessories } from './ProductDetailAccessories';
import { ProductDetailAddToCart } from './ProductDetailAddToCart';
import { ProductDetailAvailability } from './ProductDetailAvailability';
import { ProductDetailAvailabilityList } from './ProductDetailAvailabilityList';
import { ProductDetailGallery } from './ProductDetailGallery';
import { ProductDetailTabs } from './ProductDetailTabs';
import { ProductMetadata } from 'components/Basic/Head/ProductMetadata';
import { ProductCompareButton } from 'components/Blocks/Product/ButtonsAction/ProductCompareButton';
import { Webline } from 'components/Layout/Webline/Webline';
import { ProductDetailFragmentApi } from 'graphql/generated';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { useComparison } from 'hooks/comparison/useComparison';
import { useGtmProductDetailViewEvent } from 'hooks/gtm/useGtmProductDetailViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/router';
import { Fragment, useRef } from 'react';
import { ProductWishlistButton } from 'components/Blocks/Product/ButtonsAction/ProductWishlistButton';
import { useWishlist } from 'hooks/useWishlist';

type ProductDetailContentProps = {
    product: ProductDetailFragmentApi;
    fetching: boolean;
};

const TEST_IDENTIFIER = 'pages-productdetail-';

export const ProductDetailContent: FC<ProductDetailContentProps> = ({ product, fetching }) => {
    const t = useTypedTranslationFunction();
    const scrollTarget = useRef<HTMLUListElement>(null);
    const router = useRouter();
    const { isProductInComparison, handleProductInComparison } = useComparison();
    const { handleProductInWishlist, isProductInWishlist } = useWishlist();

    useGtmProductDetailViewEvent(product, getUrlWithoutGetParameters(router.asPath), fetching);

    return (
        // the key helps to re-mount the component when navigating between different products, which prevents the components from keeping an unwanted state
        <Fragment key={product.uuid}>
            <ProductMetadata product={product} />
            <Webline>
                <ProductDetail>
                    <ProductDetailImage>
                        <ProductDetailGallery
                            flags={product.flags}
                            images={product.images}
                            productName={product.name}
                            videoIds={product.productVideos}
                        />
                    </ProductDetailImage>
                    <ProductDetailInfo>
                        <ProductDetailPrefix dataTestId={TEST_IDENTIFIER + 'prefix'}>
                            {product.namePrefix}
                        </ProductDetailPrefix>
                        <ProductDetailHeading dataTestId={TEST_IDENTIFIER + 'name'}>
                            {product.name} {product.nameSuffix}
                        </ProductDetailHeading>
                        <ProductDetailCode dataTestId={TEST_IDENTIFIER + 'code'}>
                            {t('Code')}: {product.catalogNumber}
                        </ProductDetailCode>
                        <div className="mb-5" data-testid={TEST_IDENTIFIER + 'short-description'}>
                            {product.shortDescription}
                        </div>
                        <ProductDetailAddToCart product={product} />
                        <ProductDetailAvailability scrollTarget={scrollTarget} product={product} />
                        <div className="mt-3 flex flex-col gap-4 vl:flex-row">
                            <ProductCompareButton
                                isMainVariant={product.isMainVariant}
                                isWithText
                                isProductInComparison={isProductInComparison(product.uuid)}
                                onProductInComparisonClick={() => handleProductInComparison(product.uuid)}
                            />
                            <ProductWishlistButton
                                isMainVariant={product.isMainVariant}
                                isWithText
                                isInWishlist={isProductInWishlist(product.uuid)}
                                handleProductInWishlist={() => handleProductInWishlist(product.uuid)}
                            />
                        </div>
                    </ProductDetailInfo>
                </ProductDetail>
            </Webline>
            <Webline dataTestId={TEST_IDENTIFIER + 'description'}>
                <ProductDetailTabs description={product.description} parameters={product.parameters} />
            </Webline>
            <Webline dataTestId={TEST_IDENTIFIER + 'availability'}>
                <ProductDetailAvailabilityList ref={scrollTarget} storeAvailabilities={product.storeAvailabilities} />
            </Webline>
            <Webline dataTestId={TEST_IDENTIFIER + 'accessories'}>
                <ProductDetailAccessories accessories={product.accessories} />
            </Webline>
        </Fragment>
    );
};
