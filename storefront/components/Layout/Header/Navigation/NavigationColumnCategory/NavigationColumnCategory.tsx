import {
    NavigationColumnCategoryImageStyled,
    NavigationColumnCategoryLinkStyled,
    NavigationColumnCategoryStyled,
} from './NavigationColumnCategory.style';
import { Image } from 'components/Basic/Image/Image';
import { NavigationSubList } from 'components/Layout/Header/Navigation/NavigationSubList/NavigationSubList';
import NextLink from 'next/link';
import { FC } from 'react';
import { NavigationCategory } from 'types/navigation';

type NavigationColumnCategoryProps = {
    columnCategory: NavigationCategory;
};

const TEST_IDENTIFIER = 'layout-header-navigation-navigationcolumncategory';

export const NavigationColumnCategory: FC<NavigationColumnCategoryProps> = ({ columnCategory }) => (
    <NavigationColumnCategoryStyled data-testid={TEST_IDENTIFIER}>
        <NextLink href={columnCategory.slug} passHref>
            <NavigationColumnCategoryImageStyled>
                <Image image={columnCategory.image} type="default" alt={columnCategory.name} />
            </NavigationColumnCategoryImageStyled>
        </NextLink>
        <NextLink href={columnCategory.slug} passHref>
            <NavigationColumnCategoryLinkStyled>{columnCategory.name}</NavigationColumnCategoryLinkStyled>
        </NextLink>
        {columnCategory.children.length > 0 && <NavigationSubList columnCategoryChildren={columnCategory.children} />}
    </NavigationColumnCategoryStyled>
);
