import { DropdownItemLinkStyled, DropdownItemStyled } from './DropdownItem.style';
import { DropdownMenuContext } from 'components/Layout/Header/DropdownMenu/DropdownMenuContext';
import DropdownSlideRight from 'components/Layout/Header/DropdownMenu/SlideRight';
import Link from 'next/link';
import { FC, useContext, useEffect, useState } from 'react';
import * as smoothscroll from 'smoothscroll-polyfill';
import { DropdownItemType } from 'types/dropdown';
import {
    NavigationCategory as NavigationCategoryType,
    NavigationItem as NavigationItemType,
    NavigationSubCategory as NavigationSubCategoryType,
} from 'types/navigation';

type DropdownItemProps = DropdownItemType & {
    variant?: 'small';
    navigationItem?: NavigationItemType;
    columnCategory?: NavigationCategoryType;
    columnCategoryChild?: NavigationSubCategoryType;
};

const TEST_IDENTIFIER = 'layout-header-dropdownmenu-item';

const DropdownItem: FC<DropdownItemProps> = ({
    navigationItem,
    variant,
    columnCategory,
    columnCategoryChild,
    index,
    goToMenu,
}) => {
    const context = useContext(DropdownMenuContext);
    const [hasChildren, setHasChildren] = useState(false);
    const [itemLink, setItemLink] = useState('');
    const [itemName, setItemName] = useState('');

    const scrollToTop = () => {
        window.scroll({ top: 0, left: 0, behavior: 'smooth' });
    };

    useEffect(() => {
        smoothscroll.polyfill();
    }, []);

    useEffect(() => {
        if (navigationItem !== undefined) {
            setHasChildren(navigationItem.categoriesByColumns.length > 0);
            setItemLink(navigationItem.link);
            setItemName(navigationItem.name);
        } else if (columnCategory !== undefined) {
            setHasChildren(columnCategory.children.length > 0);
            setItemLink(columnCategory.slug);
            setItemName(columnCategory.name);
        } else if (columnCategoryChild !== undefined) {
            setItemLink(columnCategoryChild.slug);
            setItemName(columnCategoryChild.name);
        }
    }, [hasChildren, itemLink, itemName, columnCategory, columnCategoryChild, navigationItem]);

    return (
        <DropdownItemStyled variant={variant} onClick={scrollToTop} data-testid={TEST_IDENTIFIER}>
            <Link href={itemLink} passHref>
                <DropdownItemLinkStyled onClick={context.onMenuToggleHandler} variant={variant}>
                    {itemName}
                </DropdownItemLinkStyled>
            </Link>
            {hasChildren && <DropdownSlideRight goToMenu={goToMenu} index={index} />}
        </DropdownItemStyled>
    );
};

/* @component */
export default DropdownItem;
