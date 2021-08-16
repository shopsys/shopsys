import { getNavigationItems } from '../../../connectors/navigation/Navigation';
import { ReactElement } from 'react';
import ShopsysHeading from '../../basic/ShopsysHeading';
import ShopsysLink from '../../basic/ShopsysLink';

const Navigation = (): ReactElement | null => {
    const navigationItems = getNavigationItems();

    if (navigationItems === undefined || navigationItems.length === 0) {
        return null;
    }

    return (
        <>
            <ul>
                {navigationItems.map((navigationItem, key) => (
                    <li key={key}>
                        <ShopsysHeading type={'h2'}>
                            <ShopsysLink href={navigationItem.link}>{navigationItem.name}</ShopsysLink>
                        </ShopsysHeading>
                        <ul>
                            {navigationItem.categoriesByColumns.map((columnCategories, columnKey) => (
                                <li key={columnKey}>
                                    (Column: {columnCategories.columnNumber})
                                    <ul>
                                        {columnCategories.categories.map((columnCategory, categoryKey) => (
                                            <li key={categoryKey}>
                                                <img src={columnCategory.image.url} width={columnCategory.image.width} />
                                                <ShopsysHeading type={'h3'}>
                                                    <ShopsysLink href={columnCategory.slug}>
                                                        {columnCategory.name}
                                                    </ShopsysLink>
                                                </ShopsysHeading>
                                                <ul>
                                                    {columnCategory.children.map(
                                                        (columnCategoryChild, categoryChildKey) => (
                                                            <li key={categoryChildKey}>
                                                                <ShopsysLink href={columnCategoryChild.slug}>
                                                                    {columnCategoryChild.name}
                                                                </ShopsysLink>
                                                            </li>
                                                        ),
                                                    )}
                                                </ul>
                                            </li>
                                        ))}
                                    </ul>
                                </li>
                            ))}
                        </ul>
                    </li>
                ))}
            </ul>
        </>
    );
};

/* @component */
export default Navigation;
