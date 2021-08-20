import styled, { css } from 'styled-components';
import { Theme } from 'theme/main';

type NavigationProps = {
    isOpen?: boolean;
};

const localVariables = {
    navigationHeight: '64px',
    navigationSubListItemGap: '45px',
} as const;

const hoveredItem = (theme: Theme) => {
    return css`
        ${NavigationItemLinkStyled} {
            color: ${theme.color.orangeLight};
            text-decoration: none;

            &:after {
                display: block;
            }
        }

        ${NavigationItemLinkIconStyled} {
            img {
                transform: rotate(180deg);
            }
        }

        ${NavigationItemSubStyled} {
            opacity: 1;
            pointer-events: auto;
        }
    `;
};

export const NavigationItemStyled = styled.li<NavigationProps>`
    ${({ theme, isOpen }: { theme: Theme } & NavigationProps) => css`
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

        ${isOpen
            ? hoveredItem(theme)
            : css`
                  &:hover {
                      ${hoveredItem(theme)};
                  }
              `};

        &:last-child {
            margin-right: 0;
        }
    `}
`;

export const NavigationItemLinkStyled = styled.a`
    ${({ theme }: { theme: Theme }) => css`
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

        @media ${theme.mediaQueries.queryVl} {
            font-size: ${theme.fontSize.default};
        }

        &:after {
            content: '';
            display: none;
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;

            background-color: ${theme.color.orange};
        }
    `}
`;

export const NavigationItemLinkIconStyled = styled.span`
    margin-left: 8px;
`;

export const NavigationItemSubStyled = styled.div`
    ${({ theme }: { theme: Theme }) => css`
        display: block;
        position: absolute;
        width: 100%;
        z-index: ${theme.zIndex.menu};
        top: ${localVariables.navigationHeight};
        padding: 50px 60px 45px;

        background: ${theme.color.white};
        box-shadow: 0 5px 10px 0 rgba(164, 167, 193, 0.34);
        opacity: 0;
        pointer-events: none;
    `}
`;

export const NavigationItemSubWrapStyled = styled.div`
    display: flex;
    flex-direction: row;
    margin-left: -${localVariables.navigationSubListItemGap};
`;
