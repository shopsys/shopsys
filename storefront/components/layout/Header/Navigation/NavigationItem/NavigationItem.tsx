import { FC, useState } from 'react';
import {
    NavigationItemLinkIconStyled,
    NavigationItemLinkStyled,
    NavigationItemStyled,
    NavigationItemSubListItemImageStyled,
    NavigationItemSubListItemLinkStyled,
    NavigationItemSubListItemStyled,
    NavigationItemSubListStyled,
    NavigationItemSubStyled,
    NavigationItemSubWrapStyled,
} from './NavigationItem.style';
import { debounce } from 'lodash';
import Link from 'next/link';
import { NavigationItem as NavigationItemType } from '../../../../../connectors/navigation/Navigation';

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
                    {hasChildren && <NavigationItemLinkIconStyled src="/svg/arrow.svg" alt="" width={14} />}
                </NavigationItemLinkStyled>
            </Link>
            {hasChildren && (
                <NavigationItemSubStyled>
                    <NavigationItemSubWrapStyled>
                        {props.navigationItem.categoriesByColumns.map((columnCategories, columnKey) => (
                            <NavigationItemSubListStyled key={columnKey}>
                                {columnCategories.categories.map((columnCategory, categoryKey) => (
                                    <NavigationItemSubListItemStyled key={categoryKey}>
                                        <Link href="/" passHref>
                                            <NavigationItemSubListItemImageStyled>
                                                <img
                                                    src={columnCategory.image.url}
                                                    width={columnCategory.image.width}
                                                />
                                            </NavigationItemSubListItemImageStyled>
                                        </Link>
                                        <Link href={columnCategory.slug} passHref>
                                            <NavigationItemSubListItemLinkStyled>
                                                {columnCategory.name}
                                            </NavigationItemSubListItemLinkStyled>
                                        </Link>
                                        {columnCategory.children.length > 0 && (
                                            <NavigationItemSubListStyled isChildren>
                                                {columnCategory.children.map(
                                                    (columnCategoryChild, categoryChildKey) => (
                                                        <NavigationItemSubListItemStyled
                                                            isChildren
                                                            key={categoryChildKey}
                                                        >
                                                            <Link href={columnCategoryChild.slug} passHref>
                                                                <NavigationItemSubListItemLinkStyled isChildren>
                                                                    {columnCategoryChild.name}
                                                                </NavigationItemSubListItemLinkStyled>
                                                            </Link>
                                                        </NavigationItemSubListItemStyled>
                                                    ),
                                                )}
                                            </NavigationItemSubListStyled>
                                        )}
                                    </NavigationItemSubListItemStyled>
                                ))}
                            </NavigationItemSubListStyled>
                        ))}
                    </NavigationItemSubWrapStyled>
                </NavigationItemSubStyled>
            )}
        </NavigationItemStyled>
    );
};

/* @component */
export default NavigationItem;
