import styled, { css } from 'styled-components';
import { Theme } from 'theme/main';

const localVariables = {
    menuIconicMobileButtonSize: '40px',
};

export const MenuIconicListStyled = styled.ul`
    ${({ theme }: { theme: Theme }) => css`
        display: none;

        @media ${theme.mediaQueries.queryLg} {
            display: flex;
        }
    `}
`;

export const MenuIconicItemStyled = styled.li`
    ${({ theme }: { theme: Theme }) => css`
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

export const MenuIconicItemLinkStyled = styled.a`
    ${({ theme }: { theme: Theme }) => css`
        display: flex;
        flex-direction: row;
        justify-content: center;
        align-items: center;

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
    `}
`;

export const MenuIconicButtonMobileStyled = styled.div`
    ${({ theme }: { theme: Theme }) => css`
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

export const MenuIconicButtonMobileLinkStyled = styled.a`
    ${({ theme }: { theme: Theme }) => css`
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
