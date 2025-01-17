import { Breadcrumbs } from 'app/_components/Layout/Breadcrumbs/Breadcrumbs';
import { getProductQuery } from 'app/_queries/getProductQuery';
import { ProductMetadataJsonLd } from 'components/Basic/Head/ProductMetadataJsonLd';
import { LastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { ProductDetailAccessories } from 'components/Pages/ProductDetail/ProductDetailAccessories/ProductDetailAccessories';
import { ProductDetailContent } from 'components/Pages/ProductDetail/ProductDetailContent';
import { Metadata } from 'next';
import { notFound } from 'next/navigation';

export const generateMetadata = async (): Promise<Metadata> => {
    const { product } = await getProductQuery();

    if (!product) {
        notFound();
    }

    return {
        title: product.seoTitle || product.name,
        description: product.description,
        openGraph: {
            title: product.fullName,
            description: product.description ?? '',
            images: product.images[0].url,
        },
    };
};

const ProductPage = async () => {
    const { product } = await getProductQuery();

    if (!product) {
        notFound();
    }

    return (
        <>
            <Breadcrumbs breadcrumbs={product.breadcrumb} />

            <ProductMetadataJsonLd product={product} />

            {product.__typename === 'RegularProduct' && <ProductDetailContent product={product} />}

            <ProductDetailAccessories accessories={product.accessories} />

            <LastVisitedProducts currentProductCatnum={product.catalogNumber} />
        </>
    );
};

export default ProductPage;
