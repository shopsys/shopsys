import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { TypeColumnCategoriesFragment } from 'graphql/requests/navigation/fragments/ColumnCategoriesFragment.generated';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { twJoin } from 'tailwind-merge';

type NavigationItemColumnProps = {
    columnCategories: TypeColumnCategoriesFragment[];
    skeletonType?: PageType;
    onLinkClick: () => void;
    className?: string;
};

export const NavigationItemColumn: FC<NavigationItemColumnProps> = ({
    className,
    columnCategories,
    skeletonType,
    onLinkClick,
}) => {
    const sortedColumnCategories = [...columnCategories].sort(
        (firstColumnCategory, secondColumnCategory) =>
            firstColumnCategory.columnNumber - secondColumnCategory.columnNumber,
    );

    return (
        <ul className={twJoin('grid grid-cols-4 gap-10', className)}>
            {sortedColumnCategories.flatMap((columnCategories) =>
                columnCategories.categories.map((columnCategory, columnCategoryIndex) => {
                    const mainImageUrl = columnCategory.mainImage?.url;
                    const hasMainImage = !!mainImageUrl;

                    return (
                        <li
                            key={columnCategory.uuid}
                            className="group/main-category min-w-0 rounded-md p-2 transition-colors hover:bg-background-more"
                            style={{
                                gridColumn: columnCategories.columnNumber,
                                gridRow: columnCategoryIndex + 1,
                            }}
                        >
                            <ExtendedNextLink
                                className={twJoin(
                                    'inline-grid min-h-10 max-w-full items-center gap-3 rounded-sm font-bold font-secondary text-text-default no-underline hover:underline',
                                    hasMainImage ? 'grid-cols-[40px_1fr]' : 'ml-13 w-fit',
                                )}
                                href={columnCategory.slug}
                                skeletonType={skeletonType}
                                onClick={onLinkClick}
                            >
                                {hasMainImage && (
                                    <div className="relative size-12 w-auto" aria-hidden="true">
                                        <Image
                                            fill
                                            alt=""
                                            className="object-contain mix-blend-multiply"
                                            sizes="40px"
                                            src={mainImageUrl}
                                        />
                                    </div>
                                )}

                                {columnCategory.name}
                            </ExtendedNextLink>

                            {!!columnCategory.children.length && (
                                <ul className="ml-13 flex flex-col gap-1.5">
                                    {columnCategory.children.map((columnCategoryChild) => (
                                        <li key={columnCategoryChild.name} className="group/child-category">
                                            <ExtendedNextLink
                                                className="inline-block max-w-full self-start rounded-sm font-secondary font-semibold text-sm text-text-default no-underline hover:underline"
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
                    );
                }),
            )}
        </ul>
    );
};
