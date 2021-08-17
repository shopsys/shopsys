import styled, { css } from 'styled-components';

export const ProductExposedStoreCountStyled = styled.div`
    ${({ theme }) => css`
        line-height: 18px;
        margin-bottom: 10px;

        font-size: 13px;
        color: ${theme.color.black};
    `}
`;
