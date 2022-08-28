import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

type CartPreviewCellStyledProps = {
    textAlign?: 'right';
};

export const CartPreviewStyled = styled.table`
    width: 100%;
`;

export const CartPreviewRowStyled = styled.tr`
    width: 100%;
`;

export const CartPreviewCellStyled = styled.td<CartPreviewCellStyledProps>(
    ({ theme, textAlign }) => css`
        line-height: 18px;
        padding: 6px 0;
        vertical-align: baseline;
        ${textAlign === 'right' && 'text-align: right;'}

        font-size: ${theme.fontSize.small};
    `,
);

export const CartPreviewCellBasicPrice = styled.strong(
    ({ theme }) => css`
        font-size: ${theme.fontSize.default};
    `,
);

export const CartPreviewCellTotalPrice = styled.strong(
    ({ theme }) => css`
        color: ${theme.color.primary};
        font-size: 24px;
    `,
);
