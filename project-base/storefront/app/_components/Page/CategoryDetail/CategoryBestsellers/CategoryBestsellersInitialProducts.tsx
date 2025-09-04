import { CategoryBestsellersListItem } from './CategoryBestsellersListItem';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.ssr';

type CategoryBestsellersInitialProductsProps = {
    initialProducts: TypeListedProductFragment[];
};

export const CategoryBestsellersInitialProducts: FC<CategoryBestsellersInitialProductsProps> = async ({
    initialProducts,
}) => {
    return (
        <div className="divide-border-less flex flex-col divide-y">
            {initialProducts.map((product) => (
                <CategoryBestsellersListItem
                    key={product.uuid}
                    // gtmProductListName={GtmProductListNameType.bestsellers}
                    // listIndex={index}
                    product={product}
                />
            ))}
        </div>
    );
};
