import ProductItem from './ListedItem';
import { ProductsListStyled } from './ProductsList.style';
import { FC } from 'react';
import { ListedProductType } from 'types/product';
import { GtmListNameType } from 'types/gtm';

type ProductsListProps = {
    products: ListedProductType[];
    gtmListName: GtmListNameType;
};

const ProductsList: FC<ProductsListProps> = (props) => {
    const testIdentifier = 'blocks-product-list';

    return (
        <ProductsListStyled data-testid={testIdentifier}>
            {props.products.map((listedProductItem, index) => (
                <ProductItem key={index} product={listedProductItem} index={index} gtmListName={props.gtmListName} />
            ))}
        </ProductsListStyled>
    );
};

export default ProductsList;
