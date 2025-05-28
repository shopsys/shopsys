import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { TypeBrandDetailFragment } from 'graphql/requests/brands/fragments/BrandDetailFragment.generated';
import { BrandProductsQueryDocument } from 'graphql/requests/products/queries/BrandProductsQuery.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useGtmPaginatedProductListViewEvent } from 'gtm/utils/pageViewEvents/productList/useGtmPaginatedProductListViewEvent';
import { useProductsData } from 'utils/loadMore/useProductsData';
import { getMappedProducts } from 'utils/mappers/products';

type BrandDetailProductsWrapperProps = {
    brand: TypeBrandDetailFragment;
};

export const BrandDetailProductsWrapper: FC<BrandDetailProductsWrapperProps> = ({ brand }) => {
    const {
        products: brandProductsData,
        areProductsFetching,
        isLoadingMoreProducts,
        hasNextPage,
    } = useProductsData(BrandProductsQueryDocument, brand.products.totalCount);
    const listedBrandProducts = getMappedProducts(brandProductsData);

    useGtmPaginatedProductListViewEvent(listedBrandProducts, GtmProductListNameType.brand_detail);

    return (
        <>
            <ProductsList
                areProductsFetching={areProductsFetching}
                gtmMessageOrigin={GtmMessageOriginType.other}
                gtmProductListName={GtmProductListNameType.brand_detail}
                isLoadingMoreProducts={isLoadingMoreProducts}
                products={listedBrandProducts}
            />
            <Pagination isWithLoadMore hasNextPage={hasNextPage} totalCount={brand.products.totalCount} />
        </>
    );
};
