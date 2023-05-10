import { DropdownItem } from './DropdownItem';
import { Fragment } from 'react';
import { DropdownListProps } from 'types/dropdown';

const TEST_IDENTIFIER = 'layout-header-dropdownmenu-tertiarylist-';

export const TertiaryList: FC<DropdownListProps> = ({ navigationItems, historyOfIndexes }) => (
    <>
        {navigationItems
            .map((navigationItem, index) => (
                <Fragment key={index}>
                    {navigationItem.categoriesByColumns.map((columnCategories, columnIndex) => (
                        <Fragment key={columnIndex}>
                            {columnCategories.categories
                                .map((columnCategory, columnCategoryIndex) => (
                                    <Fragment key={columnCategoryIndex}>
                                        <div
                                            className="border-b border-greyLighter px-8 pb-4 font-bold uppercase"
                                            data-testid={TEST_IDENTIFIER + '-' + index + '-' + columnCategoryIndex}
                                        >
                                            {columnCategory.name}
                                        </div>
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
