import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

type ProductActionStyledProps = {
    isButtonFullWidth: boolean;
};

export const ProductActionWrapperStyled = styled.div`
    padding: 0 9px 10px;
`;

export const ProductActionStyled = styled.div<ProductActionStyledProps>`
    ${({ theme, isButtonFullWidth }) => css`
        padding: 8px;
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

export const AddToCartUnavailableTextStyled = styled.p`
    padding: 5px;
    font-size: 16px;
`;
