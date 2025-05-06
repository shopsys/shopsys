import { CategoryBestsellersListItem } from './CategoryBestsellersListItem';
import { CategoryBestselllersRemainingProductsCollapsible } from './CategoryBestselllersRemainingProductsCollapsible';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.ssr';

type CategoryBestselllersRemainingProductsProps = {
    remainingProducts: TypeListedProductFragment[];
};

export const CategoryBestselllersRemainingProducts: FC<CategoryBestselllersRemainingProductsProps> = ({
    remainingProducts,
}) => {
    if (remainingProducts.length === 0) {
        return null;
    }

    return (
        <CategoryBestselllersRemainingProductsCollapsible productsLength={remainingProducts.length}>
            {remainingProducts.map((product) => (
                <CategoryBestsellersListItem
                    key={product.uuid}
                    // gtmProductListName={GtmProductListNameType.bestsellers}
                    // listIndex={index}
                    product={product}
                />
            ))}
        </CategoryBestselllersRemainingProductsCollapsible>
    );
};
