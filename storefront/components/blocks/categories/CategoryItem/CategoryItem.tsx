import { CategoryItemType } from './types';
import { FC } from 'react';
import Link from 'next/link';
import ShopsysImage from '../../../basic/ShopsysImage';

type CategoryItemProps = {
    category: CategoryItemType;
};

const CategoryItem: FC<CategoryItemProps> = (props) => {
    return (
        <Link href={props.category.slug}>
            <div>
                <ShopsysImage image={props.category.image} alt={props.category.name} />
                <p>{props.category.name}</p>
            </div>
        </Link>
    );
};

export default CategoryItem;
