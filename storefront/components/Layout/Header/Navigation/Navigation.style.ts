import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

const localVariables = {
    navigationItemLinkHorizontalGap: '10px',
} as const;

export const NavigationStyled = styled.ul(
    ({ theme }) => css`
        display: none;
        width: 100%;

        @media ${theme.mediaQueries.queryLg} {
            display: block;
            width: calc(100% + (${localVariables.navigationItemLinkHorizontalGap} * 2));
            position: relative;
            margin-left: -${localVariables.navigationItemLinkHorizontalGap};
        }
    `,
);
