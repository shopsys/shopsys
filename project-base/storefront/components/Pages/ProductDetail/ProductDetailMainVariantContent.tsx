import { ProductDetailCode, ProductDetailHeading, ProductDetailPrefix } from './ProductDetaiElements';
import { ProductDetailAccessories } from './ProductDetailAccessories';
import { ProductDetailTabs } from './ProductDetailTabs';
import { ProductVariantsTable } from './ProductVariantsTable/ProductVariantsTable';
import { ProductMetadata } from 'components/Basic/Head/ProductMetadata';
import { Webline } from 'components/Layout/Webline/Webline';
import { ImageSizesFragmentApi, MainVariantDetailFragmentApi } from 'graphql/generated';
import { getUrlWithoutGetParameters } from 'helpers/parsing/urlParsing';
import { useGtmProductDetailViewEvent } from 'gtm/hooks/useGtmProductDetailViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useMemo } from 'react';
import { ProductDetailGallery } from './ProductDetailGallery';

type ProductDetailMainVariantContentProps = {
    product: MainVariantDetailFragmentApi;
    fetching: boolean;
};

export const ProductDetailMainVariantContent: FC<ProductDetailMainVariantContentProps> = ({ product, fetching }) => {
    const router = useRouter();
    const { t } = useTranslation();
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

            <Webline className="flex flex-col gap-8">
                <div>
                    {!!(product.images.length || product.productVideos.length) && (
                        <ProductDetailGallery
                            images={mainVariantImagesWithVariantImages}
                            productName={product.name}
                            flags={product.flags}
                            videoIds={product.productVideos}
                        />
                    )}

                    <div className="gap-2">
                        <ProductDetailPrefix>{product.namePrefix}</ProductDetailPrefix>

                        <ProductDetailHeading>
                            {product.name} {product.nameSuffix}
                        </ProductDetailHeading>

                        <ProductDetailCode>
                            {t('Code')}: {product.catalogNumber}
                        </ProductDetailCode>
                    </div>
                </div>

                <ProductVariantsTable variants={product.variants} isSellingDenied={product.isSellingDenied} />

                <ProductDetailTabs description={product.description} parameters={product.parameters} />

                {!!product.accessories.length && <ProductDetailAccessories accessories={product.accessories} />}
            </Webline>
        </>
    );
};
