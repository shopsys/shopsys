import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

type PaginationButtonStyledProps = {
    active?: boolean;
    dotButton?: boolean;
};

const localVariables = {
    paginationWidth: '335px',
    buttonHeightAndWidth: '45px',
};

export const PaginationWrapperStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        justify-content: center;
        margin: 10px auto;
        width: 100%;
        gap: 5px;

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

export const PaginationButtonStyled = styled.button<PaginationButtonStyledProps>`
    ${({ theme, active, dotButton }) => css`
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

        ${dotButton &&
        css`
            &:hover {
                cursor: default;
            }
        `};
    `};
`;
