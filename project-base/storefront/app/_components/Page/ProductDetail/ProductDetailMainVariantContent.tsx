import { ProductDetailGallery } from 'app/_components/Page/ProductDetail/ProductDetailGallery';
import { ProductDetailInfo } from 'app/_components/Page/ProductDetail/ProductDetailInfo';
import { ProductDetailTabs } from 'app/_components/Page/ProductDetail/ProductDetailTabs/ProductDetailTabs';
import { ProductVariantsTable } from 'app/_components/Page/ProductDetail/ProductDetailVariantsTable';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.ssr';
import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.ssr';
import { useMemo } from 'react';

type ProductDetailMainVariantContentProps = {
    product: TypeMainVariantDetailFragment;
};

export const ProductDetailMainVariantContent: FC<ProductDetailMainVariantContentProps> = ({ product }) => {
    // const router = useRouter();

    const mainVariantImagesWithVariantImages = useMemo(() => {
        const variantImages = product.variants.reduce((mappedVariantImages, variant) => {
            if (variant.mainImage) {
                mappedVariantImages.push(variant.mainImage);
            }

            return mappedVariantImages;
        }, [] as TypeImageFragment[]);

        return [...product.images, ...variantImages];
    }, [product]);

    // const pageViewEvent = useGtmFriendlyPageViewEvent(product);
    // useGtmPageViewEvent(pageViewEvent, isProductDetailFetching);
    // useLastVisitedProductView(product.catalogNumber);
    // useGtmProductDetailViewEvent(product, getUrlWithoutGetParameters(router.asPath), isProductDetailFetching);

    return (
        <Webline className="flex flex-col gap-8">
            <ProductDetailGallery
                flags={product.flags}
                images={mainVariantImagesWithVariantImages}
                percentageDiscount={product.price.percentageDiscount}
                productName={product.name}
                videoIds={product.productVideos}
            />

            <ProductDetailInfo
                catalogNumber={product.catalogNumber}
                name={product.name}
                namePrefix={product.namePrefix}
                nameSuffix={product.nameSuffix}
            />

            <ProductVariantsTable variants={product.variants} />

            <ProductDetailTabs
                description={product.description}
                files={product.files}
                parameters={product.parameters}
                relatedProducts={product.relatedProducts}
            />
        </Webline>
    );
};
