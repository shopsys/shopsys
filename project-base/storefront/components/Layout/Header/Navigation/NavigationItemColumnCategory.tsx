import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { NavigationItemColumnCategoryList } from 'components/Layout/Header/Navigation/NavigationItemColumnCategoryList';
import { ColumnCategoryFragmentApi } from 'graphql/generated';

type NavigationItemColumnCategoryProps = {
    columnCategory: ColumnCategoryFragmentApi;
};

export const NavigationItemColumnCategory: FC<NavigationItemColumnCategoryProps> = ({ columnCategory }) => {
    return (
        <li>
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
                <NavigationItemColumnCategoryList columnCategoryChildren={columnCategory.children} />
            )}
        </li>
    );
};
