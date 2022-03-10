import { css } from 'styled-components';
import Icon from 'components/Basic/Icon';
import { styled } from 'components/Theme/main';

type NavigationStyledProps = {
    isOpen?: boolean;
};

const localVariables = {
    navigationHeight: '64px',
    navigationSubListItemGap: '45px',
} as const;

export const NavigationItemStyled = styled.li<NavigationStyledProps>`
    ${({ theme }) => css`
        padding: 0;
        display: inline-block;
        vertical-align: middle;
        height: ${localVariables.navigationHeight};

        @media ${theme.mediaQueries.queryLg} {
            margin-right: 25px;
        }

        @media ${theme.mediaQueries.queryXl} {
            margin-right: 50px;
        }

        &:last-child {
            margin-right: 0;
        }
    `}
`;

export const NavigationItemLinkStyled = styled.a<NavigationStyledProps>`
    ${({ theme, isOpen }) => css`
        position: relative;
        display: block;
        padding: 0 10px;
        margin: 0;
        height: ${localVariables.navigationHeight};
        line-height: ${localVariables.navigationHeight};

        font-size: ${theme.fontSize.small};
        font-weight: 700;
        color: ${theme.color.white};
        text-transform: uppercase;
        text-decoration: none;
        ${isOpen &&
        css`
            color: ${theme.color.orangeLight};
            text-decoration: none;
        `};

        @media ${theme.mediaQueries.queryVl} {
            font-size: ${theme.fontSize.default};
        }

        &:hover {
            color: ${theme.color.orangeLight};
            text-decoration: none;

            &:after {
                display: block;
            }
        }

        &:after {
            content: '';
            display: none;
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            ${isOpen &&
            css`
                display: block;
            `};

            background-color: ${theme.color.orange};
        }
    `}
`;

export const NavigationItemLinkIconStyled = styled(Icon)<NavigationStyledProps>`
    ${({ theme, isOpen }) => css`
        margin-left: 8px;

        color: ${theme.color.white};

        ${isOpen &&
        css`
            transform: rotate(180deg);
            color: ${theme.color.orangeLight};
        `};
    `};
`;

export const NavigationItemSubStyled = styled.div<NavigationStyledProps>`
    ${({ theme, isOpen }) => css`
        display: block;
        position: absolute;
        left: 0;
        right: 0;
        z-index: ${theme.zIndex.menu};
        top: ${localVariables.navigationHeight};
        padding: 50px 60px 45px;

        background: ${theme.color.white};
        box-shadow: 0 5px 10px 0 rgba(164, 167, 193, 0.34);
        opacity: 0;
        pointer-events: none;

        ${isOpen &&
        css`
            opacity: 1;
            pointer-events: auto;
        `};
    `}
`;

export const NavigationItemSubWrapStyled = styled.div`
    display: flex;
    flex-direction: row;
    margin-left: -${localVariables.navigationSubListItemGap};
`;
