import {
    NavigationItemLinkIconStyled,
    NavigationItemLinkStyled,
    NavigationItemStyled,
    NavigationItemSubStyled,
    NavigationItemSubWrapStyled,
} from './NavigationItem.style';
import { NavigationLeaf } from 'components/Layout/Header/Navigation/NavigationLeaf/NavigationLeaf';
import { useMouseHoverDebounce } from 'hooks/ui/useMouseHoverDebounce';
import NextLink from 'next/link';
import { useRouter } from 'next/router';
import { FC, useState } from 'react';
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
    const router = useRouter();

    return (
        <>
            {props.navigationItem.link === router.asPath ? (
                <NavigationItemStyled
                    onMouseEnter={openSubmenu}
                    onMouseLeave={hideSubmenu}
                    data-testid={TEST_IDENTIFIER}
                >
                    <NextLink href={props.navigationItem.link} passHref>
                        <NavigationItemLinkStyled isOpen={isHovered}>
                            {props.navigationItem.name}
                            {hasChildren && (
                                <NavigationItemLinkIconStyled isOpen={isHovered} iconType="icon" icon="Arrow" />
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
            ) : (
                <NavigationItemStyled
                    onMouseEnter={openSubmenu}
                    onMouseLeave={hideSubmenu}
                    data-testid={TEST_IDENTIFIER}
                >
                    <NextLink href={props.navigationItem.link} passHref>
                        <NavigationItemLinkStyled isOpen={isHovered}>
                            {props.navigationItem.name}
                            {hasChildren && (
                                <NavigationItemLinkIconStyled isOpen={isHovered} iconType="icon" icon="Arrow" />
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
            )}
        </>
    );
};
