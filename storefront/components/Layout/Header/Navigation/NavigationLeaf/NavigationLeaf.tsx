import { NavigationLeafColumnStyled } from './NavigationLeaf.style';
import NavigationColumnCategory from 'components/Layout/Header/Navigation/NavigationColumnCategory';
import { FC } from 'react';
import { NavigationCategoriesColumn } from 'types/navigation';

type NavigationLeafProps = {
    columnCategories: NavigationCategoriesColumn[];
};

const NavigationLeaf: FC<NavigationLeafProps> = (props) => {
    const testIdentifier = 'layout-header-navigation-navigationleaf-';

    return (
        <>
            {props.columnCategories.map((columnCategories, columnIndex) => (
                <NavigationLeafColumnStyled key={columnIndex} data-testid={testIdentifier + columnIndex}>
                    {columnCategories.categories.map((columnCategory, columnCategoryIndex) => (
                        <NavigationColumnCategory key={columnCategoryIndex} columnCategory={columnCategory} />
                    ))}
                </NavigationLeafColumnStyled>
            ))}
        </>
    );
};

/* @component */
export default NavigationLeaf;
