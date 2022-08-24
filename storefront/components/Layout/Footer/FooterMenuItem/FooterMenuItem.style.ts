import { Heading } from 'components/Basic/Heading/Heading';
import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

const localVariables = {
    footerMenuitemBorderColor: '#606476',
    footerMenuItemGap: '20px',
};

export const FooterMenuItemStyled = styled.div(
    ({ theme }) => css`
        padding: 0 ${theme.layout.padding};

        @media ${theme.mediaQueries.queryLg} {
            width: 25%;
            padding-left: ${localVariables.footerMenuItemGap};
        }
    `,
);

export const FooterMenuHeadingStyled = styled(Heading)(
    ({ theme }) => css`
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0;
        padding: 20px 0;

        color: ${theme.color.white};
        font-weight: 700;
        text-transform: uppercase;

        @media ${theme.mediaQueries.queryLg} {
            padding: 0;
            margin-bottom: 24px;
            pointer-events: none;
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
