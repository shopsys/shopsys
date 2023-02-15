import {
    FooterMenuItemStyled,
    FooterMenuListItemLinkStyled,
    FooterMenuListItemStyled,
    FooterMenuListStyled,
} from './FooterMenuItem.style';
import { Heading } from 'components/Basic/Heading/Heading';
import { SimpleNotBlogArticleFragmentApi } from 'graphql/generated';
import NextLink from 'next/link';
import { FC } from 'react';

type FooterMenuItemProps = {
    title: string;
    items: SimpleNotBlogArticleFragmentApi[];
};

const TEST_IDENTIFIER = 'layout-footer-footermenuitem';

export const FooterMenuItem: FC<FooterMenuItemProps> = ({ items, title }) => (
    <FooterMenuItemStyled data-testid={TEST_IDENTIFIER}>
        <Heading
            type="h4"
            className="mb-0 flex items-center justify-between py-5 font-bold uppercase text-white lg:pointer-events-none lg:mb-6 lg:p-0"
        >
            {title}
        </Heading>
        <FooterMenuListStyled>
            {items.map((item) => (
                <FooterMenuListItemStyled key={item.uuid}>
                    <NextLink href={item.__typename === 'ArticleSite' ? item.slug : item.url} passHref>
                        <FooterMenuListItemLinkStyled
                            target={item.external ? '_blank' : undefined}
                            rel={item.external ? 'nofollow noreferrer noopener' : undefined}
                        >
                            {item.name}
                        </FooterMenuListItemLinkStyled>
                    </NextLink>
                </FooterMenuListItemStyled>
            ))}
        </FooterMenuListStyled>
    </FooterMenuItemStyled>
);
