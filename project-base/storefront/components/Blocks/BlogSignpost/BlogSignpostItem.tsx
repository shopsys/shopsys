import { Children } from './Children';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { twJoin } from 'tailwind-merge';
import { ListedBlogCategoryRecursiveType } from 'types/blogCategory';
import { twMergeCustom } from 'utils/twMerge';

type BlogSignpostItemProps = {
    isActive: boolean;
    itemLevel?: number;
    activeItem: string;
    blogCategory: ListedBlogCategoryRecursiveType;
    activeArticleCategoryPathUuids: string[];
};

export const BlogSignpostItem: FC<BlogSignpostItemProps> = ({
    blogCategory,
    isActive,
    itemLevel = 0,
    activeItem,
    activeArticleCategoryPathUuids,
}) => {
    const isFirstLevel = itemLevel === 0;
    const isSecondLevel = itemLevel === 1;
    const isThirdLevel = itemLevel === 2;
    const hasChildren = !!blogCategory.children?.length;

    const level1WrapperTwClassName = [
        'rounded-xl overflow-hidden',
        hasChildren && 'flex flex-col bg-background shadow-[inset_0_0_0_1px] shadow-borderAccentLess',
    ];

    const level1LinkTwClassName = [
        'flex items-center gap-3 px-3 py-[11px] font-semibold',
        !hasChildren && 'bg-backgroundMore',
        isActive && 'text-link',
        !hasChildren && isActive && 'bg-backgroundAccentLess',
    ];
    const level2LinkTwClassName = [
        'rounded-md py-2 px-4 bg-backgroundMore font-semibold',
        isActive && 'text-link bg-backgroundAccentLess',
    ];
    const level3LinkTwClassName = [
        'py-3 px-5 border-l border-borderAccentLess',
        isActive && 'text-link border-backgroundAccent',
    ];

    const level1ChildrenWrapperTwClassName = ['px-12 pb-3 flex flex-col gap-3'];
    const level2ChildrenWrapperTwClassName = ['px-3 mt-3'];

    return (
        <div className={twJoin(isFirstLevel && level1WrapperTwClassName)}>
            <ExtendedNextLink
                href={blogCategory.link}
                type="blogCategory"
                className={twMergeCustom(
                    'flex font-secondary text-sm text-text no-underline hover:text-linkHovered',
                    isFirstLevel && level1LinkTwClassName,
                    isSecondLevel && level2LinkTwClassName,
                    isThirdLevel && level3LinkTwClassName,
                )}
            >
                {isFirstLevel && (
                    <ArrowIcon
                        className={twMergeCustom(
                            'size-[18px] p-[2.5px] text-textSubtle',
                            !hasChildren && isActive && 'text-link',
                            !hasChildren && '-rotate-90',
                        )}
                    />
                )}
                {blogCategory.name}
            </ExtendedNextLink>

            {hasChildren && (
                <div
                    className={twJoin(
                        isFirstLevel && level1ChildrenWrapperTwClassName,
                        isSecondLevel && level2ChildrenWrapperTwClassName,
                    )}
                >
                    <Children
                        activeArticleCategoryPathUuids={activeArticleCategoryPathUuids}
                        activeItem={activeItem}
                        blogCategory={blogCategory}
                        itemLevel={itemLevel}
                    />
                </div>
            )}
        </div>
    );
};
