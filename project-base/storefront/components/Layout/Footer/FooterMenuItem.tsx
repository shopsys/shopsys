import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Heading } from 'components/Basic/Heading/Heading';
import { SimpleNotBlogArticleFragmentApi } from 'graphql/generated';

type FooterMenuItemProps = {
    title: string;
    items: SimpleNotBlogArticleFragmentApi[];
};

export const FooterMenuItem: FC<FooterMenuItemProps> = ({ items, title }) => (
    <>
        <Heading type="h3" className="text-center font-bold uppercase text-white lg:text-left">
            {title}
        </Heading>

        <ul className="flex flex-col gap-1 lg:gap-4">
            {items.map((item) => (
                <li key={item.uuid}>
                    <ExtendedNextLink
                        href={item.__typename === 'ArticleSite' ? item.slug : item.url}
                        type="static"
                        className="block text-sm text-greyLight no-underline hover:text-greyLight"
                        target={item.external ? '_blank' : undefined}
                        rel={item.external ? 'nofollow noreferrer noopener' : undefined}
                    >
                        {item.name}
                    </ExtendedNextLink>
                </li>
            ))}
        </ul>
    </>
);
