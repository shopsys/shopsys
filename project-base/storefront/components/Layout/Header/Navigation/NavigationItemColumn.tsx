import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ColumnCategoriesFragmentApi } from 'graphql/generated';
import { Image } from 'components/Basic/Image/Image';

type NavigationItemColumnProps = {
    columnCategories: ColumnCategoriesFragmentApi[];
    onLinkClick: () => void;
};

export const NavigationItemColumn: FC<NavigationItemColumnProps> = ({ columnCategories, onLinkClick }) => (
    <>
        {columnCategories.map((columnCategories, columnIndex) => (
            <ul className="flex flex-col gap-9" key={columnIndex}>
                {columnCategories.categories.map((columnCategory, columnCategoryIndex) => (
                    <li key={columnCategoryIndex}>
                        <ExtendedNextLink
                            href={columnCategory.slug}
                            type="static"
                            className="mb-4 flex justify-center rounded bg-dark bg-opacity-5 p-2"
                            onClick={onLinkClick}
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
                            onClick={onLinkClick}
                        >
                            {columnCategory.name}
                        </ExtendedNextLink>

                        {!!columnCategory.children.length && (
                            <ul className="flex w-full flex-col gap-1">
                                {columnCategory.children.map((columnCategoryChild) => (
                                    <li key={columnCategoryChild.name}>
                                        <ExtendedNextLink
                                            type="category"
                                            href={columnCategoryChild.slug}
                                            className="block text-sm text-dark no-underline"
                                            onClick={onLinkClick}
                                        >
                                            {columnCategoryChild.name}
                                        </ExtendedNextLink>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </li>
                ))}
            </ul>
        ))}
    </>
);
