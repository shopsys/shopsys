import { DeferredProductDetailAccessories } from './ProductDetailAccessories/DeferredProductDetailAccessories';
import { ProductDetailGallery } from './ProductDetailGallery';
import { ProductDetailInfo } from './ProductDetailInfo';
import { ProductDetailSections } from './ProductDetailSections/ProductDetailSections';
import { ProductVariantsTable } from './ProductDetailVariantsTable';
import { ProductMetadata } from 'components/Basic/Head/ProductMetadata';
import { DeferredLastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/DeferredLastVisitedProducts';
import { useLastVisitedProductView } from 'components/Blocks/Product/LastVisitedProducts/lastVisitedProductsUtils';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/factories/useGtmFriendlyPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import { useGtmProductDetailViewEvent } from 'gtm/utils/pageViewEvents/useGtmProductDetailViewEvent';
import { useRouter } from 'next/router';
import { getUrlWithoutGetParameters } from 'utils/parsing/getUrlWithoutGetParameters';

type ProductDetailMainVariantContentProps = {
    product: TypeMainVariantDetailFragment;
    isProductDetailFetching: boolean;
};

export const ProductDetailMainVariantContent: FC<ProductDetailMainVariantContentProps> = ({
    product,
    isProductDetailFetching,
}) => {
    const router = useRouter();

    const variantImages = product.variants.reduce((mappedVariantImages, variant) => {
        if (variant.mainImage) {
            mappedVariantImages.push(variant.mainImage);
        }

        return mappedVariantImages;
    }, [] as TypeImageFragment[]);

    const mainVariantImagesWithVariantImages = [...product.images, ...variantImages];

    const pageViewEvent = useGtmFriendlyPageViewEvent(product);
    useGtmPageViewEvent(pageViewEvent, isProductDetailFetching);
    useLastVisitedProductView(product.catalogNumber);
    useGtmProductDetailViewEvent(product, getUrlWithoutGetParameters(router.asPath), isProductDetailFetching);

    return (
        <>
            <ProductMetadata product={product} />

            <VerticalStack gap="md">
                <Webline>
                    <ProductDetailGallery
                        categoryName={product.categories[0]?.name}
                        flags={product.flags}
                        images={mainVariantImagesWithVariantImages}
                        percentageDiscount={product.price.percentageDiscount}
                        productName={product.name}
                        videoIds={product.productVideos}
                    />
                </Webline>

                <Webline>
                    <ProductDetailInfo
                        catalogNumber={product.catalogNumber}
                        name={product.name}
                        namePrefix={product.namePrefix}
                        nameSuffix={product.nameSuffix}
                    />
                </Webline>

                <ProductVariantsTable variants={product.variants} />

                <ProductDetailSections
                    description={product.description}
                    files={product.files}
                    parameters={product.parameters}
                    relatedProducts={product.relatedProducts}
                />

                <DeferredProductDetailAccessories accessories={product.accessories} />

                <DeferredLastVisitedProducts currentProductCatnum={product.catalogNumber} />
            </VerticalStack>
        </>
    );
};
