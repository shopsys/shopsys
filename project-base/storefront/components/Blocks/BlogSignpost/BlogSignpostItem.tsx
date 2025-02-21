import { Children } from './Children';
import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { AnimatePresence } from 'framer-motion';
import { twJoin } from 'tailwind-merge';
import { ListedBlogCategoryRecursiveType } from 'types/blogCategory';
import { twMergeCustom } from 'utils/twMerge';

type BlogSignpostItemProps = {
    isActive: boolean;
    itemLevel?: number;
    activeItem: string;
    blogCategory: ListedBlogCategoryRecursiveType;
    activeArticleCategoryPathUuids: string[];
    openUuids: string[];
    handleToggle: (uuids: string[]) => void;
};

export const BlogSignpostItem: FC<BlogSignpostItemProps> = ({
    blogCategory,
    isActive,
    itemLevel = 0,
    activeItem,
    activeArticleCategoryPathUuids,
    handleToggle,
    openUuids,
}) => {
    const isFirstLevel = itemLevel === 0;
    const isSecondLevel = itemLevel === 1;
    const isThirdLevel = itemLevel === 2;
    const hasChildren = !!blogCategory.children?.length;
    const isOpen = openUuids.includes(blogCategory.uuid);

    const level1WrapperTwClassName = [
        'rounded-xl',
        hasChildren && isActive && 'flex flex-col bg-background shadow-[inset_0_0_0_1px] shadow-borderAccentLess',
        'max-vl:max-h-[400px] max-vl:overflow-auto vl:overflow-hidden',
    ];

    const level1ItemTwClassName = [
        'flex px-5 py-3',
        (!hasChildren || !isActive) && 'bg-backgroundMore',
        !hasChildren && isActive && 'bg-backgroundAccentLess',
    ];
    const level2ItemTwClassName = [
        'flex items-center rounded-md py-2 px-2 bg-backgroundMore',
        isActive && 'bg-backgroundAccentLess',
    ];
    const level3ItemTwClassName = ['py-3 px-5 border-l border-borderAccentLess', isActive && 'border-backgroundAccent'];

    const level1LinkTwClassName = ['font-semibold', isActive && 'text-link'];
    const level2LinkTwClassName = ['font-semibold pl-2', isActive && 'text-link'];
    const level3LinkTwClassName = isActive && 'text-link';

    const level1ChildrenWrapperTwClassName = ['px-12 pb-3 flex flex-col gap-3'];
    const level2ChildrenWrapperTwClassName = ['px-3 mt-3'];

    return (
        <div className={twJoin(isFirstLevel && level1WrapperTwClassName)}>
            <div
                className={twMergeCustom(
                    isFirstLevel && level1ItemTwClassName,
                    isSecondLevel && level2ItemTwClassName,
                    isThirdLevel && level3ItemTwClassName,
                )}
            >
                {isSecondLevel && hasChildren && (
                    <ArrowIcon
                        className={twMergeCustom(
                            'text-textSubtle size-4 -rotate-90 cursor-pointer transition-all',
                            isActive && 'text-link',
                            isOpen && 'rotate-0',
                        )}
                        onClick={(e) => {
                            e.stopPropagation();
                            e.preventDefault();
                            handleToggle([blogCategory.uuid]);
                        }}
                    />
                )}
                <ExtendedNextLink
                    href={blogCategory.link}
                    type="blogCategory"
                    className={twMergeCustom(
                        'font-secondary text-text hover:text-linkHovered text-sm no-underline',
                        isFirstLevel && level1LinkTwClassName,
                        isSecondLevel && level2LinkTwClassName,
                        isThirdLevel && level3LinkTwClassName,
                    )}
                >
                    {blogCategory.name}
                </ExtendedNextLink>
            </div>

            <AnimatePresence initial={false}>
                {((hasChildren && isFirstLevel) || isOpen) && (
                    <AnimateCollapseDiv className="!block">
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
                                handleToggle={handleToggle}
                                itemLevel={itemLevel}
                                openUuids={openUuids}
                            />
                        </div>
                    </AnimateCollapseDiv>
                )}
            </AnimatePresence>
        </div>
    );
};
