import { ProductsList } from './ProductsList';
import { CategoryDetailContentMessage } from 'app/_components/Page/CategoryDetail/CategoryDetailContentMessage';
import { getCategoryProductsQuery } from 'app/_queries/getCategoryProductsQuery';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { TypeProductFilter } from 'graphql/types';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';

type ProductsListWrapperProps = {
    categorySlug: string;
    orderingMode: TypeProductOrderingModeEnum;
    filter: TypeProductFilter | undefined;
};

export const ProductsListWrapper: FC<ProductsListWrapperProps> = async ({ categorySlug, orderingMode, filter }) => {
    const products = await getCategoryProductsQuery(categorySlug, '', orderingMode, filter, 10);

    if (!products) {
        return <CategoryDetailContentMessage />;
    }

    return (
        <ProductsList
            gtmMessageOrigin={GtmMessageOriginType.other}
            gtmProductListName={GtmProductListNameType.category_detail}
            products={products}
        />
    );
};
