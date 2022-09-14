import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const ProductPriceStyled = styled.div`
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    align-items: center;
`;

export const ProductPriceMainStyled = styled.div(
    ({ theme }) => css`
        order: 2;
        margin-right: 10px;
        line-height: 22px;

        font-weight: 700;
        font-size: 18px;
        color: ${theme.color.primary};
    `,
);
