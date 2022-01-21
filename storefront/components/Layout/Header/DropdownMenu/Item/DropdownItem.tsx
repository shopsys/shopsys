import * as smoothscroll from 'smoothscroll-polyfill';
import { DropdownItemLinkStyled, DropdownItemStyled } from './DropdownItem.style';
import { FC, useContext, useEffect, useState } from 'react';
import {
    NavigationCategory as NavigationCategoryType,
    NavigationItem as NavigationItemType,
    NavigationSubCategory as NavigationSubCategoryType,
} from 'types/navigation';
import { DropdownItemType } from 'types/dropdown';
import { DropdownMenuContext } from 'components/Layout/Header/DropdownMenu//DropdownMenu';
import DropdownSlideRight from 'components/Layout/Header/DropdownMenu/SlideRight';
import Link from 'next/link';

type DropdownItemProps = DropdownItemType & {
    variant?: 'small';
    navigationItem?: NavigationItemType;
    columnCategory?: NavigationCategoryType;
    columnCategoryChild?: NavigationSubCategoryType;
};

const DropdownItem: FC<DropdownItemProps> = (props) => {
    const testIdentifier = 'layout-header-dropdownmenu-item';

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
        <DropdownItemStyled variant={props.variant} onClick={scrollToTop} data-testid={testIdentifier}>
            <Link href={itemLink} passHref>
                <DropdownItemLinkStyled onClick={context.onMenuToggleHandler} variant={props.variant}>
                    {itemName}
                </DropdownItemLinkStyled>
            </Link>
            {hasChildren && <DropdownSlideRight goToMenu={props.goToMenu} index={props.index} />}
        </DropdownItemStyled>
    );
};

/* @component */
export default DropdownItem;
