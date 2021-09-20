import styled, { css } from 'styled-components';
import { Availability } from 'components/Pages/ProductDetail/types';

type ProductDetailAvailabilityLinkStyledProps = Pick<Availability, 'status'>;

export const ProductDetailAvailabilityStyled = styled.div`
    ${({ theme }) => css`
        padding: 14px 10px;

        background-color: ${theme.color.blueLight};
        border-radius: 6px;
    `}
`;

export const ProductDetailAvailabilityLinkStyled = styled.a<ProductDetailAvailabilityLinkStyledProps>`
    ${({ theme, status }) => css`
        align-items: center;
        display: flex;
        margin-bottom: 1.8px;

        font-weight: 700;
        font-size: ${theme.fontSize.default};
        text-decoration: none;

        ${status === 'in-stock' &&
        css`
            color: ${theme.color.inStock};

            &:hover {
                color: ${theme.color.inStock};
            }
        `}

        ${status === 'out-of-stock' &&
        css`
            color: ${theme.color.red};

            &:hover {
                color: ${theme.color.red};
            }
        `}

        &:hover {
            text-decoration: none;
        }

        img {
            margin-left: 8px;
        }
    `}
`;

export const ProductDetailAvailabilityInfoStyled = styled.span`
    ${({ theme }) => css`
        margin-right: 3px;

        font-size: ${theme.fontSize.small};
    `}
`;
