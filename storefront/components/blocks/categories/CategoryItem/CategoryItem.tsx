import { CategoryItemBlockStyled, CategoryItemImageStyled, CategoryItemNameStyled } from './CategoryItem.style';
import { CategoryItemType } from './types';
import { FC } from 'react';
import Link from 'next/link';
import ShopsysImage from '../../../basic/ShopsysImage';

type CategoryItemProps = {
    category: CategoryItemType;
};

const CategoryItem: FC<CategoryItemProps> = (props) => {
    return (
        <Link href={props.category.slug} passHref>
            <CategoryItemBlockStyled>
                <CategoryItemImageStyled>
                    <ShopsysImage image={props.category.image} alt={props.category.name} />
                </CategoryItemImageStyled>
                <CategoryItemNameStyled>{props.category.name}</CategoryItemNameStyled>
            </CategoryItemBlockStyled>
        </Link>
    );
};

export default CategoryItem;
