import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ListedBlogCategoryRecursiveType } from 'types/blogCategory';
import { twMergeCustom } from 'utils/twMerge';

type BlogSignpostCategoryLinkProps = {
    blogCategory: ListedBlogCategoryRecursiveType;
    isCurrent: boolean;
    label?: string;
    variant: 'item' | 'overview';
};

export const BlogSignpostCategoryLink: FC<BlogSignpostCategoryLinkProps> = ({
    blogCategory,
    isCurrent,
    label = blogCategory.name,
    variant,
}) => (
    <ExtendedNextLink
        aria-current={isCurrent ? 'page' : undefined}
        href={blogCategory.link}
        type="blogCategory"
        className={twMergeCustom(
            'flex w-full items-center font-secondary text-sm text-text-default no-underline transition-colors',
            variant === 'item' &&
                'rounded-md bg-background-more px-3 py-2 font-semibold hover:bg-background-most hover:text-text-default hover:no-underline focus-visible:outline-2 focus-visible:outline-input-border-active focus-visible:outline-offset-2 active:bg-background-most',
            variant === 'overview' && 'w-fit px-2 py-1 text-link-default underline-offset-2 hover:underline',
            isCurrent && 'text-link-default',
            isCurrent &&
                variant === 'item' &&
                'bg-background-accent-less hover:bg-background-accent-less hover:text-link-default active:bg-background-accent-less',
            isCurrent && variant === 'overview' && 'font-semibold',
        )}
    >
        <span>{label}</span>
    </ExtendedNextLink>
);
