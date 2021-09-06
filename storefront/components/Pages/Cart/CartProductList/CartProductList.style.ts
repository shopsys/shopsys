import { css } from 'styled-components';
import { styled } from 'theme/main';

export const StyledCartProductList = styled.ul`
    ${({ theme }) => css`
        @media ${theme.mediaQueries.queryTablet} {
            border: 1px solid ${theme.color.greyLighter};
        }
    `}
`;
