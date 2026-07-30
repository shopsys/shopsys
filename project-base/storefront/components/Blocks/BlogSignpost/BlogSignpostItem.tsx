import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { ListedBlogCategoryRecursiveType } from 'types/blogCategory';
import { twMergeCustom } from 'utils/twMerge';
import { BlogSignpostCategoryLink } from './BlogSignpostCategoryLink';
import { getBlogSignpostPanelId } from './blogSignpostUtils';

type BlogSignpostItemProps = {
    activeItem: string;
    blogCategory: ListedBlogCategoryRecursiveType;
    activeArticleCategoryPathUuids: string[];
    onPanelChange: (uuid: string) => void;
};

export const BlogSignpostItem: FC<BlogSignpostItemProps> = ({
    blogCategory,
    activeItem,
    activeArticleCategoryPathUuids,
    onPanelChange,
}) => {
    const hasChildren = !!blogCategory.children?.length;
    const isCurrent = blogCategory.uuid === activeItem;
    const isInActivePath = activeArticleCategoryPathUuids.includes(blogCategory.uuid);

    return (
        <>
            {hasChildren ? (
                <button
                    aria-controls={getBlogSignpostPanelId(blogCategory.uuid)}
                    className={twMergeCustom(
                        'group flex w-full cursor-pointer items-center justify-between rounded-md bg-background-more px-3 py-2 text-left font-secondary font-semibold text-sm text-text-default transition-colors hover:bg-background-most focus-visible:outline-2 focus-visible:outline-input-border-active focus-visible:outline-offset-2 active:bg-background-most',
                        isInActivePath &&
                            'bg-background-accent-less text-link-default hover:bg-background-accent-less active:bg-background-accent-less',
                    )}
                    type="button"
                    onClick={() => onPanelChange(blogCategory.uuid)}
                >
                    <span>{blogCategory.name}</span>
                    <ArrowIcon
                        className={twMergeCustom(
                            'size-4 shrink-0 -rotate-90 text-text-less transition-transform group-hover:translate-x-0.5',
                            isInActivePath && 'text-link',
                        )}
                    />
                </button>
            ) : (
                <BlogSignpostCategoryLink blogCategory={blogCategory} isCurrent={isCurrent} variant="item" />
            )}
        </>
    );
};
