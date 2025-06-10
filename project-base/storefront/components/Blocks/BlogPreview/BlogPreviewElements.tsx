import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { twJoin } from 'tailwind-merge';

export const ArticleLink: FC<{ href: string; tabIndex?: number }> = ({ href, children, className, tabIndex }) => (
    <ExtendedNextLink
        className={twJoin('no-underline hover:underline', className)}
        href={href}
        tabIndex={tabIndex ? tabIndex : 0}
        type="blogArticle"
    >
        {children}
    </ExtendedNextLink>
);
