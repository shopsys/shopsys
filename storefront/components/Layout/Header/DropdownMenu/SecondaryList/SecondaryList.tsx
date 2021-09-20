import { FC, Fragment } from 'react';
import DropdownItem from 'components/Layout/Header/DropdownMenu/Item';
import { DropdownListType } from 'components/Layout/Header/DropdownMenu/types';
import { SecondaryListTitleStyled } from './SecondaryList.style';

const SecondaryList: FC<DropdownListType> = (props) => {
    return (
        <>
            {props.navigationItems
                .map((navigationItem, index) => (
                    <Fragment key={index}>
                        <SecondaryListTitleStyled>{navigationItem.name}</SecondaryListTitleStyled>
                        {navigationItem.categoriesByColumns.map((columnCategories, columnIndex) => (
                            <Fragment key={columnIndex}>
                                {columnCategories.categories.map((columnCategory, columnCategoryIndex) => (
                                    <DropdownItem
                                        key={columnCategoryIndex}
                                        columnCategory={columnCategory}
                                        goToMenu="tertiary"
                                        index={columnCategories.columnNumber + '-' + columnCategoryIndex}
                                        variant="small"
                                    />
                                ))}
                            </Fragment>
                        ))}
                    </Fragment>
                ))
                .filter((_, index) => index === props.historyOfIndexes[0])}
        </>
    );
};

/* @component */
export default SecondaryList;
