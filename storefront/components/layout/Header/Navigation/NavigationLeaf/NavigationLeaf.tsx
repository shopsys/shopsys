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
            {props.columnCategories.map((columnCategories, columnIndex) => (
                <NavigationLeafColumnStyled key={columnIndex}>
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
