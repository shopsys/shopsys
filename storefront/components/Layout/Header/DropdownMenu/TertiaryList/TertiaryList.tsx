import { TertiaryListTitleStyled } from './TertiaryList.style';
import DropdownItem from 'components/Layout/Header/DropdownMenu/Item';
import { FC, Fragment } from 'react';
import { DropdownListProps } from 'types/dropdown';

const TEST_IDENTIFIER = 'layout-header-dropdownmenu-tertiarylist-';

const TertiaryList: FC<DropdownListProps> = ({ navigationItems, historyOfIndexes }) => {
    return (
        <>
            {navigationItems
                .map((navigationItem, index) => (
                    <Fragment key={index}>
                        {navigationItem.categoriesByColumns.map((columnCategories, columnIndex) => (
                            <Fragment key={columnIndex}>
                                {columnCategories.categories
                                    .map((columnCategory, columnCategoryIndex) => (
                                        <Fragment key={columnCategoryIndex}>
                                            <TertiaryListTitleStyled
                                                data-testid={TEST_IDENTIFIER + '-' + index + '-' + columnCategoryIndex}
                                            >
                                                {columnCategory.name}
                                            </TertiaryListTitleStyled>
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
                                            historyOfIndexes[1],
                                    )}
                            </Fragment>
                        ))}
                    </Fragment>
                ))
                .filter((_, index) => index === historyOfIndexes[0])}
        </>
    );
};

export default TertiaryList;
