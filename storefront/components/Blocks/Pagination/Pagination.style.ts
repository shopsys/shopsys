import { styled } from 'components/Theme/main';
import { HTMLAttributes } from 'react';
import { css } from 'styled-components';

const localVariables = {
    paginationWidth: '340px',
    buttonHeightAndWidth: '44px',
};

type PaginationButtonStyledProps = HTMLAttributes<HTMLAnchorElement> & {
    active?: boolean;
    dotButton?: boolean;
};

export const PaginationWrapperStyled = styled.div(
    ({ theme }) => css`
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
    `,
);

export const PaginationButtonStyled = styled.a<PaginationButtonStyledProps>(
    ({ theme, active, dotButton }) => css`
        display: flex;
        text-decoration: none;
        width: ${localVariables.buttonHeightAndWidth};
        height: ${localVariables.buttonHeightAndWidth};

        background-color: ${theme.color.white};
        border: 1px solid ${theme.color.whitesmoke};
        border-radius: ${theme.radius.medium};
        font-weight: 700;
        align-items: center;
        justify-content: center;

        &:hover {
            text-decoration: none;
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
    `,
);
