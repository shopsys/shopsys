import CategoryDetailAdvancedSeoCategories from './CategoryDetailAdvancedSeoCategories';
import CategoryDetailSubcategories from './CategoryDetailSubcategories';
import { CategoryDetailType } from './types';
import { FC } from 'react';
import Heading from 'components/Basic/Heading';
import Pagination from 'components/Blocks/Pagination/Pagination';
import ProductsList from 'components/Blocks/Product/List/ProductsList';
import SortingBar from 'components/Blocks/SortingBar';
import Webline from 'components/Layout/Webline';

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
            <Pagination />
        </Webline>
    );
};

export default CategoryDetail;
