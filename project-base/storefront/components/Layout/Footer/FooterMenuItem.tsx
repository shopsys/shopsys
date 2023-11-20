import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { SimpleNotBlogArticleFragmentApi } from 'graphql/generated';

type FooterMenuItemProps = {
    title: string;
    items: SimpleNotBlogArticleFragmentApi[];
};

export const FooterMenuItem: FC<FooterMenuItemProps> = ({ items, title }) => (
    <>
        <h3 className="mb-3 text-center uppercase text-white lg:text-left">{title}</h3>

        <ul className="flex flex-col gap-1 lg:gap-4">
            {items.map((item) => (
                <li key={item.uuid}>
                    <ExtendedNextLink
                        className="block text-sm text-greyLight no-underline hover:text-greyLight"
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
