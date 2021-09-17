import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

export const ListStyled = styled.ul`
    ${({ theme }) => css`
        @media ${theme.mediaQueries.queryTablet} {
            border: 1px solid ${theme.color.greyLighter};
        }
    `}
`;
