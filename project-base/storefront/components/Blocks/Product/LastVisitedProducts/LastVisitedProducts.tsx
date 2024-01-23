import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { SkeletonModuleLastVisitedProducts } from 'components/Blocks/Skeleton/SkeletonModuleLastVisitedProducts';
import { Webline } from 'components/Layout/Webline/Webline';
import { useProductsByCatnumsApi } from 'graphql/generated';
import { GtmProductListNameType } from 'gtm/types/enums';
import useTranslation from 'next-translate/useTranslation';

type LastVisitedProductsProps = {
    currentProductCatalogNumber?: string;
    lastVisitedProductsFromCookies: string | undefined;
};

export const LastVisitedProducts: FC<LastVisitedProductsProps> = ({
    currentProductCatalogNumber,
    lastVisitedProductsFromCookies,
}) => {
    const parsedCookies = lastVisitedProductsFromCookies ? JSON.parse(lastVisitedProductsFromCookies) : [];

    const lastVisitedProductWithoutCurrentProduct = parsedCookies?.filter(
        (productCatalogNumber: string) => productCatalogNumber !== currentProductCatalogNumber,
    );

    const [{ data: result, fetching }] = useProductsByCatnumsApi({
        variables: { catnums: lastVisitedProductWithoutCurrentProduct || '' },
        pause: !lastVisitedProductWithoutCurrentProduct?.length,
    });

    const lastVisitedProducts = result?.productsByCatnums;
    const { t } = useTranslation();

    if (lastVisitedProductWithoutCurrentProduct?.length && fetching) {
        return <SkeletonModuleLastVisitedProducts />;
    }

    if (!lastVisitedProducts) {
        return null;
    }

    return (
        <Webline className="my-6">
            <h2>{t('Last visited products')}</h2>
            <ProductsSlider
                gtmProductListName={GtmProductListNameType.last_visited_products}
                products={lastVisitedProducts}
            />
        </Webline>
    );
};

LastVisitedProducts.displayName = 'LastVisitedProducts';
