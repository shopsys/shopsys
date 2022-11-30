import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

const localVariables = {
    orderSummaryWrapperWidth: '420px',
    marginBottom: '20px',
    totalPriceFontSize: '24px',
} as const;

export const OrderSummaryWrapperStyled = styled.div(
    ({ theme }) => css`
        width: 100%;

        @media ${theme.mediaQueries.queryVl} {
            width: ${localVariables.orderSummaryWrapperWidth};
        }
    `,
);

export const OrderSummaryTitleStyled = styled.h3(
    ({ theme }) => css`
        margin-bottom: 11px;

        font-size: ${theme.fontSize.default};
        font-weight: 700;

        @media ${theme.mediaQueries.queryLg} {
            font-size: ${theme.fontSize.bigger};
        }
    `,
);

export const OrderSummaryContentWrapperStyled = styled.div(
    ({ theme }) => css`
        margin: 0 -${localVariables.marginBottom};
        padding: 10px 20px;

        background-color: ${theme.color.greyVeryLight};

        @media ${theme.mediaQueries.queryVl} {
            margin: 0;

            border-radius: ${theme.radius.big};
        }
    `,
);

export const OrderSummaryContentStyled = styled.div`
    position: relative;
    display: flex;
    flex-direction: column;
`;

export const TransportAndPaymentPreviewWrapperStyled = styled.div`
    position: relative;
`;

export const ProductsPreviewStyled = styled.div`
    margin-bottom: ${localVariables.marginBottom};
`;

export const OrderSummaryListStyled = styled.ul`
    margin: 0;
    padding: 0;

    list-style: none;
`;

export const ListItemStyled = styled.li(
    ({ theme }) => css`
        display: flex;
        align-items: center;
        padding: 10px 0;

        border-bottom: 1px solid ${theme.color.creamWhite};
    `,
);

export const ListItemPictureWrapperStyled = styled.div`
    margin-right: 17px;
    width: 54px;
`;

export const ListItemInfoWrapperStyled = styled.div`
    display: flex;
    flex-direction: row;
    flex: 1;
    align-items: center;
`;

export const ListItemInfoStyled = styled.span(
    ({ theme }) => css`
        padding-right: 10px;
        flex: 1;

        font-size: ${theme.fontSize.small};
    `,
);

export const ListItemPriceStyled = styled.strong(
    ({ theme }) => css`
        margin-left: auto;
        width: 95px;
        text-align: right;

        font-size: ${theme.fontSize.small};
    `,
);

export const OrderSummaryRowWrapperStyled = styled.div(
    ({ theme }) => css`
        padding-bottom: 10px;
        margin-bottom: ${localVariables.marginBottom};

        border-bottom: 1px solid ${theme.color.creamWhite};
    `,
);

export const OrderSummaryRowContentStyled = styled.div`
    display: table;
    width: 100%;
`;

export const OrderSummaryRowStyled = styled.div`
    display: flex;
    justify-content: space-between;
`;

export const PriceWrapperStyled = styled.div`
    display: flex;
    justify-content: flex-end;
`;

export const OrderSummaryTextAndImageStyled = styled.div(
    ({ theme }) => css`
        display: table-row;
        padding: 6px 0;
        vertical-align: baseline;
        line-height: ${theme.lineHeight.default};

        font-size: ${theme.fontSize.small};
    `,
);

export const OrderSummaryPriceStyled = styled.div(
    ({ theme }) => css`
        padding: 6px 0;
        vertical-align: baseline;
        line-height: ${theme.lineHeight.default};
        text-align: right;

        font-size: ${theme.fontSize.small};
    `,
);

export const TransportAndPaymentImageWrapperStyled = styled.span`
    display: inline-block;
    height: 18px;
    margin-left: 8px;
    vertical-align: bottom;

    > img {
        width: 35px;
    }
`;

export const OrderSummaryTotalPriceWrapperStyled = styled.div`
    margin-bottom: ${localVariables.marginBottom};
`;

export const OrderSummaryTotalPriceTextStyled = styled.span`
    margin-right: 15px;
    display: inline-flex;
    align-items: end;
`;

export const OrderSummaryTotalPriceAmountStyled = styled.strong(
    ({ theme }) => css`
        color: ${theme.color.primary};

        font-size: ${localVariables.totalPriceFontSize};
    `,
);
