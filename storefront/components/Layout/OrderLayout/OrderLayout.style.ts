import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

export const OrderLayoutStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        flex-direction: column;
        flex-wrap: wrap;
        margin-bottom: 90px;
        width: 100%;

        @media ${theme.mediaQueries.queryVl} {
            flex-direction: row;
            margin-top: 28px 0 60px;
        }
    `}
`;

export const OrderLayoutContentStyled = styled.div`
    ${({ theme }) => css`
        margin-bottom: 60px;
        width: 100%;

        @media ${theme.mediaQueries.queryVl} {
            flex: 1;
            margin-bottom: 0;
            padding-right: 40px;
        }
    `}
`;

export const OrderLayoutSummaryStyled = styled.div`
    ${({ theme }) => css`
        width: 100%;

        @media ${theme.mediaQueries.queryVl} {
            width: 420px;
        }
    `}
`;
