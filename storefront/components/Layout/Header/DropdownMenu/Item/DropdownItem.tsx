import { DropdownItemLinkStyled, DropdownItemStyled } from './DropdownItem.style';
import {
    NavigationCategory as NavigationCategoryType,
    NavigationItem as NavigationItemType,
    NavigationSubCategory as NavigationSubCategoryType,
} from '../../../../../connectors/navigation/Navigation';
import { DropdownItemType } from '../types';
import DropdownSlideTo from '../SlideTo';
import { FC } from 'react';
import Link from 'next/link';

type DropdownItemProps = {
    variant?: 'small';
    level: 'primary' | 'secondary' | 'tertiary';
    navigationItem?: NavigationItemType;
    columnCategory?: NavigationCategoryType;
    columnCategoryChild?: NavigationSubCategoryType;
};

const DropdownItem: FC<DropdownItemProps & DropdownItemType> = (props) => {
    let hasChildren, itemName;
    let itemLink = '';

    if (props.navigationItem !== undefined) {
        hasChildren = props.navigationItem.categoriesByColumns.length > 0;
        itemLink = props.navigationItem.link;
        itemName = props.navigationItem.name;
    }

    if (props.columnCategory !== undefined) {
        hasChildren = props.columnCategory.children.length > 0;
        itemLink = props.columnCategory.slug;
        itemName = props.columnCategory.name;
    }

    if (props.columnCategoryChild !== undefined) {
        hasChildren = false;
        itemLink = props.columnCategoryChild.slug;
        itemName = props.columnCategoryChild.name;
    }

    return (
        <DropdownItemStyled variant={props.variant}>
            <Link href={itemLink} passHref>
                <DropdownItemLinkStyled variant={props.variant}>{itemName}</DropdownItemLinkStyled>
            </Link>
            {hasChildren && (
                <DropdownSlideTo
                    changeState={props.changeState}
                    goToMenu={props.goToMenu}
                    slideTo={props.slideTo}
                    index={props.index}
                />
            )}
        </DropdownItemStyled>
    );
};

/* @component */
export default DropdownItem;
