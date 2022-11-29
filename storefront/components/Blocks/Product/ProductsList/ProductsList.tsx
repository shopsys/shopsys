import { ProductItem } from './ProductItem/ProductItem';
import { ProductsListStyled } from './ProductsList.style';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { usePaginationContext } from 'components/Blocks/Pagination/usePaginationContext';
import { FC } from 'react';
import { GtmListNameType } from 'types/gtm';
import { ListedProductType } from 'types/product';

type ProductsListProps = {
    products: ListedProductType[];
    gtmListName: GtmListNameType;
    fetching?: boolean;
};

const TEST_IDENTIFIER = 'blocks-product-list';

export const ProductsList: FC<ProductsListProps> = ({ gtmListName, products, fetching }) => {
    const [{ page }] = usePaginationContext();

    return (
        <ProductsListStyled data-testid={TEST_IDENTIFIER}>
            {products.map((listedProductItem, index) => (
                <ProductItem
                    key={listedProductItem.uuid}
                    product={listedProductItem}
                    listIndex={(page - 1) * DEFAULT_PAGE_SIZE + index}
                    gtmListName={gtmListName}
                />
            ))}
            {fetching && <LoaderWithOverlay iconSize={80} />}
        </ProductsListStyled>
    );
};
