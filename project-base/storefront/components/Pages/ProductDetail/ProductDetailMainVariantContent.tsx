import { ProductDetailCode, ProductDetailHeading, ProductDetailPrefix } from './ProductDetaiElements';
import { ProductDetailAccessories } from './ProductDetailAccessories';
import { ProductDetailGallery } from './ProductDetailGallery';
import { ProductDetailTabs } from './ProductDetailTabs';
import { ProductVariantsTable } from './ProductDetailVariantsTable';
import { ProductMetadata } from 'components/Basic/Head/ProductMetadata';
import { Webline } from 'components/Layout/Webline/Webline';
import { ImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { MainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { useGtmProductDetailViewEvent } from 'gtm/hooks/useGtmProductDetailViewEvent';
import { getUrlWithoutGetParameters } from 'helpers/parsing/urlParsing';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useMemo } from 'react';

type ProductDetailMainVariantContentProps = {
    product: MainVariantDetailFragment;
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
        }, [] as ImageFragment[]);

        return [...product.images, ...variantImages];
    }, [product]);

    useGtmProductDetailViewEvent(product, getUrlWithoutGetParameters(router.asPath), fetching);

    return (
        <>
            <ProductMetadata product={product} />

            <Webline className="flex flex-col gap-8">
                <ProductDetailGallery
                    flags={product.flags}
                    images={mainVariantImagesWithVariantImages}
                    productName={product.name}
                    videoIds={product.productVideos}
                />

                <div className="gap-2">
                    <ProductDetailPrefix>{product.namePrefix}</ProductDetailPrefix>

                    <ProductDetailHeading>
                        {product.name} {product.nameSuffix}
                    </ProductDetailHeading>

                    <ProductDetailCode>
                        {t('Code')}: {product.catalogNumber}
                    </ProductDetailCode>
                </div>

                <ProductVariantsTable isSellingDenied={product.isSellingDenied} variants={product.variants} />

                <ProductDetailTabs
                    description={product.description}
                    parameters={product.parameters}
                    relatedProducts={product.relatedProducts}
                />

                {!!product.accessories.length && <ProductDetailAccessories accessories={product.accessories} />}
            </Webline>
        </>
    );
};
