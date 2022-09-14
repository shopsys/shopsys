import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const FooterStyled = styled.div`
    position: relative;
    margin-top: auto;
`;

export const FooterBottomStyled = styled.div(
    ({ theme }) => css`
        display: flex;
        flex-direction: column;
        padding: 20px 0 42px;

        @media ${theme.mediaQueries.queryLg} {
            padding: 45px 0;
        }
    `,
);

export const FooterBlockStyled = styled.div(
    ({ theme }) => css`
        margin-bottom: 50px;

        @media ${theme.mediaQueries.queryVl} {
            display: flex;
            margin-bottom: 95px;
        }
    `,
);

export const CookieConsentLinkStyled = styled.a(
    ({ theme }) => css`
        align-self: center;

        color: ${theme.color.greyLight};
        text-decoration: none;
        transition: ${theme.transition};

        &:hover {
            color: ${theme.color.whitesmoke};
            text-decoration: none;
        }
    `,
);
