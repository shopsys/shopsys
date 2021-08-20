import styled, { css } from 'styled-components';
import { Theme } from 'theme/main';

type NavigationColumnCategoryProps = {
    isChildren?: boolean;
};

const localVariables = {
    navigationHeight: '64px',
    navigationSubListItemGap: '45px',
} as const;

export const NavigationItemSubListStyled = styled.ul<NavigationColumnCategoryProps>`
    ${({ isChildren }: NavigationColumnCategoryProps) => css`
        display: flex;
        flex-direction: column;
        width: calc(100% / 4);
        padding-left: ${localVariables.navigationSubListItemGap};

        ${isChildren &&
        css`
            padding-left: 0;
        `}
    `}
`;

export const NavigationItemSubListItemStyled = styled.li<NavigationColumnCategoryProps>`
    ${({ isChildren }: NavigationColumnCategoryProps) => css`
        width: 100%;
        margin-bottom: 35px;

        ${isChildren &&
        css`
            margin-bottom: 0;
        `}

        &:last-child {
            margin-bottom: 0;
        }
    `}
`;

export const NavigationItemSubListItemImageStyled = styled.a`
    ${({ theme }: { theme: Theme }) => css`
        display: flex;
        justify-content: center;
        height: 64px;
        margin-bottom: 13px;
        padding: 8px;

        border-radius: ${theme.radius.big};
        background-color: rgba(65, 67, 83, 0.05);
        font-size: 0;

        img {
            mix-blend-mode: multiply;
        }
    `}
`;

export const NavigationItemSubListItemLinkStyled = styled.a<NavigationColumnCategoryProps>`
    ${({ theme, isChildren }: { theme: Theme } & NavigationColumnCategoryProps) => css`
        display: block;
        margin-bottom: 4px;

        text-decoration: none;
        font-weight: 700;
        font-size: ${theme.fontSize.default};
        color: ${theme.color.base};

        ${isChildren &&
        css`
            margin-bottom: 5px;

            font-weight: 400;
            font-size: ${theme.fontSize.small};
        `}
    `}
`;
