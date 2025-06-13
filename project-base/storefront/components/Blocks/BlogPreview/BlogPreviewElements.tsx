import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { twJoin } from 'tailwind-merge';

export const ArticleLink: FC<{ href: string; tabIndex?: number; ariaLabel?: string; title: string }> = ({
    href,
    children,
    className,
    tabIndex,
    ariaLabel,
    title,
}) => (
    <ExtendedNextLink
        aria-label={ariaLabel}
        className={twJoin('no-underline hover:underline', className)}
        href={href}
        tabIndex={tabIndex ? tabIndex : 0}
        title={title}
        type="blogArticle"
    >
        {children}
    </ExtendedNextLink>
);
