import { css } from 'styled-components';
import Icon from 'components/Basic/Icon';
import { styled } from 'components/Theme/main';

const localVariables = {
    menuIconicMobileButtonSize: '40px',
} as const;

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

export const MenuIconicItemLinkStyled = styled.a`
    ${({ theme }) => css`
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

export const MenuIconicItemIconStyled = styled(Icon)`
    ${({ theme }) => css`
        width: 18px;
        height: 18px;
        margin-right: 10px;

        color: ${theme.color.white};
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

export const MenuIconicButtonMobileLinkStyled = styled.a`
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
