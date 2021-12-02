import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

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
            }
        }
    `}
`;
