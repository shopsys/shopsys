import { ProductsList } from 'app/_components/Blocks/Product/ProductsList/ProductsList';
import { CategoryDetailContentMessage } from 'app/_components/Page/CategoryDetail/CategoryDetailContentMessage';
import { getCategoryProductsQuery } from 'app/_queries/getCategoryProductsQuery';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.ssr';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { TypeProductFilter } from 'graphql/types';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { mapConnectionEdges } from 'utils/mappers/connection';

type CategoryDetailProductsServerProps = {
    categorySlug: string;
    orderingMode: TypeProductOrderingModeEnum;
    endCursor: string;
    filter: TypeProductFilter | undefined;
};

export const CategoryDetailProductsServer: FC<CategoryDetailProductsServerProps> = async ({
    categorySlug,
    orderingMode,
    endCursor,
    filter,
}) => {
    const productsData = await getCategoryProductsQuery(
        categorySlug,
        endCursor,
        orderingMode,
        filter,
        DEFAULT_PAGE_SIZE,
    );

    const mappedArticles = mapConnectionEdges<TypeListedProductFragment>(productsData?.products.edges);

    if (!mappedArticles?.length) {
        <CategoryDetailContentMessage />;
    }

    return (
        <ProductsList
            gtmMessageOrigin={GtmMessageOriginType.other}
            gtmProductListName={GtmProductListNameType.category_detail}
            products={mappedArticles}
        />
    );
};
