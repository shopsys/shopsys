import { css } from 'styled-components';
import Icon from 'components/Basic/Icon';
import { styled } from 'components/Theme/main';

export const PromoCodeInfoStyled = styled.div`
    ${({ theme }) => css`
        font-size: ${theme.fontSize.default};
    `}
`;

export const PromoCodeInfoTitleStyled = styled.div`
    ${({ theme }) => css`
        color: ${theme.color.primary};
    `}
`;

export const PromoCodeInfoCouponStyled = styled.div`
    display: flex;
    align-items: center;

    font-weight: 700;
`;

export const PromoCodeInfoCouponIconStyled = styled(Icon)`
    ${({ theme }) => css`
        width: 16px;
        height: 16px;
        margin-left: 5px;

        color: ${theme.color.greyDark};
        cursor: pointer;

        &:hover {
            color: ${theme.color.primary};
        }
    `}
`;
