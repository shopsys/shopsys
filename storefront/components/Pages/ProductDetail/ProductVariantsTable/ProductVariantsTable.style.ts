import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

const localVariables = {
    tableGab: '5px',
} as const;

export const VariantsTableStyled = styled.table`
    margin-bottom: 20px;
    width: 100%;
`;

export const VariantsTableHeaderStyled = styled.thead`
    ${({ theme }) => css`
        @media ${theme.mediaQueries.queryTablet} {
            display: none;
        }
    `}
`;

export const VariantsTableBodyStyled = styled.tbody`
    ${({ theme }) => css`
        @media ${theme.mediaQueries.queryTablet} {
            display: flex;
            flex-wrap: wrap;
            margin-left: -${localVariables.tableGab};
        }

        ${theme.mediaQueries.queryMobile} {
            margin-left: 0;
        }
    `}
`;

export const VariantsTableRowStyled = styled.tr`
    ${({ theme }) =>
        css`
            @media ${theme.mediaQueries.queryTablet} {
                margin-left: ${localVariables.tableGab};
                margin-bottom: ${localVariables.tableGab};
                width: calc(50% - ${localVariables.tableGab});
                display: block;
                padding: ${localVariables.tableGab};
                position: relative;

                border: 1px solid ${theme.color.greyLighter};
            }
            @media ${theme.mediaQueries.queryMobile} {
                margin-left: 0;
                width: 100%;
            }
        `}
`;

export const TableHeaderCellStyled = styled.th`
    ${({ theme }) =>
        css`
            text-align: center;
            vertical-align: middle;

            @media ${theme.mediaQueries.queryTablet} {
                text-align: left;
                display: block;
                padding-left: 50px;
            }

            @media ${theme.mediaQueries.queryLg} {
                padding: ${localVariables.tableGab};
                text-align: left;

                border-bottom: 1px solid ${theme.color.greyLighter};
                font-size: 12px;
            }
        `}
`;

export const TableHeaderImageCellStyled = styled(TableHeaderCellStyled)`
    ${({ theme }) => css`
        @media ${theme.mediaQueries.queryTablet} {
            width: 40px;
        }

        @media ${theme.mediaQueries.queryLg} {
            width: 100px;
        }
    `}
`;

export const TableHeaderPriceCellStyled = styled(TableHeaderCellStyled)`
    ${({ theme }) => css`
        @media ${theme.mediaQueries.queryLg} {
            text-align: right;
        }
    `}
`;

export const TableHeaderActionCellStyled = styled(TableHeaderCellStyled)`
    ${({ theme }) => css`
        @media ${theme.mediaQueries.queryLg} {
            width: 240px;
        }
    `}
`;
