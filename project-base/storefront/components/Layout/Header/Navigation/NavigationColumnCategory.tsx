import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { NavigationSubList } from 'components/Layout/Header/Navigation/NavigationSubList';
import { ColumnCategoryFragmentApi } from 'graphql/requests/navigation/fragments/ColumnCategoryFragment.generated';
type NavigationColumnCategoryProps = {
    columnCategory: ColumnCategoryFragmentApi;
};

const TEST_IDENTIFIER = 'layout-header-navigation-navigationcolumncategory';

export const NavigationColumnCategory: FC<NavigationColumnCategoryProps> = ({ columnCategory }) => {
    return (
        <li className="mb-9 w-full last:mb-0" data-testid={TEST_IDENTIFIER}>
            <ExtendedNextLink
                href={columnCategory.slug}
                type="static"
                className="mb-4 flex justify-center rounded bg-dark bg-opacity-5 p-2"
            >
                <Image
                    image={columnCategory.mainImage}
                    type="default"
                    alt={columnCategory.mainImage?.name || columnCategory.name}
                    className="h-16 mix-blend-multiply"
                />
            </ExtendedNextLink>
            <ExtendedNextLink
                href={columnCategory.slug}
                type="static"
                className="mb-1 block font-bold text-dark no-underline"
            >
                {columnCategory.name}
            </ExtendedNextLink>
            {columnCategory.children.length > 0 && (
                <NavigationSubList columnCategoryChildren={columnCategory.children} />
            )}
        </li>
    );
};
