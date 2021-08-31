import { DropdownItemType, DropdownListType } from '../types';
import { FC, Fragment } from 'react';
import DropdownItem from '../Item';
import { SecondaryListTitleStyled } from './SecondaryList.style';

const SecondaryList: FC<DropdownListType & DropdownItemType> = (props) => {
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
                                        changeState={props.changeState}
                                        goToMenu="tertiary"
                                        index={columnCategories.columnNumber + '-' + columnCategoryIndex}
                                        slideTo="right"
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
