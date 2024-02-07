import { DropdownItem } from './DropdownItem';
import { Fragment } from 'react';
import { DropdownListProps } from 'types/dropdown';

export const SecondaryList: FC<DropdownListProps> = ({ navigationItems, historyOfIndexes }) => (
    <>
        {navigationItems
            .map((navigationItem, index) => (
                <Fragment key={index}>
                    <div className="border-b border-greyLighter px-8 pb-4 font-bold uppercase">
                        {navigationItem.name}
                    </div>
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
