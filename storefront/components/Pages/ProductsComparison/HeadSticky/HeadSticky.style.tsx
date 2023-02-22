import { localVariables as headVariables } from 'components/Pages/ProductsComparison//Head/Head.style';
import { localVariables as headItemVariables } from 'components/Pages/ProductsComparison//Head/Item/Item.style';
import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const HeadStickyStyled = styled.div(
    ({ theme }) => css`
        display: none;
        position: fixed;
        width: 100%;
        top: 0;
        left: 0;
        overflow: hidden;
        min-height: 72px;
        z-index: 3001;
        padding: 0 ${theme.layout.padding};

        &.isActive {
            display: flex;
        }
    `,
);

export const HeadStickyInStyled = styled.div(
    ({ theme }) => css`
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        width: calc(${theme.layout.width} - ${theme.layout.padding} * 2);
        height: 72px;
        margin: 0 auto;
        overflow: hidden;

        border-bottom: 3px solid ${theme.color.greyVeryLight};
        border-top: none;
        background-color: ${theme.color.white};

        @media ${theme.mediaQueries.queryXl} {
            width: calc(${theme.layout.width} - ${theme.layout.padding} * 2);
        }
    `,
);

export const HeadStickyEmptyStyled = styled.div(
    ({ theme }) => css`
        display: flex;
        position: sticky;
        left: 0;
        top: 0;
        min-width: ${headVariables.tableCompareLeftWidthMobileSmall};
        max-width: ${headItemVariables.itemInWidthSmall};
        width: 100%;

        background-color: ${theme.color.white};
        border-right: 1px solid ${theme.color.greyVeryLight};

        @media ${theme.mediaQueries.querySm} {
            min-width: ${headVariables.tableCompareLeftWidthMobile};
            max-width: none;
            width: auto;
        }

        @media ${theme.mediaQueries.queryLg} {
            min-width: ${headVariables.tableCompareLeftWidth};
        }
    `,
);

export const HeadStickyItemStyled = styled.div(
    ({ theme }) => css`
        display: flex;
        flex-direction: row;
        align-items: center;
        padding: 0 4px;
        min-width: calc(${headItemVariables.itemInWidthSmall} + ${headItemVariables.itemPaddingSmall} * 2);
        max-width: calc(${headItemVariables.itemInWidthSmall} + ${headItemVariables.itemPaddingSmall} * 2);
        height: 72px;

        border-right: 1px solid ${theme.color.greyVeryLight};

        @media ${theme.mediaQueries.querySm} {
            min-width: calc(${headItemVariables.itemInWidth} + ${headItemVariables.itemPadding} * 2);
            max-width: calc(${headItemVariables.itemInWidth} + ${headItemVariables.itemPadding} * 2);
        }
    `,
);

export const HeadStickyItemImageStyled = styled.a`
    width: 70px;
`;

export const HeadStickyItemInfoStyled = styled.div`
    display: flex;
    flex-direction: column;
    flex: 1;
    margin-left: 10px;
`;

export const HeadStickyItemCodeStyled = styled.div`
    font-size: 12px;
`;

export const HeadStickyItemNameStyled = styled.a`
    line-height: 17px;
    height: calc(17px * 2);
    overflow: hidden;

    font-size: 12px;
    text-decoration: none;

    &:hover {
        text-decoration: underline;
    }
`;
