import { SecondaryListTitleStyled } from './SecondaryList.style';
import { DropdownItem } from 'components/Layout/Header/DropdownMenu/Item/DropdownItem';
import { FC, Fragment } from 'react';
import { DropdownListProps } from 'types/dropdown';

const TEST_IDENTIFIER = 'layout-header-dropdownmenu-secondarylist-';

export const SecondaryList: FC<DropdownListProps> = ({ navigationItems, historyOfIndexes }) => (
    <>
        {navigationItems
            .map((navigationItem, index) => (
                <Fragment key={index}>
                    <SecondaryListTitleStyled data-testid={TEST_IDENTIFIER + index}>
                        {navigationItem.name}
                    </SecondaryListTitleStyled>
                    {navigationItem.categoriesByColumns.map((columnCategories, columnIndex) => (
                        <Fragment key={`${index}-${columnIndex}`}>
                            {columnCategories.categories.map((columnCategory, columnCategoryIndex) => (
                                <DropdownItem
                                    key={`${index}-${columnIndex}-${columnCategoryIndex}`}
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
            .filter((_, index) => index === historyOfIndexes[0])}
    </>
);
