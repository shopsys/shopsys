import CategoryDetailAdvancedSeoCategories from './CategoryDetailAdvancedSeoCategories';
import CategoryDetailSubcategories from './CategoryDetailSubcategories';
import { CategoryDetailType } from './types';
import { FC } from 'react';
import ProductsList from '../../Blocks/Product/List/ProductsList';
import ShopsysHeading from '../../Basic/ShopsysHeading';
import Webline from '../../Layout/Webline';

type CategoryDetailProps = {
    category: CategoryDetailType;
};

const CategoryDetail: FC<CategoryDetailProps> = (props) => {
    return (
        <Webline>
            <ShopsysHeading type={'h1'}>
                {props.category.seoH1 !== null ? props.category.seoH1 : props.category.name}
            </ShopsysHeading>
            <CategoryDetailSubcategories
                categories={[...props.category.children, ...props.category.linkedCategories]}
            />
            <CategoryDetailAdvancedSeoCategories readyCategorySeoMixLinks={props.category.readyCategorySeoMixLinks} />
            {props.category.products.edges.length !== 0 && <ProductsList products={props.category.products.edges} />}
        </Webline>
    );
};

export default CategoryDetail;
