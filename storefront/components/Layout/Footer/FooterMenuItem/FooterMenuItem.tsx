import {
    FooterMenuHeadingStyled,
    FooterMenuItemStyled,
    FooterMenuListItemLinkStyled,
    FooterMenuListItemStyled,
    FooterMenuListStyled,
} from './FooterMenuItem.style';
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
        <FooterMenuHeadingStyled type="h4">{title}</FooterMenuHeadingStyled>
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
