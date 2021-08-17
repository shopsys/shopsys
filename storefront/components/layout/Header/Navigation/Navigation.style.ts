import styled, { css } from 'styled-components';
import { Theme } from 'theme/main';

const localVariables = {
    navigationItemLinkHorizontalGap: '10px',
} as const;

export const NavigationStyled = styled.ul`
    ${({ theme }: { theme: Theme }) => css`
        display: none;
        width: 100%;

        @media ${theme.mediaQueries.queryLg} {
            display: block;
            width: calc(100% + (${localVariables.navigationItemLinkHorizontalGap} * 2));
            position: relative;
            margin-left: -${localVariables.navigationItemLinkHorizontalGap};
        }
    `}
`;
