import { css } from 'styled-components';
import Heading from 'components/Basic/Heading';
import Icon from 'components/Basic/Icon';
import { styled } from 'components/Theme/main';

type FooterMenuItemStyledProps = {
    contentElementHeight: number;
};

type FooterMenuHeadingStyledProps = {
    isContentVisible: boolean;
};

const localVariables = {
    footerMenuitemBorderColor: '#606476',
    footerMenuItemGap: '20px',
};

export const FooterMenuItemStyled = styled.div<FooterMenuItemStyledProps>`
    ${({ theme, contentElementHeight }) => css`
        padding: 0 ${theme.layout.padding};

        border-top: 2px solid ${localVariables.footerMenuitemBorderColor};

        &:last-child {
            border-bottom: 2px solid ${localVariables.footerMenuitemBorderColor};

            @media ${theme.mediaQueries.queryLg} {
                border-bottom: 0;
            }
        }

        @media ${theme.mediaQueries.queryLg} {
            width: 25%;
            padding-left: ${localVariables.footerMenuItemGap};

            border-top: 0;
        }

        @media ${theme.mediaQueries.queryTablet} {
            .footer-menu-item-enter {
                height: 0;
                overflow: hidden;
            }

            .footer-menu-item-enter-active {
                height: ${contentElementHeight}px;
                transition: 0.3s all ease;
            }

            .footer-menu-item-exit {
                height: ${contentElementHeight}px;
            }

            .footer-menu-item-exit-active {
                height: 0;
                overflow: hidden;
                transition: 0.3s all ease;
            }
        }
    `}
`;

export const FooterMenuHeadingStyled = styled(Heading)<FooterMenuHeadingStyledProps>`
    ${({ theme, isContentVisible }) => css`
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0;
        padding: 20px 0;

        cursor: pointer;
        color: ${theme.color.white};
        font-weight: 700;
        text-transform: uppercase;

        @media ${theme.mediaQueries.queryLg} {
            padding: 0;
            margin-bottom: 24px;
            pointer-events: none;
        }

        ${isContentVisible &&
        css`
            ${FooterMenuHeadingIconStyled} {
                transform: rotate(180deg);
            }
        `}
    `}
`;

export const FooterMenuHeadingIconStyled = styled(Icon)`
    ${({ theme }) => css`
        width: 20px;
        height: 20px;

        color: ${theme.color.creamWhite};

        @media ${theme.mediaQueries.queryLg} {
            display: none;
        }
    `}
`;

export const FooterMenuListStyled = styled.ul`
    ${({ theme }) => css`
        padding-bottom: 20px;

        @media ${theme.mediaQueries.queryLg} {
            padding-bottom: 0;
        }
    `}
`;

export const FooterMenuListItemStyled = styled.li`
    ${({ theme }) => css`
        margin-bottom: 5px;

        @media ${theme.mediaQueries.queryLg} {
            margin-bottom: 18px;
        }

        &:last-child {
            margin-bottom: 0;
        }
    `}
`;

export const FooterMenuListItemLinkStyled = styled.a`
    ${({ theme }) => css`
        display: block;

        font-size: ${theme.fontSize.small};
        color: ${theme.color.greyLight};
        text-decoration: none;

        &:hover {
            color: ${theme.color.greyLight};
        }
    `}
`;
