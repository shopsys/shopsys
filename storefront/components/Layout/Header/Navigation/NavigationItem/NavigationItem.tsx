import { FC, useState } from 'react';
import { initialState, userActions } from 'redux/slices/user';
import {
    NavigationItemLinkIconStyled,
    NavigationItemLinkStyled,
    NavigationItemStyled,
    NavigationItemSubStyled,
    NavigationItemSubWrapStyled,
} from './NavigationItem.style';
import { debounce } from 'lodash';
import { NavigationItem as NavigationItemType } from 'types/navigation';
import NavigationLeaf from 'components/Layout/Header/Navigation/NavigationLeaf';
import NextLink from 'next/link';
import { useRouter } from 'next/router';
import { useShopsysDispatch } from 'redux/main';

type NavigationItemProps = {
    navigationItem: NavigationItemType;
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
    const dispatch = useShopsysDispatch();
    const router = useRouter();

    return (
        <>
            {props.navigationItem.link === router.asPath ? (
                <NavigationItemStyled
                    onMouseEnter={openSubmenu}
                    onMouseLeave={hideSubmenu}
                    isOpen={isHovered}
                    onClick={() => dispatch(userActions.setPagination({ ...initialState.pagination }))}
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
                <NavigationItemStyled onMouseEnter={openSubmenu} onMouseLeave={hideSubmenu} isOpen={isHovered}>
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

/* @component */
export default NavigationItem;
