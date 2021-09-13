import { css } from 'styled-components';
import { styled } from '../../../Theme/main';

type ProductActionStyledProps = {
    isButtonFullWidth: boolean;
};

export const ProductActionWrapperStyled = styled.div`
    padding: 0 9px 10px;
`;

export const ProductActionStyled = styled.div<ProductActionStyledProps>`
    ${({ theme, isButtonFullWidth }) => css`
        padding: 10px;
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        justify-content: space-between;

        background-color: ${theme.color.greyVeryLight};
        border-radius: ${theme.radius.big};

        ${isButtonFullWidth &&
        css`
            button {
                width: 100%;
            }
        `}
    `}
`;
