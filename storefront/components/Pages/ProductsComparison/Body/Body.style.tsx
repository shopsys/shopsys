import { localVariables as HeadItemVariables } from 'components/Pages/ProductsComparison/Head/Item/Item.style';
import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const BodyRowStyled = styled.tr(
    ({ theme }) => css`
        min-height: 40px;

        &:nth-child(even) {
            td {
                background-color: ${theme.color.white};
            }
        }
    `,
);

export const BodyItemStyled = styled.td(
    ({ theme }) => css`
        padding: 10px ${HeadItemVariables.itemPaddingSmall};
        line-height: 24px;
        width: ${HeadItemVariables.itemInWidthSmall};

        background-color: ${theme.color.greyVeryLight};
        box-shadow: inset -1px 0px 0px 0px ${theme.color.greyVeryLight};
        font-size: 13px;
        word-break: break-all;

        &.isTitle {
            color: ${theme.color.grey};
        }

        &.isSticky {
            position: sticky;
            left: 0;
            z-index: calc(${theme.zIndex.above} + 1);

            border-right: 0;
            box-shadow: inset -1px 0px 0px 0px ${theme.color.greyVeryLight};
        }

        @media ${theme.mediaQueries.querySm} {
            width: ${HeadItemVariables.itemInWidth};
            padding: 10px ${HeadItemVariables.itemPadding};

            font-size: 16px;
        }
    `,
);

export const BodyItemAvailabilityStyled = styled.div(
    ({ theme }) => css`
        word-break: break-word;
        font-size: 13px;
        font-weight: 700;

        &.in-stock {
            color: ${theme.color.inStock};

            &:hover {
                color: ${theme.color.inStock};
            }
        }

        &.out-of-stock {
            color: ${theme.color.red};

            &:hover {
                color: ${theme.color.red};
            }
        }

        &.to-delivery {
            color: ${theme.color.orange};

            &:hover {
                color: ${theme.color.orange};
            }
        }

        @media ${theme.mediaQueries.querySm} {
            font-size: 16px;
        }
    `,
);
