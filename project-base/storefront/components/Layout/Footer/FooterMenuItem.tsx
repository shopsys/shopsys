import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Heading } from 'components/Basic/Heading/Heading';
import { SimpleNotBlogArticleFragmentApi } from 'graphql/generated';

type FooterMenuItemProps = {
    title: string;
    items: SimpleNotBlogArticleFragmentApi[];
};

export const FooterMenuItem: FC<FooterMenuItemProps> = ({ items, title }) => (
    <>
        <Heading className="text-center font-bold uppercase text-white lg:text-left" type="h3">
            {title}
        </Heading>

        <ul className="flex flex-col gap-1 lg:gap-4">
            {items.map((item) => (
                <li key={item.uuid}>
                    <ExtendedNextLink
                        className="block text-sm text-greyLight no-underline hover:text-greyLight"
                        href={item.__typename === 'ArticleSite' ? item.slug : item.url}
                        rel={item.external ? 'nofollow noreferrer noopener' : undefined}
                        target={item.external ? '_blank' : undefined}
                        type="static"
                    >
                        {item.name}
                    </ExtendedNextLink>
                </li>
            ))}
        </ul>
    </>
);
