import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

export const CartBottomStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        flex-direction: column;
        margin-bottom: 30px;

        @media ${theme.mediaQueries.queryVl} {
            flex-direction: row;
        }
    `}
`;

export const CartBottomLeftStyled = styled.div`
    ${({ theme }) => css`
        padding-right: 30px;

        @media ${theme.mediaQueries.queryVl} {
            padding-right: 15px;
        }
    `}
`;

export const CartBottomRightStyled = styled.div`
    ${({ theme }) => css`
        @media ${theme.mediaQueries.queryVl} {
            width: 300px;
            margin-left: auto;
        }
    `}
`;
