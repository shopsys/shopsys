import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const FormFieldErrorStyled = styled.div`
    position: relative;
    margin-top: 6px;
`;

export const ErrorMessageStyled = styled.span(
    ({ theme }) => css`
        line-height: 21px;
        color: ${theme.color.red};
        font-size: ${theme.fontSize.small};
    `,
);
