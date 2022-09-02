import {
    NavigationColumnCategoryImageStyled,
    NavigationColumnCategoryLinkStyled,
    NavigationColumnCategoryStyled,
} from './NavigationColumnCategory.style';
import Image from 'components/Basic/Image';
import NavigationSubList from 'components/Layout/Header/Navigation/NavigationSubList';
import NextLink from 'next/link';
import { FC } from 'react';
import { NavigationCategory } from 'types/navigation';

type NavigationColumnCategoryProps = {
    columnCategory: NavigationCategory;
};

const NavigationColumnCategory: FC<NavigationColumnCategoryProps> = (props) => {
    const testIdentifier = 'layout-header-navigation-navigationcolumncategory';

    return (
        <NavigationColumnCategoryStyled data-testid={testIdentifier}>
            <NextLink href={props.columnCategory.slug} passHref>
                <NavigationColumnCategoryImageStyled>
                    <Image image={props.columnCategory.image} type="default" alt={props.columnCategory.name} />
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

export default NavigationColumnCategory;
