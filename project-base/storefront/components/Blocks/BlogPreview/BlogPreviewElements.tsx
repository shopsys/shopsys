import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';

export const ArticleLink: FC<{ href: string }> = ({ href, children, className }) => (
    <ExtendedNextLink type="blogArticle" href={href} className={className}>
        {children}
    </ExtendedNextLink>
);
