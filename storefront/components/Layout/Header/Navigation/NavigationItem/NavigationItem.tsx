import {
    NavigationItemLinkStyled,
    NavigationItemStyled,
    NavigationItemSubStyled,
    NavigationItemSubWrapStyled,
} from './NavigationItem.style';
import { Icon } from 'components/Basic/Icon/Icon';
import { NavigationLeaf } from 'components/Layout/Header/Navigation/NavigationLeaf/NavigationLeaf';
import { useMouseHoverDebounce } from 'hooks/ui/useMouseHoverDebounce';
import NextLink from 'next/link';
import { FC, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { NavigationItem as NavigationItemType } from 'types/navigation';

type NavigationItemProps = {
    navigationItem: NavigationItemType;
};

const TEST_IDENTIFIER = 'layout-header-navigation-navigationitem';

export const NavigationItem: FC<NavigationItemProps> = (props) => {
    const [onMouseEnterTrigger, setOnMouseEnterTrigger] = useState(false);
    const [onMouseLeaveTrigger, setOnMouseLeaveTrigger] = useState(false);
    const isHovered = useMouseHoverDebounce(onMouseEnterTrigger, onMouseLeaveTrigger);

    const openSubmenu = () => {
        if (hasChildren) {
            setOnMouseEnterTrigger(!onMouseEnterTrigger);
        }
    };
    const hideSubmenu = () => {
        if (hasChildren) {
            setOnMouseLeaveTrigger(!onMouseLeaveTrigger);
        }
    };
    const hasChildren = props.navigationItem.categoriesByColumns.length > 0;

    return (
        <NavigationItemStyled onMouseEnter={openSubmenu} onMouseLeave={hideSubmenu} data-testid={TEST_IDENTIFIER}>
            <NextLink href={props.navigationItem.link} passHref>
                <NavigationItemLinkStyled isOpen={isHovered}>
                    {props.navigationItem.name}
                    {hasChildren && (
                        <Icon
                            iconType="icon"
                            icon="Arrow"
                            className={twJoin('ml-2 text-white ', isHovered && 'rotate-180 text-orangeLight')}
                        />
                    )}
                </NavigationItemLinkStyled>
            </NextLink>
            {hasChildren && (
                <NavigationItemSubStyled isOpen={isHovered}>
                    <NavigationItemSubWrapStyled>
                        <NavigationLeaf columnCategories={props.navigationItem.categoriesByColumns} />
                    </NavigationItemSubWrapStyled>
                </NavigationItemSubStyled>
            )}
        </NavigationItemStyled>
    );
};
