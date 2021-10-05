import { css } from 'styled-components';
import Icon from 'components/Basic/Icon';
import { styled } from 'components/Theme/main';

export const BreadcrumbsStyled = styled.div`
    ${({ theme }) => css`
        margin-bottom: 36px;
        display: flex;
        align-items: center;

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
        margin-right: 11px;

        color: ${theme.color.primary};

        @media ${theme.mediaQueries.queryTablet} {
            display: none;

            font-size: 13px;
            color: ${theme.color.greyLight};
            text-decoration: none;

            &:last-of-type {
                display: initial;
            }
        }
    `}
`;

export const BreadcrumbsSpanStyled = styled.span`
    ${({ theme }) => css`
        margin-right: 11px;

        color: ${theme.color.greyLight};
        font-size: 13px;

        @media ${theme.mediaQueries.queryTablet} {
            display: none;
        }
    `}
`;

export const LeftArrowIconStyled = styled(Icon)`
    ${({ theme }) => css`
        height: 12px;
        width: 12px;
        transform: rotate(90deg);
        margin-right: 10px;

        color: ${theme.color.greyLight};

        @media ${theme.mediaQueries.queryLg} {
            display: none;
        }
    `}
`;
