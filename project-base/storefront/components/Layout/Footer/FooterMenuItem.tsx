import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Heading } from 'components/Basic/Heading/Heading';
import { SimpleNotBlogArticleFragmentApi } from 'graphql/generated';

type FooterMenuItemProps = {
    title: string;
    items: SimpleNotBlogArticleFragmentApi[];
};

const TEST_IDENTIFIER = 'layout-footer-footermenuitem';

export const FooterMenuItem: FC<FooterMenuItemProps> = ({ items, title }) => (
    <div className="px-5 lg:w-1/4 lg:pl-5" data-testid={TEST_IDENTIFIER}>
        <Heading
            type="h4"
            className="mb-0 flex items-center justify-between py-5 font-bold uppercase text-white lg:pointer-events-none lg:mb-6 lg:p-0"
        >
            {title}
        </Heading>
        <ul className="pb-5 lg:pb-0">
            {items.map((item) => (
                <li className="mb-1 last:mb-0 lg:mb-4" key={item.uuid}>
                    <ExtendedNextLink
                        href={item.__typename === 'ArticleSite' ? item.slug : item.url}
                        type="static"
                        passHref
                    >
                        <a
                            className="block text-sm text-greyLight no-underline hover:text-greyLight"
                            target={item.external ? '_blank' : undefined}
                            rel={item.external ? 'nofollow noreferrer noopener' : undefined}
                        >
                            {item.name}
                        </a>
                    </ExtendedNextLink>
                </li>
            ))}
        </ul>
    </div>
);
