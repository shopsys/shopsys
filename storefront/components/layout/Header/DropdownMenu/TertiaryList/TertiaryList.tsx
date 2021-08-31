import { DropdownItemType, DropdownListType } from '../types';
import { FC, Fragment } from 'react';
import DropdownItem from '../Item';
import { TertiaryListTitleStyled } from './TertiaryList.style';

const TertiaryList: FC<DropdownListType & DropdownItemType> = (props) => {
    return (
        <>
            {props.navigationItems
                .map((navigationItem, index) => (
                    <Fragment key={index}>
                        {navigationItem.categoriesByColumns.map((columnCategories, columnIndex) => (
                            <Fragment key={columnIndex}>
                                {columnCategories.categories
                                    .map((columnCategory, columnCategoryIndex) => (
                                        <Fragment key={columnCategoryIndex}>
                                            <TertiaryListTitleStyled>{columnCategory.name}</TertiaryListTitleStyled>
                                            {columnCategory.children.map((columnCategoryChild, subListIndex) => (
                                                <DropdownItem
                                                    key={subListIndex}
                                                    columnCategoryChild={columnCategoryChild}
                                                    variant="small"
                                                />
                                            ))}
                                        </Fragment>
                                    ))
                                    .filter(
                                        (_, columnCategoryIndex) =>
                                            columnCategories.columnNumber + '-' + columnCategoryIndex ===
                                            props.historyOfIndexes[1],
                                    )}
                            </Fragment>
                        ))}
                    </Fragment>
                ))
                .filter((_, index) => index === props.historyOfIndexes[0])}
        </>
    );
};

/* @component */
export default TertiaryList;
