import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

const localVariables = {
    footerMenuitemBorderColor: '#606476',
    footerMenuItemGap: '20px',
} as const;

export const FooterMenuItemStyled = styled.div(
    ({ theme }) => css`
        padding: 0 ${theme.layout.padding};

        @media ${theme.mediaQueries.queryLg} {
            width: 25%;
            padding-left: ${localVariables.footerMenuItemGap};
        }
    `,
);

export const FooterMenuListStyled = styled.ul(
    ({ theme }) => css`
        padding-bottom: 20px;

        @media ${theme.mediaQueries.queryLg} {
            padding-bottom: 0;
        }
    `,
);

export const FooterMenuListItemStyled = styled.li(
    ({ theme }) => css`
        margin-bottom: 5px;

        @media ${theme.mediaQueries.queryLg} {
            margin-bottom: 18px;
        }

        &:last-child {
            margin-bottom: 0;
        }
    `,
);

export const FooterMenuListItemLinkStyled = styled.a(
    ({ theme }) => css`
        display: block;

        font-size: ${theme.fontSize.small};
        color: ${theme.color.greyLight};
        text-decoration: none;

        &:hover {
            color: ${theme.color.greyLight};
        }
    `,
);
