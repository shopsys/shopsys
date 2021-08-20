import { FC, useState } from 'react';
import {
    NavigationItemLinkIconStyled,
    NavigationItemLinkStyled,
    NavigationItemStyled,
    NavigationItemSubStyled,
    NavigationItemSubWrapStyled,
} from './NavigationItem.style';
import { debounce } from 'lodash';
import Link from 'next/link';
import { NavigationItem as NavigationItemType } from '../../../../../connectors/navigation/Navigation';
import NavigationLeaf from '../NavigationLeaf';
import ShopsysIcon from '../../../../basic/ShopsysIcon';

type NavigationItemProps = {
    navigationItem: NavigationItemType;
    asKey: number;
};

const NavigationItem: FC<NavigationItemProps> = (props) => {
    const [isHovered, setIsHovered] = useState<boolean>(false);

    const openSubmenu = () => {
        if (hasChildren) {
            setIsHovered(true);
        }
    };
    const hideSubmenu = debounce(() => {
        if (hasChildren) {
            setIsHovered(false);
        }
    }, 300);
    const hasChildren = props.navigationItem.categoriesByColumns.length > 0;

    return (
        <NavigationItemStyled onMouseEnter={openSubmenu} onMouseLeave={hideSubmenu} isOpen={isHovered}>
            <Link href={props.navigationItem.link} passHref>
                <NavigationItemLinkStyled>
                    {props.navigationItem.name}
                    {hasChildren && (
                        <NavigationItemLinkIconStyled>
                            <ShopsysIcon icon="arrow" iconHeight={14} />
                        </NavigationItemLinkIconStyled>
                    )}
                </NavigationItemLinkStyled>
            </Link>
            {hasChildren && (
                <NavigationItemSubStyled>
                    <NavigationItemSubWrapStyled>
                        <NavigationLeaf columnCategories={props.navigationItem.categoriesByColumns} />
                    </NavigationItemSubWrapStyled>
                </NavigationItemSubStyled>
            )}
        </NavigationItemStyled>
    );
};

/* @component */
export default NavigationItem;
