import { css } from 'styled-components';
import Icon from 'components/Basic/Icon';
import { styled } from 'components/Theme/main';

const localVariables = {
    menuIconicMobileButtonSize: '40px',
    menuIconicItemPadding: '10px',
} as const;

type HasSubmenuProps = {
    hasSubmenu?: boolean;
};

export const MenuIconicListStyled = styled.ul`
    ${({ theme }) => css`
        display: none;

        @media ${theme.mediaQueries.queryLg} {
            display: flex;
        }
    `}
`;

export const MenuIconicItemStyled = styled.li`
    ${({ theme }) => css`
        display: flex;
        position: relative;
        margin-right: 20px;

        @media ${theme.mediaQueries.queryXl} {
            margin-right: 32px;
        }

        &:last-child {
            margin-right: 0;
        }
    `}
`;

export const MenuIconicItemLinkStyled = styled.a<HasSubmenuProps>`
    ${({ theme, hasSubmenu }) => css`
        display: flex;
        flex-direction: row;
        justify-content: center;
        align-items: center;

        border-radius: ${theme.radius.big} 0 ${theme.radius.big} ${theme.radius.big};
        font-size: ${theme.fontSize.small};
        color: ${theme.color.white};
        text-decoration: none;
        transition: ${theme.transition};

        &:hover {
            color: ${theme.color.white};
            text-decoration: underline;
        }

        img {
            font-size: 18px;
            margin-right: 10px;
        }

        ${hasSubmenu &&
        css`
            padding: 0 10px;

            &:hover {
                background-color: ${theme.color.white};
                color: ${theme.color.base};
                border-radius: ${theme.radius.big} ${theme.radius.big} 0 0;

                i {
                    color: ${theme.color.base};
                }

                ul {
                    opacity: 1;
                    transform: scale(1);
                    pointer-events: auto;
                }
            }
        `}
    `}
`;

export const MenuIconicItemIconStyled = styled(Icon)`
    ${({ theme }) => css`
        width: 18px;
        height: 18px;
        margin-right: 10px;

        color: ${theme.color.white};
    `}
`;

export const MenuIconicSubStyled = styled.ul`
    ${({ theme }) => css`
        display: block;
        position: absolute;
        top: 100%;
        right: 0;
        min-width: 150px;

        background: ${theme.color.white};
        z-index: ${theme.zIndex.cart};
        border-radius: ${theme.radius.big} 0 ${theme.radius.big} ${theme.radius.big};
        box-shadow: 0 20px 20px 0 rgba(0, 0, 0, 0.15);
        transition: all 0.2s ease-in-out;
        transform-origin: right top;
        opacity: 0;
        transform: scaleY(0.5) scaleX(0.7);
        will-change: transform;
        pointer-events: none;
    `}
`;

export const MenuIconicSubItemStyled = styled.li`
    ${({ theme }) => css`
        display: block;

        border-top: 1px solid ${theme.color.border};

        &:first-child {
            border-top: 0;
        }
    `}
`;

export const MenuIconicSubItemLinkStyled = styled.a`
    ${({ theme }) => css`
        display: block;
        padding: 12px 20px;

        color: ${theme.color.base};
        text-decoration: none;
        font-size: ${theme.fontSize.small};
    `}
`;

export const MenuIconicButtonMobileStyled = styled.div`
    ${({ theme }) => css`
        display: none;

        @media ${theme.mediaQueries.queryTablet} {
            display: flex;
            order: 2;
            align-items: center;
            justify-content: center;
            width: ${localVariables.menuIconicMobileButtonSize};
            height: ${localVariables.menuIconicMobileButtonSize};
            margin-left: 4px;

            outline: 0;
            cursor: pointer;
            font-size: 18px;
        }
    `}
`;

export const MenuIconicButtonMobileLinkStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        transition: ${theme.transition} color;
        width: 100%;
        height: 100%;

        color: ${theme.color.white};
        text-decoration: none;

        &:hover {
            text-decoration: none;
            color: ${theme.color.white};
        }
    `}
`;
