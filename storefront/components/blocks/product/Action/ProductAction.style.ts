import styled, { css } from 'styled-components';

type ProductActionStyledProps = {
    isButtonFullWidth: boolean;
};

export const ProductActionStyled = styled.div<ProductActionStyledProps>`
    ${({ isButtonFullWidth }: ProductActionStyledProps) => css`
        padding: 20px 20px 19px 20px;

        /* TODO KOD: check later if this could be styled component*/
        form {
            display: flex;
            flex-direction: row;
            flex-wrap: nowrap;
            justify-content: space-between;
        }

        ${isButtonFullWidth &&
        css`
            button {
                width: 100%;
            }
        `}
    `}
`;
