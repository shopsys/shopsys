import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { TypeColumnCategoriesFragment } from 'graphql/requests/navigation/fragments/ColumnCategoriesFragment.generated';
import { PageType } from 'store/slices/createPageLoadingStateSlice';

type NavigationItemColumnProps = {
    columnCategories: TypeColumnCategoriesFragment[];
    skeletonType?: PageType;
    onLinkClick: () => void;
};

export const NavigationItemColumn: FC<NavigationItemColumnProps> = ({
    columnCategories,
    skeletonType,
    onLinkClick,
}) => (
    <>
        {columnCategories.map((columnCategories, columnIndex) => (
            <ul key={columnIndex} className="flex flex-col gap-9">
                {columnCategories.categories.map((columnCategory, columnCategoryIndex) => (
                    <li key={columnCategoryIndex}>
                        <ExtendedNextLink
                            className="mb-4 flex justify-center rounded bg-whiteSnow p-2"
                            href={columnCategory.slug}
                            skeletonType={skeletonType}
                            onClick={onLinkClick}
                        >
                            <Image
                                alt={columnCategory.mainImage?.name || columnCategory.name}
                                className="h-14 w-auto mix-blend-multiply"
                                height={56}
                                src={columnCategory.mainImage?.url}
                                width={64}
                            />
                        </ExtendedNextLink>

                        <ExtendedNextLink
                            className="mb-1 block font-bold text-dark no-underline"
                            href={columnCategory.slug}
                            skeletonType={skeletonType}
                            onClick={onLinkClick}
                        >
                            {columnCategory.name}
                        </ExtendedNextLink>

                        {!!columnCategory.children.length && (
                            <ul className="flex w-full flex-col gap-1">
                                {columnCategory.children.map((columnCategoryChild) => (
                                    <li key={columnCategoryChild.name}>
                                        <ExtendedNextLink
                                            className="block text-sm text-dark no-underline"
                                            href={columnCategoryChild.slug}
                                            skeletonType={skeletonType}
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
