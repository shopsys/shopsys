import {
    NavigationColumnCategoryImageStyled,
    NavigationColumnCategoryLinkStyled,
    NavigationColumnCategoryStyled,
} from './NavigationColumnCategory.style';
import { FC } from 'react';
import Link from 'next/link';
import { NavigationCategory } from '../../../../../connectors/navigation/Navigation';
import NavigationSubList from '../NavigationSubList';

type NavigationColumnCategoryProps = {
    columnCategory: NavigationCategory;
};

const NavigationColumnCategory: FC<NavigationColumnCategoryProps> = (props) => {
    return (
        <NavigationColumnCategoryStyled>
            <Link href={props.columnCategory.slug} passHref>
                <NavigationColumnCategoryImageStyled>
                    <img src={props.columnCategory.image.url} width={props.columnCategory.image.width} />
                </NavigationColumnCategoryImageStyled>
            </Link>
            <Link href={props.columnCategory.slug} passHref>
                <NavigationColumnCategoryLinkStyled>{props.columnCategory.name}</NavigationColumnCategoryLinkStyled>
            </Link>
            {props.columnCategory.children.length > 0 && (
                <NavigationSubList columnCategoryChildren={props.columnCategory.children} />
            )}
        </NavigationColumnCategoryStyled>
    );
};

/* @component */
export default NavigationColumnCategory;
