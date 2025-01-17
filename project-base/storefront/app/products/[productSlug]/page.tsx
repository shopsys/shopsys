import { getProductQuery } from 'app/_queries/getProductQuery';
import { LastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { ProductDetailAccessories } from 'components/Pages/ProductDetail/ProductDetailAccessories/ProductDetailAccessories';
import { ProductDetailContent } from 'components/Pages/ProductDetail/ProductDetailContent';
import { notFound } from 'next/navigation';

export default async function ProductPage() {
    const { data, error } = await getProductQuery();

    const product =
        data?.product?.__typename === 'RegularProduct' || data?.product?.__typename === 'MainVariant'
            ? data.product
            : null;

    if (error || !product) {
        notFound();
    }

    const firstImageUrl = product.images[0].url;

    return (
        <>
            {product.__typename === 'RegularProduct' && <ProductDetailContent product={product} />}

            {/* how about separete query for accessories similar to last visited products❓ */}
            <ProductDetailAccessories accessories={product.accessories} />

            <LastVisitedProducts currentProductCatnum={product.catalogNumber} />
        </>
    );
}
