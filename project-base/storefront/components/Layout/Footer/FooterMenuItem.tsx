import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { TypeSimpleNotBlogArticleFragment } from 'graphql/requests/articlesInterface/articles/fragments/SimpleNotBlogArticleFragment.generated';

type FooterMenuItemProps = {
    title: string;
    items: TypeSimpleNotBlogArticleFragment[];
};

export const FooterMenuItem: FC<FooterMenuItemProps> = ({ items, title }) => (
    <>
        <h3 className="mb-3 text-center uppercase text-text lg:text-left">{title}</h3>

        <ul className="flex flex-col gap-1 lg:gap-4">
            {items.map((item) => (
                <li key={item.uuid}>
                    <ExtendedNextLink
                        className="block text-sm text-text no-underline hover:text-text"
                        href={item.__typename === 'ArticleSite' ? item.slug : item.url}
                        rel={item.external ? 'nofollow noreferrer noopener' : undefined}
                        target={item.external ? '_blank' : undefined}
                    >
                        {item.name}
                    </ExtendedNextLink>
                </li>
            ))}
        </ul>
    </>
);
