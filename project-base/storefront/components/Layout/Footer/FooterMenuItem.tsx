import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { TypeSimpleNotBlogArticleFragment } from 'graphql/requests/articlesInterface/articles/fragments/SimpleNotBlogArticleFragment.generated';

type FooterMenuItemProps = {
    title: string;
    items: TypeSimpleNotBlogArticleFragment[];
};

export const FooterMenuItem: FC<FooterMenuItemProps> = ({ items, title }) => (
    <>
        <h3 className="text-text mb-3 text-center uppercase lg:text-left">{title}</h3>

        <ul className="flex flex-col gap-1 lg:gap-4">
            {items.map((item) => (
                <li key={item.uuid}>
                    <ExtendedNextLink
                        className="text-text hover:text-text block text-sm no-underline hover:underline"
                        href={item.__typename === 'ArticleSite' ? item.slug : item.url}
                        rel={item.external ? 'nofollow noreferrer noopener' : undefined}
                        skeletonType="article"
                        target={item.external ? '_blank' : undefined}
                        type="article"
                    >
                        {item.name}
                    </ExtendedNextLink>
                </li>
            ))}
        </ul>
    </>
);
