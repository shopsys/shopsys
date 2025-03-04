import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';

export const ArticleLink: FC<{ href: string }> = ({ href, children, className }) => (
    <ExtendedNextLink className={className} href={href} type="blogArticle">
        {children}
    </ExtendedNextLink>
);
