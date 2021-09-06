import { css } from 'styled-components';
import { styled } from 'theme/main';

type CartProductItemPriceStyledProps = {
    isInSale: boolean;
};

type CartProductTotalPriceStyledProps = {
    isInSale: boolean;
};

export const CartProductListItemStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        position: relative;
        padding: 20px 0;

        border-bottom: 1px solid ${theme.color.greyLighter};

        @media ${theme.mediaQueries.queryTablet} {
            padding: 11px;
        }
    `}
`;

export const CartProductImageCellStyled = styled.div`
    ${({ theme }) => css`
        width: 93px;
        align-items: center;
        display: flex;
        padding-right: 15px;

        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            margin-bottom: 26px;
        }
    `}
`;

export const CartProductInfoCellStyled = styled.div`
    ${({ theme }) => css`
        text-align: center;
        width: calc(100% - 93px);
        align-items: center;
        display: flex;
        padding-right: 15px;

        font-size: 13px;
        font-weight: 700;

        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            align-items: flex-start;
            flex-direction: column;
            margin-bottom: 20px;
            padding-right: 30px;
        }

        @media ${theme.mediaQueries.queryVl} {
            flex: 1;
        }
    `}
`;

export const CartProductSpinboxCellStyled = styled.div`
    ${({ theme }) => css`
        padding-right: 15px;
        width: 150px;
        align-items: center;
        display: flex;

        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            padding-right: 0;
            width: 120px;
        }
    `}
`;

export const CartProductItemPriceCellStyled = styled.div`
    ${({ theme }) => css`
        margin-left: 0;
        padding-right: 15px;
        align-items: center;
        display: flex;

        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            margin-left: auto;
            padding-right: 0;
        }

        @media ${theme.mediaQueries.queryVl} {
            width: 136px;
        }
    `}
`;

export const CartProductItemPriceStyled = styled.span<CartProductItemPriceStyledProps>`
    ${({ theme, isInSale }) => css`
        font-size: ${theme.fontSize.small};

        ${isInSale &&
        css`
            color: ${theme.color.primary};
        `}
    `}
`;

export const CartProductTotalPriceCellStyled = styled.div`
    ${({ theme }) => css`
        margin-left: 0;
        padding-right: 15px;
        justify-content: flex-end;
        width: 136px;
        align-items: center;
        display: flex;

        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            justify-content: flex-end;
            margin-left: auto;
            padding-right: 0;
        }
    `}
`;

export const CartProductTotalPriceStyled = styled.span<CartProductTotalPriceStyledProps>`
    ${({ theme, isInSale }) => css`
        color: ${theme.color.primary};

        ${isInSale &&
        css`
            font-weight: 700;
        `}
    `}
`;

export const CartProductRemoveButtonCellStyled = styled.div`
    ${({ theme }) => css`
        position: static;
        align-items: center;
        display: flex;

        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            right: 0;
            top: 15px;
            padding-right: 0;
            position: absolute;
        }

        @media ${theme.mediaQueries.queryTablet} {
            right: 11px;
            top: 11px;
        }
    `}
`;

export const CartProductRemoveButtonStyled = styled.button`
    ${({ theme }) => css`
        align-items: center;
        display: flex;
        height: 20px;
        justify-content: center;
        transition: all ${theme.transition};
        width: 20px;

        background-color: ${theme.color.whitesmoke};
        border-radius: 50%;
        cursor: pointer;
        outline: none;
        border: none;

        &:hover {
            background-color: #e3e3ff;
        }
    `}
`;
