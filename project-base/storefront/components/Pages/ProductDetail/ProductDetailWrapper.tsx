import { DeferredLastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/DeferredLastVisitedProducts';
import { SkeletonPageProductDetail } from 'components/Blocks/Skeleton/SkeletonPageProductDetail';
import { SkeletonPageProductDetailMainVariant } from 'components/Blocks/Skeleton/SkeletonPageProductDetailMainVariant';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { useProductDetailQuery } from 'graphql/requests/products/queries/ProductDetailQuery.generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/factories/useGtmFriendlyPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { getSlugFromUrl } from 'utils/parsing/getSlugFromUrl';

const ProductDetailContent = dynamic(
    () => import('./ProductDetailContent').then((component) => component.ProductDetailContent),
    {
        loading: () => <SkeletonPageProductDetail />,
    },
);

const ProductDetailMainVariantContent = dynamic(
    () => import('./ProductDetailMainVariantContent').then((component) => component.ProductDetailMainVariantContent),
    {
        loading: () => <SkeletonPageProductDetailMainVariant />,
    },
);

export const ProductDetailWrapper: FC = () => {
    const router = useRouter();
    const [{ data: productData, fetching }] = useProductDetailQuery({
        variables: { urlSlug: getSlugFromUrl(router.asPath) },
    });

    const product =
        productData?.product?.__typename === 'RegularProduct' || productData?.product?.__typename === 'MainVariant'
            ? productData.product
            : null;

    const pageViewEvent = useGtmFriendlyPageViewEvent(product);
    useGtmPageViewEvent(pageViewEvent, fetching);

    return (
        <CommonLayout
            breadcrumbs={product?.breadcrumb}
            breadcrumbsType="category"
            canonicalQueryParams={[]}
            description={product?.seoMetaDescription}
            hreflangLinks={product?.hreflangLinks}
            isFetchingData={fetching}
            title={product?.seoTitle || product?.name}
        >
            {product?.__typename === 'RegularProduct' && <ProductDetailContent fetching={fetching} product={product} />}

            {product?.__typename === 'MainVariant' && (
                <ProductDetailMainVariantContent fetching={fetching} product={product} />
            )}

            <DeferredLastVisitedProducts currentProductCatnum={product?.catalogNumber} />
        </CommonLayout>
    );
};
