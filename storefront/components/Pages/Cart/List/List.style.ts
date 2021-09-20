import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

export const ListStyled = styled.ul`
    ${({ theme }) => css`
        margin-bottom: 24px;

        border: 1px solid ${theme.color.greyLighter};
        border-bottom: 0;

        @media ${theme.mediaQueries.queryLg} {
            margin-bottom: 30px;

            border: 0;
        }
    `}
`;
