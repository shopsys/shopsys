import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { SkeletonModuleLastVisitedProducts } from 'components/Blocks/Skeleton/SkeletonModuleLastVisitedProducts';
import { Webline } from 'components/Layout/Webline/Webline';
import { useProductsByCatnumsApi } from 'graphql/generated';
import { GtmProductListNameType } from 'gtm/types/enums';
import useTranslation from 'next-translate/useTranslation';

type LastVisitedProductsProps = {
    productsCatnums: string[];
};

export const LastVisitedProductsContent: FC<LastVisitedProductsProps> = ({ productsCatnums }) => {
    const { t } = useTranslation();
    const [{ data: result, fetching }] = useProductsByCatnumsApi({
        variables: { catnums: productsCatnums },
    });

    const lastVisitedProducts = result?.productsByCatnums;

    if (fetching) {
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
