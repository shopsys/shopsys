import { FC } from 'react';
import { NavigationCategoriesColumn } from '../../../../../connectors/navigation/Navigation';
import NavigationColumnCategory from '../NavigationColumnCategory';
import { NavigationLeafColumnStyled } from './NavigationLeaf.style';

type NavigationLeafProps = {
    columnCategories: NavigationCategoriesColumn[];
};

const NavigationLeaf: FC<NavigationLeafProps> = (props) => {
    return (
        <>
            {props.columnCategories.map((columnCategories, columnKey) => (
                <NavigationLeafColumnStyled key={columnKey}>
                    {columnCategories.categories.map((columnCategory, categoryKey) => (
                        <NavigationColumnCategory
                            key={categoryKey}
                            columnCategory={columnCategory}
                            categoryKey={categoryKey}
                        />
                    ))}
                </NavigationLeafColumnStyled>
            ))}
        </>
    );
};

/* @component */
export default NavigationLeaf;
