import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

type OrderStepsListItemLinkStyledProps = {
    isActive: boolean;
    cursor?: 'pointer';
};

export const OrderStepsListStyled = styled.ul`
    ${({ theme }) => css`
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        margin: 0 -20px 26px;
        padding: 0;

        border-bottom: 1px solid ${theme.color.greyLighter};
        list-style: none;

        @media ${theme.mediaQueries.queryLg} {
            margin: 0 0 12px;
        }
    `}
`;

export const OrderStepsListItemStyled = styled.li`
    ${({ theme }) => css`
        padding: 12px;
        position: relative;
        width: calc(100% / 3);

        @media ${theme.mediaQueries.queryLg} {
            padding: 12px 20px;
        }
    `}
`;

export const OrderStepsListItemLinkStyled = styled.span<OrderStepsListItemLinkStyledProps>`
    ${({ theme, isActive, cursor }) => css`
        display: block;

        line-height: 14px;
        text-decoration: none;
        text-transform: uppercase;
        font-size: ${theme.fontSize.extraSmall};
        cursor: default;

        ${cursor === 'pointer' &&
        css`
            cursor: pointer;

            &:hover {
                outline-width: 0;
                color: ${theme.color.primary};
                text-decoration: none;
            }
        `}

        ${isActive &&
        css`
            color: ${theme.color.primary};

            &::before {
                content: '';
                bottom: -1px;
                height: 2px;
                left: 0;
                position: absolute;
                right: 0;

                background-color: ${theme.color.primary};
            }
        `}
    `}
`;
