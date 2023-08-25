import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { NavigationSubCategoriesLinkFragmentApi } from 'graphql/generated';

type NavigationItemColumnCategoryListProps = {
    columnCategoryChildren: NavigationSubCategoriesLinkFragmentApi['children'];
};

export const NavigationItemColumnCategoryList: FC<NavigationItemColumnCategoryListProps> = ({
    columnCategoryChildren,
}) => (
    <ul className="flex w-full flex-col gap-1">
        {columnCategoryChildren.map((columnCategoryChild) => (
            <li key={columnCategoryChild.name}>
                <ExtendedNextLink
                    type="category"
                    href={columnCategoryChild.slug}
                    className="block text-sm text-dark no-underline"
                >
                    {columnCategoryChild.name}
                </ExtendedNextLink>
            </li>
        ))}
    </ul>
);
