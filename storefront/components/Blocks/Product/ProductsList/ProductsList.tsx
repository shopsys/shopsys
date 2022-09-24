import { ProductItem } from './ProductItem/ProductItem';
import { ProductsListStyled } from './ProductsList.style';
import { LoadingOverlay } from 'components/Basic/LoadingOverlay/LoadingOverlay';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';
import { GtmListNameType } from 'types/gtm';
import { ListedProductType } from 'types/product';

type ProductsListProps = {
    products: ListedProductType[];
    gtmListName: GtmListNameType;
    fetching?: boolean;
};

const TEST_IDENTIFIER = 'blocks-product-list';

export const ProductsList: FC<ProductsListProps> = ({ gtmListName, products, fetching }) => {
    const { currentPage, pageSize } = useShopsysSelector((state) => state.user.pagination);

    return (
        <ProductsListStyled data-testid={TEST_IDENTIFIER}>
            {products.map((listedProductItem, index) => (
                <ProductItem
                    key={listedProductItem.uuid}
                    product={listedProductItem}
                    listIndex={(currentPage - 1) * pageSize + index}
                    gtmListName={gtmListName}
                />
            ))}
            {fetching && <LoadingOverlay iconSize={80} />}
        </ProductsListStyled>
    );
};
