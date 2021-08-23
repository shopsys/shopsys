import { CategoryDetailType } from './types';
import { FC } from 'react';
import ProductsList from '../../blocks/product/Listed/ProductsList';
import ShopsysHeading from '../../basic/ShopsysHeading';
import ShopsysLink from '../../basic/ShopsysLink';
import Webline from '../../layout/Webline';

type CategoryDetailProps = {
    category: CategoryDetailType;
};

const CategoryDetail: FC<CategoryDetailProps> = (props) => {
    return (
        <Webline>
            <ShopsysHeading type={'h1'}>
                {props.category.seoH1 !== null ? props.category.seoH1 : props.category.name}
            </ShopsysHeading>
            <ul>
                {props.category.children.map((child, key) => (
                    <li key={key}>
                        <ShopsysLink href={child.slug}>{child.name}</ShopsysLink> ({child.products.totalCount})
                    </li>
                ))}
            </ul>
            {props.category.products.edges.length !== 0 && <ProductsList products={props.category.products.edges} />}
        </Webline>
    );
};

export default CategoryDetail;
