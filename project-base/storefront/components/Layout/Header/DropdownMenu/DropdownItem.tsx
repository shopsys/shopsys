import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { DropdownMenuContext } from 'components/Layout/Header/DropdownMenu/DropdownMenuContext';
import { DropdownSlideRight } from 'components/Layout/Header/DropdownMenu/DropdownSlideRight';
import {
    CategoriesByColumnFragmentApi,
    ColumnCategoryFragmentApi,
    NavigationSubCategoriesLinkFragmentApi,
} from 'graphql/generated';
import { useContext, useEffect, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { DropdownItemType } from 'types/dropdown';

type DropdownItemProps = DropdownItemType & {
    variant?: 'small';
    navigationItem?: CategoriesByColumnFragmentApi;
    columnCategory?: ColumnCategoryFragmentApi;
    columnCategoryChild?: NavigationSubCategoriesLinkFragmentApi['children'][number];
};

const TEST_IDENTIFIER = 'layout-header-dropdownmenu-item';

export const DropdownItem: FC<DropdownItemProps> = ({
    navigationItem,
    variant,
    columnCategory,
    columnCategoryChild,
    index,
    goToMenu,
}) => {
    const context = useContext(DropdownMenuContext);
    const [hasChildren, setHasChildren] = useState(false);
    const [itemLink, setItemLink] = useState(navigationItem?.link || columnCategory?.slug || '');
    const [itemName, setItemName] = useState(navigationItem?.name || columnCategory?.name || '');

    const scrollToTop = () => {
        window.scroll({ top: 0, left: 0, behavior: 'smooth' });
    };

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
        <div
            className={twJoin('flex border-b border-greyLighter last:border-b-0', variant === 'small' && 'mx-8')}
            onClick={scrollToTop}
            data-testid={TEST_IDENTIFIER}
        >
            <ExtendedNextLink
                type="category"
                href={itemLink}
                className={twJoin(
                    'flex-1 font-bold text-dark no-underline',
                    variant === 'small' ? 'py-4 text-sm' : 'py-5 pr-11 pl-8 text-base uppercase',
                )}
                onClick={context.onMenuToggleHandler}
            >
                {itemName}
            </ExtendedNextLink>
            {hasChildren && <DropdownSlideRight goToMenu={goToMenu} index={index} />}
        </div>
    );
};
