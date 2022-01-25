import styled, { css } from 'styled-components';
import { StoreAvailabilityType } from 'types/availability';

type ProductDetailAvailabilityListItemStatusStyledProps = Pick<StoreAvailabilityType, 'availabilityStatus'>;

export const ProductDetailAvailabilityListWrapperStyled = styled.div`
    ${({ theme }) => css`
        display: block;
        width: 588px;

        @media ${theme.mediaQueries.queryNotLargeDesktop} {
            width: 100%;
        }
    `}
`;

export const ProductDetailAvailabilityListItemStyled = styled.li`
    ${({ theme }) => css`
        width: 100%;
        display: flex;
        padding: 14px 0;
        align-items: center;

        border-bottom: 1px solid ${theme.color.greyLighter};
    `}
`;

export const ProductDetailAvailabilityListItemStoreNameStyled = styled.strong`
    margin-right: 10px;
    width: 148px;
`;

export const ProductDetailAvailabilityListItemStatusStyled = styled.span<ProductDetailAvailabilityListItemStatusStyledProps>`
    ${({ theme, availabilityStatus }) => css`
        flex: 1;
        padding-right: 10px;

        font-size: ${theme.fontSize.small};

        ${availabilityStatus === 'in-stock' &&
        css`
            color: ${theme.color.inStock};
        `}

        ${availabilityStatus === 'out-of-stock' &&
        css`
            color: ${theme.color.red};
        `}
    `}
`;

export const ProductDetailAvailabilityListItemStoreLinkStyled = styled.a`
    ${({ theme }) => css`
        align-items: center;
        display: flex;
        margin-left: auto;

        color: ${theme.color.base};
        text-decoration: none;

        &:hover {
            text-decoration: none;
            color: initial;
        }

        img {
            margin-left: 8px;
        }
    `}
`;
