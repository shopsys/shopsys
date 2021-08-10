import styled, { css } from 'styled-components';

const localVariables = {
    inputHeight: '54px',
    inputPaddingVertical: '20px',
    inputPaddingHorizontal: '10px',
    inputBorderWidth: '2px',
} as const;

type ShopsysTextInputStyledProps = {
    inputState?: 'success' | 'error' | undefined;
};

export const StyledShopsysInputFormLine = styled.div`
    width: 100%;
`;

export const StyledShopsysTextInput = styled.input<ShopsysTextInputStyledProps>`
    ${({ theme, inputState }) => css`
        box-sizing: border-box;
        height: ${localVariables.inputHeight};
        width: 100%;
        padding: ${localVariables.inputPaddingVertical} ${localVariables.inputPaddingHorizontal} 0
            ${localVariables.inputPaddingHorizontal};

        border: ${localVariables.inputBorderWidth} solid ${theme.color.border};
        color: ${theme.color.base};
        background-color: ${theme.color.white};
        border-radius: ${theme.radius.default};
        font-size: ${theme.fontSize.default};

        // iOS ugly appearance fix
        -webkit-appearance: none !important;

        &::placeholder {
            color: transparent;
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
    `}
`;

export const StyledShopsysPasswordVisibilityToggle = styled.img`
    width: 25px;
    position: absolute;
    top: 50%;
    right: 15px;
    transform: translateY(-50%);

    cursor: pointer;

    &.not-visible {
        opacity: 50%;
    }
`;
