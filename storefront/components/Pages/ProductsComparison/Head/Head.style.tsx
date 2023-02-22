import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const localVariables = {
    tableCompareLeftWidth: '298px',
    tableCompareLeftWidthMobile: '211px',
    tableCompareLeftWidthMobileSmall: '115px',
    tableCompareItemHeight: '361px',
} as const;

export const HeadRowStyled = styled.tr`
    min-height: 40px;
`;

export const HeadMainSquareStyled = styled.td(
    ({ theme }) => css`
        position: sticky;
        left: 0;
        z-index: calc(${theme.zIndex.above} + 1);
        min-width: ${localVariables.tableCompareLeftWidthMobileSmall};
        max-width: 205px;
        height: ${localVariables.tableCompareItemHeight};
        vertical-align: top;
        padding-right: 12px;

        box-shadow: inset -1px 0px 0px 0px ${theme.color.greyVeryLight};
        background-color: ${theme.color.white};

        @media ${theme.mediaQueries.querySm} {
            min-width: ${localVariables.tableCompareLeftWidthMobile};
        }

        @media ${theme.mediaQueries.queryLg} {
            min-width: ${localVariables.tableCompareLeftWidth};
        }
    `,
);
