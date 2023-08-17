import {
    ProductDetail,
    ProductDetailCode,
    ProductDetailHeading,
    ProductDetailInfo,
    ProductDetailPrefix,
} from './ProductDetaiElements';
import { ProductDetailAccessories } from './ProductDetailAccessories';
import { ProductDetailTabs } from './ProductDetailTabs';
import { ProductVariantsTable } from './ProductVariantsTable/ProductVariantsTable';
import { ProductMetadata } from 'components/Basic/Head/ProductMetadata';
import { Webline } from 'components/Layout/Webline/Webline';
import { getUrlWithoutGetParameters } from 'helpers/parsing/urlParsing';
import { useGtmProductDetailViewEvent } from 'gtm/hooks/useGtmProductDetailViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/router';
import { useMemo } from 'react';
import { ProductDetailGallery } from './ProductDetailGallery';
import { MainVariantDetailFragmentApi } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { ImageSizesFragmentApi } from 'graphql/requests/images/fragments/ImageSizesFragment.generated';

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
                    <ProductDetailGallery
                        dataTestId={TEST_IDENTIFIER + 'gallery'}
                        images={mainVariantImagesWithVariantImages}
                        productName={product.name}
                        flags={product.flags}
                    />
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
            <Webline dataTestId={TEST_IDENTIFIER + 'accessories'} className="mt-5">
                <ProductDetailAccessories accessories={product.accessories} />
            </Webline>
        </>
    );
};
