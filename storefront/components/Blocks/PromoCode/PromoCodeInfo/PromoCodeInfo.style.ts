import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const PromoCodeInfoStyled = styled.div(
    ({ theme }) => css`
        font-size: ${theme.fontSize.default};
    `,
);

export const PromoCodeInfoTitleStyled = styled.div(
    ({ theme }) => css`
        color: ${theme.color.primary};
    `,
);

export const PromoCodeInfoCouponStyled = styled.div`
    display: flex;
    align-items: center;

    font-weight: 700;
`;
