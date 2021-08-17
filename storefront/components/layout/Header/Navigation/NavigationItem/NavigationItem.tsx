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
import { ReactElement, useState } from 'react';
import { debounce } from 'lodash';
import Link from 'next/link';

type navigationItem = {
    navigationItem: {
        name: string;
        link: string;
        categoriesByColumns: Array<{
            columnNumber: number;
            categories: Array<{
                name: string;
                slug: string;
                image: {
                    url: string;
                    width: number;
                    height: number;
                };
                children: Array<{
                    name: string;
                    slug: string;
                }>;
            }>;
        }>;
    };
};

const NavigationItem = ({ asKey, navigationItem }: { asKey: number } & navigationItem): ReactElement => {
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
    const hasChildren = navigationItem.categoriesByColumns.length > 0;

    return (
        <NavigationItemStyled key={asKey} onMouseEnter={openSubmenu} onMouseLeave={hideSubmenu} open={isHovered}>
            <Link href={navigationItem.link} passHref>
                <NavigationItemLinkStyled>
                    {navigationItem.name}
                    {hasChildren && <NavigationItemLinkIconStyled src="/svg/arrow.svg" alt="" width={14} />}
                </NavigationItemLinkStyled>
            </Link>
            {hasChildren && (
                <NavigationItemSubStyled>
                    <NavigationItemSubWrapStyled>
                        {navigationItem.categoriesByColumns.map((columnCategories, columnKey) => (
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
