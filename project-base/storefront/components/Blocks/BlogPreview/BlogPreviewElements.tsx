import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { twJoin } from 'tailwind-merge';

type ArticleLinkProps = {
    href: string;
    tabIndex?: number;
    ariaLabel?: string;
    title: string;
    preventRedirectOnTextSelection?: boolean;
    draggable?: boolean;
};

export const ArticleLink: FC<ArticleLinkProps> = ({
    href,
    children,
    className,
    tabIndex,
    ariaLabel,
    title,
    preventRedirectOnTextSelection,
    draggable,
}) => (
    <ExtendedNextLink
        preventRedirectOnTextSelection={preventRedirectOnTextSelection}
        aria-label={ariaLabel}
        className={twJoin('rounded-xl no-underline hover:no-underline focus-visible:outline-hidden', className)}
        data-focus-color="preserve"
        draggable={draggable}
        href={href}
        tabIndex={tabIndex ?? 0}
        title={title}
        type="blogArticle"
    >
        {children}
    </ExtendedNextLink>
);
