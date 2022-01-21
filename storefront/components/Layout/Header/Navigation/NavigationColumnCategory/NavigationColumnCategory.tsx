import {
    NavigationColumnCategoryImageStyled,
    NavigationColumnCategoryLinkStyled,
    NavigationColumnCategoryStyled,
} from './NavigationColumnCategory.style';
import { FC } from 'react';
import { NavigationCategory } from 'types/navigation';
import NavigationSubList from 'components/Layout/Header/Navigation/NavigationSubList';
import NextLink from 'next/link';

type NavigationColumnCategoryProps = {
    columnCategory: NavigationCategory;
};

const NavigationColumnCategory: FC<NavigationColumnCategoryProps> = (props) => {
    const testIdentifier = 'layout-header-navigation-navigationcolumncategory';

    return (
        <NavigationColumnCategoryStyled data-testid={testIdentifier}>
            <NextLink href={props.columnCategory.slug} passHref>
                <NavigationColumnCategoryImageStyled>
                    <img src={props.columnCategory.image.url} width={props.columnCategory.image.width} />
                </NavigationColumnCategoryImageStyled>
            </NextLink>
            <NextLink href={props.columnCategory.slug} passHref>
                <NavigationColumnCategoryLinkStyled>{props.columnCategory.name}</NavigationColumnCategoryLinkStyled>
            </NextLink>
            {props.columnCategory.children.length > 0 && (
                <NavigationSubList columnCategoryChildren={props.columnCategory.children} />
            )}
        </NavigationColumnCategoryStyled>
    );
};

/* @component */
export default NavigationColumnCategory;
