import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { TypeColumnCategoriesFragment } from 'graphql/requests/navigation/fragments/ColumnCategoriesFragment.generated';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { twJoin } from 'tailwind-merge';

type NavigationItemColumnProps = {
    columnCategories: TypeColumnCategoriesFragment[];
    skeletonType?: PageType;
    onLinkClick: () => void;
};

export const NavigationItemColumn: FC<NavigationItemColumnProps> = ({
    className,
    columnCategories,
    skeletonType,
    onLinkClick,
}) => (
    <>
        {columnCategories.map((columnCategories, columnIndex) => (
            <ul key={columnIndex} className={twJoin('flex flex-col gap-9', className)}>
                {columnCategories.categories.map((columnCategory, columnCategoryIndex) => (
                    <li key={columnCategoryIndex}>
                        <ExtendedNextLink
                            className="bg-background-more hover:bg-background-most mb-2 flex justify-center rounded p-2"
                            href={columnCategory.slug}
                            skeletonType={skeletonType}
                            onClick={onLinkClick}
                        >
                            <div className="relative h-14 w-full">
                                <Image
                                    fill
                                    alt={columnCategory.mainImage?.name || columnCategory.name}
                                    className="object-contain mix-blend-multiply"
                                    sizes="80px"
                                    src={columnCategory.mainImage?.url}
                                />
                            </div>
                        </ExtendedNextLink>

                        <ExtendedNextLink
                            className="text-text-default mb-2 block font-bold no-underline hover:underline"
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
                                            className="text-text-default block text-sm no-underline hover:underline"
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
