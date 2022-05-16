import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const CartSummaryStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        flex-direction: column;
        margin-bottom: 30px;

        @media ${theme.mediaQueries.queryVl} {
            flex-direction: row;
        }
    `}
`;

export const CartSummaryLeftStyled = styled.div`
    ${({ theme }) => css`
        padding-right: 30px;

        @media ${theme.mediaQueries.queryVl} {
            padding-right: 15px;
        }
    `}
`;

export const CartSummaryMiddleStyled = styled.div`
    ${({ theme }) => css`
        text-align: center;
        margin-left: auto;

        @media ${theme.mediaQueries.queryVl} {
            padding-right: 30px;
        }
    `}
`;

export const CartSummaryRightStyled = styled.div`
    ${({ theme }) => css`
        @media ${theme.mediaQueries.queryVl} {
            width: 300px;
        }
    `}
`;
