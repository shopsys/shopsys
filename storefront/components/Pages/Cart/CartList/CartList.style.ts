import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const ListStyled = styled.ul(
    ({ theme }) => css`
        margin-bottom: 24px;
        position: relative;

        border: 1px solid ${theme.color.greyLighter};
        border-bottom: 0;

        @media ${theme.mediaQueries.queryLg} {
            margin-bottom: 30px;

            border: 0;
        }
    `,
);
