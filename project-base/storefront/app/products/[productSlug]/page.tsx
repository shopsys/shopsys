import { ProductMetadataJsonLd } from 'app/_components/Basic/Head/ProductMetadataJsonLd';
import { LastVisitedProducts } from 'app/_components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { RecommendedProducts } from 'app/_components/Blocks/Product/RecommendedProducts/RecommendedProducts';
import { Breadcrumbs } from 'app/_components/Layout/Breadcrumbs/Breadcrumbs';
import { Container } from 'app/_components/Layout/Container/Container';
import { ProductDetailAccessories } from 'app/_components/Page/ProductDetail/ProductDetailAccessories';
import { ProductDetailContent } from 'app/_components/Page/ProductDetail/ProductDetailContent';
import { ProductDetailMainVariantContent } from 'app/_components/Page/ProductDetail/ProductDetailMainVariantContent';
import { getProductQuery } from 'app/_queries/getProductQuery';
import { TypeRecommendationType } from 'graphql/types';
import { Metadata } from 'next';
import { notFound } from 'next/navigation';

export const generateMetadata = async ({ params }: { params: { productSlug: string } }): Promise<Metadata> => {
    const { product } = await getProductQuery(params.productSlug);

    if (!product) {
        notFound();
    }

    return {
        title: product.seoTitle || product.name,
        description: product.description,
        openGraph: {
            title: product.fullName,
            description: product.description ?? '',
            images: product.images.length ? product.images[0].url : undefined,
        },
    };
};

const ProductPage = async ({ params }: { params: { productSlug: string } }) => {
    const { product } = await getProductQuery(params.productSlug);

    if (!product) {
        notFound();
    }

    return (
        <>
            <ProductMetadataJsonLd product={product} />

            <Breadcrumbs breadcrumbs={product.breadcrumb} />

            <Container>
                {product.__typename === 'RegularProduct' && <ProductDetailContent product={product} />}

                {product.__typename === 'MainVariant' && <ProductDetailMainVariantContent product={product} />}

                <ProductDetailAccessories accessories={product.accessories} />

                <RecommendedProducts
                    itemUuids={[product.uuid]}
                    recommendationType={TypeRecommendationType.ItemDetail}
                />

                <LastVisitedProducts currentProductCatnum={product.catalogNumber} />
            </Container>
        </>
    );
};

export default ProductPage;
