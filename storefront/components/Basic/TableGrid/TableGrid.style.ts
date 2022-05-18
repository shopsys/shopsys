import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const TableGridStyled = styled.div`
    ${({ theme }) => css`
        margin-bottom: 24px;
        overflow-x: auto;

        border-radius: ${theme.radius.biggest};
        border: 2px solid ${theme.color.border};
    `}
`;

export const TableGridRootStyled = styled.table`
    ${({ theme }) => css`
        width: 100%;

        tr {
            border-bottom: 1px solid ${theme.color.border};

            &:last-child {
                border-bottom: 0;
            }

            th {
                padding: 24px 15px 17px;

                font-size: 13px;
                font-weight: 700;
                color: ${theme.color.greyLight};
                text-align: left;

                @media ${theme.mediaQueries.queryMd} {
                    padding-top: 20px;
                }

                @media ${theme.mediaQueries.queryVl} {
                    white-space: nowrap;
                }

                &.text-right {
                    text-align: right;
                }
            }

            td {
                padding: 24px 15px 17px;
                line-height: 18px;
                text-align: left;

                font-size: ${theme.fontSize.small};

                &.text-right {
                    text-align: right;
                }

                &.nowrap {
                    white-space: nowrap;
                }
            }
        }
    `}
`;

export const TableGridColumnsStyled = styled.tbody`
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    align-items: flex-start;
    padding: 25px;
`;

export const TableGridColumnStyled = styled.table`
    ${({ theme }) => css`
        width: 100%;

        @media ${theme.mediaQueries.queryMd} {
            width: calc(50% - 15px);
            margin-right: 15px;

            &:nth-child(even) {
                margin-right: 0;
                margin-left: 15px;
            }
        }

        tr {
            th {
                margin-bottom: 0;
                padding-bottom: 15px;
                padding-left: 0;
                text-transform: none;

                font-size: ${theme.fontSize.bigger} !important;
                font-weight: 400 !important;
                border-bottom: 2px solid ${theme.color.greyLighter};
                color: ${theme.color.base};
            }

            td {
                padding-left: 0;

                color: ${theme.color.greyLight};
                font-size: 15px;

                &.text-right {
                    text-align: right;
                }

                &.nowrap {
                    white-space: nowrap;
                }
            }
        }
    `}
`;
