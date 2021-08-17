import styled, { css } from 'styled-components';

export const ProductActionStyled = styled.div`
    ${() => css`
        padding: 20px 20px 19px 20px;

        form {
            display: flex;
            flex-direction: row;
            flex-wrap: nowrap;
            justify-content: space-between;
        }
    `}
`;
