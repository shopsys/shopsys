import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

const localVariables = {
    inputHeightDefault: '54px',
    inputHeightSmall: '48px',
    inputPaddingVertical: '20px',
    inputPaddingHorizontal: '10px',
    inputBorderWidth: '2px',
    inputSearchIconSize: '20px',
} as const;

type TextInputStyledProps = {
    placeholderType?: 'static';
    inputState?: 'success' | 'error';
    inputSize?: 'small';
    variant?: 'searchInHeader';
};

type PasswordVisibilityToggleStyledProps = {
    isVisible: boolean;
};

export const TextInputStyled = styled.input<TextInputStyledProps>(
    ({ theme, inputState, placeholderType, inputSize, variant }) => css`
        box-sizing: border-box;
        height: ${inputSize === 'small' ? localVariables.inputHeightSmall : localVariables.inputHeightDefault};
        width: 100%;
        ${variant === 'searchInHeader' &&
        css`
            padding: 0 45px 0 15px;
        `}
        ${placeholderType === 'static'
            ? css`
                  padding: 0 ${localVariables.inputPaddingHorizontal};
              `
            : css`
                  padding: ${localVariables.inputPaddingVertical} ${localVariables.inputPaddingHorizontal} 0
                      ${localVariables.inputPaddingHorizontal};
              `};

        border: ${localVariables.inputBorderWidth} solid
            ${variant === 'searchInHeader' ? `${theme.color.white}` : `${theme.color.border}`};
        color: ${theme.color.base};
        background-color: ${theme.color.white};
        border-radius: ${theme.radius.big};
        font-size: ${inputSize === 'small' ? '13px' : theme.fontSize.default};

        /* iOS ugly appearance fix */
        -webkit-appearance: none !important;
        -moz-appearance: textfield !important;

        &::placeholder {
            ${placeholderType === 'static'
                ? css`
                      color: ${theme.color.grey};
                      opacity: 1;
                  `
                : css`
                      color: transparent;
                  `}
        }

        ${inputState === 'error' &&
        css`
            box-shadow: none;
            background-color: ${theme.color.white};
            border-color: ${theme.color.red};
        `}

        ${inputState === 'success' &&
        css`
            border: 1px solid ${theme.color.green};
            border-radius: ${theme.radius.medium};
            box-shadow: ${theme.boxShadow.green};
        `};

        &:disabled,
        &[readonly] {
            opacity: 0.5;
            pointer-events: none;
            cursor: no-drop;
        }

        &:-webkit-autofill,
        &:-webkit-autofill:hover,
        &:-webkit-autofill:focus,
        &:-internal-autofill-selected {
            box-shadow: 0 0 0 1000px ${theme.color.white} inset !important;
            background-color: ${theme.color.white} !important;
        }

        &:focus {
            outline: none;
        }

        &[type='password'] {
            font-size: 24px;
            color: ${theme.color.greyLight};

            &:focus-visible {
                color: ${theme.color.base};
            }

            &::-webkit-outer-spin-button,
            &::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }
        }
    `,
);

export const PasswordVisibilityToggleStyled = styled.img<PasswordVisibilityToggleStyledProps>(
    ({ isVisible }) => css`
        width: 25px;
        position: absolute;
        top: 50%;
        right: 15px;
        transform: translateY(-50%);

        cursor: pointer;

        ${!isVisible &&
        css`
            opacity: 50%;
        `}
    `,
);

export const SearchButtonStyled = styled.button`
    position: absolute;
    top: 12px;
    right: 15px;

    cursor: pointer;
    background: transparent;
    border: none;

    & i {
        width: ${localVariables.inputSearchIconSize};
        height: ${localVariables.inputSearchIconSize};
    }
`;
