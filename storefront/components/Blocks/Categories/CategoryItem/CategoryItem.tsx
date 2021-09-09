import {
    CategoryItemBlockStyled,
    CategoryItemCountStyled,
    CategoryItemImageStyled,
    CategoryItemNameStyled,
    CategoryItemNameWrapperStyled,
} from './CategoryItem.style';
import { CategoryItemType } from './types';
import { FC } from 'react';
import Image from '../../../Basic/Image';
import NextLink from 'next/link';

type CategoryItemProps = {
    category: CategoryItemType;
};

const CategoryItem: FC<CategoryItemProps> = (props) => {
    return (
        <NextLink href={props.category.slug} passHref>
            <CategoryItemBlockStyled>
                <CategoryItemImageStyled>
                    <Image image={props.category.image} alt={props.category.name} />
                </CategoryItemImageStyled>
                <CategoryItemNameWrapperStyled>
                    <CategoryItemNameStyled>{props.category.name}</CategoryItemNameStyled>
                    {props.category.products !== undefined && (
                        <CategoryItemCountStyled>({props.category.products.totalCount})</CategoryItemCountStyled>
                    )}
                </CategoryItemNameWrapperStyled>
            </CategoryItemBlockStyled>
        </NextLink>
    );
};

export default CategoryItem;
