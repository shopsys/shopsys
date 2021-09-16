import CategoryDetailAdvancedSeoCategories from './CategoryDetailAdvancedSeoCategories';
import CategoryDetailSubcategories from './CategoryDetailSubcategories';
import { CategoryDetailType } from './types';
import { FC } from 'react';
import Heading from '../../Basic/Heading';
import ProductsList from '../../Blocks/Product/List/ProductsList';
import SortingBar from 'components/Blocks/SortingBar';
import Webline from '../../Layout/Webline';

type CategoryDetailProps = {
    category: CategoryDetailType;
};

const CategoryDetail: FC<CategoryDetailProps> = (props) => {
    return (
        <Webline>
            <Heading type={'h1'}>{props.category.seoH1 !== null ? props.category.seoH1 : props.category.name}</Heading>
            <CategoryDetailSubcategories
                categories={[...props.category.children, ...props.category.linkedCategories]}
            />
            <CategoryDetailAdvancedSeoCategories readyCategorySeoMixLinks={props.category.readyCategorySeoMixLinks} />
            <SortingBar />
            {props.category.products.edges.length !== 0 && <ProductsList products={props.category.products.edges} />}
        </Webline>
    );
};

export default CategoryDetail;
