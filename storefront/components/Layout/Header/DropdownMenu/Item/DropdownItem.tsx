import { DropdownItemLinkStyled, DropdownItemStyled } from './DropdownItem.style';
import { FC, useEffect, useState } from 'react';
import {
    NavigationCategory as NavigationCategoryType,
    NavigationItem as NavigationItemType,
    NavigationSubCategory as NavigationSubCategoryType,
} from '../../../../../connectors/navigation/Navigation';
import { DropdownItemType } from '../types';
import DropdownSlideTo from '../SlideTo';
import Link from 'next/link';

type DropdownItemProps = {
    variant?: 'small';
    navigationItem?: NavigationItemType;
    columnCategory?: NavigationCategoryType;
    columnCategoryChild?: NavigationSubCategoryType;
};

const DropdownItem: FC<DropdownItemProps & DropdownItemType> = (props) => {
    const [hasChildren, setHasChildren] = useState(false);
    const [itemLink, setItemLink] = useState('');
    const [itemName, setItemName] = useState('');

    useEffect(() => {
        if (props.navigationItem !== undefined) {
            setHasChildren(props.navigationItem.categoriesByColumns.length > 0);
            setItemLink(props.navigationItem.link);
            setItemName(props.navigationItem.name);
        } else if (props.columnCategory !== undefined) {
            setHasChildren(props.columnCategory.children.length > 0);
            setItemLink(props.columnCategory.slug);
            setItemName(props.columnCategory.name);
        } else if (props.columnCategoryChild !== undefined) {
            setItemLink(props.columnCategoryChild.slug);
            setItemName(props.columnCategoryChild.name);
        }
    }, [hasChildren, itemLink, itemName]);

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
