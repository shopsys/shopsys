import { css } from 'styled-components';
import { styled } from '../../Theme/main';

export const TableStyled = styled.table`
    ${({ theme }) => css`
        width: 100%;

        tr {
            border-top: 1px solid ${theme.color.greyLighter};

            &:first-child {
                border-top: 0;
            }
        }

        td,
        th {
            padding: 8px 0;
            line-height: 18px;

            font-size: ${theme.fontSize.small};
        }

        th {
            text-align: left;

            font-weight: 700;
            text-transform: uppercase;
        }

        td {
            text-align: right;
        }
    `}
`;
