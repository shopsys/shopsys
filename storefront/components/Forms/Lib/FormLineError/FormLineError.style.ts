import { Icon } from 'components/Basic/Icon/Icon';
import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

const localVariables = {
    smallTextInputErrorIconTopOffset: '-30px',
    defaultTextInputErrorIconTopOffset: '-33px',
} as const;

type ErrorIconStyledProps = {
    inputType: 'textarea' | 'text-input' | 'checkbox' | 'text-input-password' | 'select';
    textInputSize?: 'small';
};

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

export const ErrorIconStyled = styled(Icon)<ErrorIconStyledProps>(
    ({ theme, inputType, textInputSize }) => css`
        display: flex;
        position: absolute;
        width: 16px;
        height: 16px;

        color: ${theme.color.red};

        ${(inputType === 'text-input' || inputType === 'textarea') &&
        css`
            transform: translateY(-50%);
            top: ${textInputSize === 'small'
                ? localVariables.smallTextInputErrorIconTopOffset
                : localVariables.defaultTextInputErrorIconTopOffset};
            right: 19px;
        `}

        ${inputType === 'text-input-password' &&
        css`
            transform: translateY(-50%);
            top: ${textInputSize === 'small'
                ? localVariables.smallTextInputErrorIconTopOffset
                : localVariables.defaultTextInputErrorIconTopOffset};
            right: 45px;
        `}

        ${inputType === 'text-input-password' &&
        css`
            transform: translateY(-50%);
            top: ${textInputSize === 'small'
                ? localVariables.smallTextInputErrorIconTopOffset
                : localVariables.defaultTextInputErrorIconTopOffset};
            right: 45px;
        `}

        ${inputType === 'checkbox' &&
        css`
            top: 2px;
            right: -19px;
        `}

         ${inputType === 'select' &&
        css`
            z-index: 2;
            top: -40px;
            right: 45px;
        `}
    `,
);
