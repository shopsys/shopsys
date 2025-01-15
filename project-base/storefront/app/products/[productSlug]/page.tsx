import { createQuery } from 'app/_urql/urql-dto';
import { LastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { ProductDetailContent } from 'components/Pages/ProductDetail/ProductDetailContent';
import { ProductDetailQueryDocument } from 'graphql/requests/products/queries/ProductDetailQuery.ssr';
import { headers } from 'next/headers';
import { notFound } from 'next/navigation';

async function getProductQuery() {
    const headersList = headers();
    const slug = headersList.get('x-friendly-slug');

    console.log('🐳 slug', slug);

    return createQuery(ProductDetailQueryDocument, {
        urlSlug: slug,
    });
}

export default async function ProductPage() {
    const { data, error } = await getProductQuery();

    console.log('🧪 data', data);

    if (error || !data?.product) {
        notFound();
    }

    const product =
        data?.product?.__typename === 'RegularProduct' || data?.product?.__typename === 'MainVariant'
            ? data.product
            : null;

    const firstImageUrl = product?.images[0]?.url;

    return (
        <>
            {product?.__typename === 'RegularProduct' && <ProductDetailContent product={product} />}

            <LastVisitedProducts currentProductCatnum={product.catalogNumber} />
        </>
    );
}
