import { DropdownItemLinkStyled, DropdownItemStyled } from './DropdownItem.style';
import DropdownSlideTo from '../SlideTo';
import Link from 'next/link';
import { ReactElement } from 'react';

const DropdownItem = (props): ReactElement => {
    const hasChildren = () => {
        if (props.level === 'main') {
            return props.itemData.categoriesByColumns.length > 0;
        } else if (props.level === 'secondary') {
            return props.itemData.children.length > 0;
        }

        return false;
    };

    const itemLink = () => {
        if (props.level === 'main') {
            return props.itemData.link;
        } else if (props.level === 'secondary' || props.level === 'third') {
            return props.itemData.slug;
        }

        return false;
    };

    return (
        <DropdownItemStyled variant={props.variant}>
            <Link href={itemLink()} passHref>
                <DropdownItemLinkStyled variant={props.variant}>{props.itemData.name}</DropdownItemLinkStyled>
            </Link>
            {hasChildren() && (
                <DropdownSlideTo
                    changeState={props.changeState}
                    goToMenu={props.goToMenu}
                    slideTo={props.slideTo}
                    activeChild={props.activeChild}
                    indexes={props.indexes}
                />
            )}
        </DropdownItemStyled>
    );
};

/* @component */
export default DropdownItem;
