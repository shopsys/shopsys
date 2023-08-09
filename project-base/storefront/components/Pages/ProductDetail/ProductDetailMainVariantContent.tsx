import {
    ProductDetail,
    ProductDetailCode,
    ProductDetailHeading,
    ProductDetailImage,
    ProductDetailInfo,
    ProductDetailPrefix,
} from './ProductDetaiElements';
import { ProductDetailAccessories } from './ProductDetailAccessories';
import { ProductDetailGallery } from './ProductDetailGallery';
import { ProductDetailTabs } from './ProductDetailTabs';
import { ProductVariantsTable } from './ProductVariantsTable/ProductVariantsTable';
import { ProductMetadata } from 'components/Basic/Head/ProductMetadata';
import { Webline } from 'components/Layout/Webline/Webline';
import { ImageSizesFragmentApi, MainVariantDetailFragmentApi } from 'graphql/generated';
import { getUrlWithoutGetParameters } from 'helpers/parsing/urlParsing';
import { useGtmProductDetailViewEvent } from 'gtm/hooks/useGtmProductDetailViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/router';
import { useMemo } from 'react';

type ProductDetailMainVariantContentProps = {
    product: MainVariantDetailFragmentApi;
    fetching: boolean;
};

const TEST_IDENTIFIER = 'pages-productdetail-';

export const ProductDetailMainVariantContent: FC<ProductDetailMainVariantContentProps> = ({ product, fetching }) => {
    const router = useRouter();
    const t = useTypedTranslationFunction();
    const mainVariantImagesWithVariantImages = useMemo(() => {
        const variantImages = product.variants.reduce((mappedVariantImages, variant) => {
            if (variant.mainImage) {
                mappedVariantImages.push(variant.mainImage);
            }

            return mappedVariantImages;
        }, [] as ImageSizesFragmentApi[]);

        return [...product.images, ...variantImages];
    }, [product]);

    useGtmProductDetailViewEvent(product, getUrlWithoutGetParameters(router.asPath), fetching);

    return (
        <>
            <ProductMetadata product={product} />
            <Webline>
                <ProductDetail>
                    <ProductDetailImage dataTestId={TEST_IDENTIFIER + 'gallery'}>
                        <ProductDetailGallery
                            images={mainVariantImagesWithVariantImages}
                            productName={product.name}
                            flags={product.flags}
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
                    </ProductDetailInfo>
                </ProductDetail>
            </Webline>
            <Webline dataTestId={TEST_IDENTIFIER + 'variants'}>
                <ProductVariantsTable variants={product.variants} isSellingDenied={product.isSellingDenied} />
            </Webline>
            <Webline dataTestId={TEST_IDENTIFIER + 'description'}>
                <ProductDetailTabs description={product.description} parameters={product.parameters} />
            </Webline>
            <Webline dataTestId={TEST_IDENTIFIER + 'accessories'}>
                <ProductDetailAccessories accessories={product.accessories} />
            </Webline>
        </>
    );
};
