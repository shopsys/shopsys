import ProductItem from './ListedItem';
import { ProductsListStyled } from './ProductsList.style';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';
import { GtmListNameType } from 'types/gtm';
import { ListedProductType } from 'types/product';

type ProductsListProps = {
    products: ListedProductType[];
    gtmListName: GtmListNameType;
};

const ProductsList: FC<ProductsListProps> = (props) => {
    const testIdentifier = 'blocks-product-list';
    const { currentPage, pageSize } = useShopsysSelector((state) => state.user.pagination);

    return (
        <ProductsListStyled data-testid={testIdentifier}>
            {props.products.map((listedProductItem, index) => (
                <ProductItem
                    key={index}
                    product={listedProductItem}
                    listIndex={(currentPage - 1) * pageSize + index}
                    gtmListName={props.gtmListName}
                />
            ))}
        </ProductsListStyled>
    );
};

export default ProductsList;
