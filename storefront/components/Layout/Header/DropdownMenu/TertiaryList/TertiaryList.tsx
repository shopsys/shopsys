import { FC, Fragment } from 'react';
import DropdownItem from 'components/Layout/Header/DropdownMenu/Item';
import { DropdownListProps } from 'types/dropdown';
import { TertiaryListTitleStyled } from './TertiaryList.style';

const TertiaryList: FC<DropdownListProps> = (props) => {
    const testIdentifier = 'layout-header-dropdownmenu-tertiarylist-';

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
                                            <TertiaryListTitleStyled
                                                data-testid={testIdentifier + '-' + index + '-' + columnCategoryIndex}
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
