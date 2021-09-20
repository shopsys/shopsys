import { css } from 'styled-components';
import { PaginationButtonActiveType } from './Pagination';
import { styled } from 'components/Theme/main';

const localVariables = {
    paginationWidth: '300px',
    buttonHeightAndWidth: '45px',
};

export const PaginationWrapperStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        justify-content: space-around;
        margin: 10px auto;
        width: 100%;

        @media ${theme.mediaQueries.querySm} {
            width: ${localVariables.paginationWidth};
        }

        @media ${theme.mediaQueries.queryVl} {
            position: relative;
            width: ${localVariables.paginationWidth};
            left: 100%;
            margin-left: -${localVariables.paginationWidth};
        }
    `}
`;

export const PaginationButtonStyled = styled.button<PaginationButtonActiveType>`
    ${({ theme, active }) => css`
        width: ${localVariables.buttonHeightAndWidth};
        height: ${localVariables.buttonHeightAndWidth};

        background-color: ${theme.color.white};
        border: 1px solid ${theme.color.whitesmoke};
        border-radius: ${theme.radius.medium};
        font-weight: 700;

        &:hover {
            cursor: pointer;
        }

        ${active &&
        css`
            background-color: ${theme.color.orange};
            border: none;

            &:hover {
                cursor: default;
            }
        `};
    `};
`;
