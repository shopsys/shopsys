import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

const localVariables = {
    navigationHeight: '64px',
    navigationSubListItemGap: '45px',
} as const;

export const NavigationItemSubListStyled = styled.ul`
    display: flex;
    flex-direction: column;
    width: calc(100% / 4);
    padding-left: ${localVariables.navigationSubListItemGap};
`;

export const NavigationColumnCategoryStyled = styled.li`
    width: 100%;
    margin-bottom: 35px;

    &:last-child {
        margin-bottom: 0;
    }
`;

export const NavigationColumnCategoryImageStyled = styled.a`
    ${({ theme }) => css`
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

export const NavigationColumnCategoryLinkStyled = styled.a`
    ${({ theme }) => css`
        display: block;
        margin-bottom: 4px;

        text-decoration: none;
        font-weight: 700;
        font-size: ${theme.fontSize.default};
        color: ${theme.color.base};
    `}
`;
