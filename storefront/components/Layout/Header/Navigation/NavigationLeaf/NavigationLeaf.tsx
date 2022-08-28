import { NavigationLeafColumnStyled } from './NavigationLeaf.style';
import { NavigationColumnCategory } from 'components/Layout/Header/Navigation/NavigationColumnCategory/NavigationColumnCategory';
import { FC } from 'react';
import { NavigationCategoriesColumn } from 'types/navigation';

type NavigationLeafProps = {
    columnCategories: NavigationCategoriesColumn[];
};

const TEST_IDENTIFIER = 'layout-header-navigation-navigationleaf-';

export const NavigationLeaf: FC<NavigationLeafProps> = ({ columnCategories }) => (
    <>
        {columnCategories.map((columnCategories, columnIndex) => (
            <NavigationLeafColumnStyled key={columnIndex} data-testid={TEST_IDENTIFIER + columnIndex}>
                {columnCategories.categories.map((columnCategory, columnCategoryIndex) => (
                    <NavigationColumnCategory key={columnCategoryIndex} columnCategory={columnCategory} />
                ))}
            </NavigationLeafColumnStyled>
        ))}
    </>
);
