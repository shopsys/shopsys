import {
    FooterMenuHeadingStyled,
    FooterMenuItemStyled,
    FooterMenuListItemLinkStyled,
    FooterMenuListItemStyled,
    FooterMenuListStyled,
} from './FooterMenuItem.style';
import { SimpleArticleFragmentApi } from 'graphql/generated';
import NextLink from 'next/link';
import { FC } from 'react';

type FooterMenuItemProps = {
    title: string;
    items: SimpleArticleFragmentApi[];
};

const TEST_IDENTIFIER = 'layout-footer-footermenuitem';

export const FooterMenuItem: FC<FooterMenuItemProps> = ({ items, title }) => {
    return (
        <FooterMenuItemStyled data-testid={TEST_IDENTIFIER}>
            <FooterMenuHeadingStyled type="h4">{title}</FooterMenuHeadingStyled>
            <FooterMenuListStyled>
                {items.map((item) => (
                    <FooterMenuListItemStyled key={item.uuid}>
                        <NextLink href={item.slug} passHref>
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
};
