import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const ErrorListStyled = styled.ul`
    overflow-y: auto;
    max-height: 50vh;
`;

export const ErrorListItemStyled = styled.li(
    ({ theme }) => css`
        margin-bottom: 6px;
        padding-bottom: 6px;

        border-bottom: 1px solid ${theme.color.greyLighter};
    `,
);

export const ErrorMessageStyled = styled.span(
    ({ theme }) => css`
        color: ${theme.color.red};
    `,
);
