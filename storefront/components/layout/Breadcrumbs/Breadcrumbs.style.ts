import { css } from 'styled-components';
import { styled } from 'theme/main';

export const BreadcrumbsStyled = styled.div`
    ${({ theme }) => css`
        margin-bottom: 36px;

        @media ${theme.mediaQueries.queryTablet} {
            margin: 0 -20px 36px;
            padding: 11px 20px 9px;

            border-bottom: 2px solid ${theme.color.greyLighter};
            font-size: 0;
        }
    `}
`;

export const BreadcrumbsLinkStyled = styled.a`
    ${({ theme }) => css`
        color: ${theme.color.primary};

        @media ${theme.mediaQueries.queryTablet} {
            display: none;
            margin-right: 11px;

            font-size: 13px;
            color: ${theme.color.greyLight};
            text-decoration: none;

            &: last-of-type {
                display: initial;
            }
        }
    `}
`;

export const BreadcrumbsSpanStyled = styled.a`
    ${({ theme }) => css`
        margin-right: 11px;

        color: ${theme.color.greyLight};
        font-size: 13px;

        @media ${theme.mediaQueries.queryTablet} {
            display: none;
        }
    `}
`;
