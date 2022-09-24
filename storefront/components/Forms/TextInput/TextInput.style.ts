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
    hasError?: boolean;
    inputSize?: 'small' | 'default';
};

type PasswordVisibilityToggleStyledProps = {
    isVisible: boolean;
};

export const TextInputStyled = styled.input<TextInputStyledProps>(
    ({ theme, hasError, inputSize }) => css`
        box-sizing: border-box;
        height: ${inputSize === 'small' ? localVariables.inputHeightSmall : localVariables.inputHeightDefault};
        width: 100%;
        padding: ${localVariables.inputPaddingVertical} ${localVariables.inputPaddingHorizontal} 0
            ${localVariables.inputPaddingHorizontal};

        border: ${localVariables.inputBorderWidth} solid ${theme.color.border};
        color: ${theme.color.base};
        background-color: ${theme.color.white};
        border-radius: ${theme.radius.big};
        font-size: ${inputSize === 'small' ? '13px' : theme.fontSize.default};

        /* iOS ugly appearance fix */
        -webkit-appearance: none !important;
        -moz-appearance: textfield !important;

        &::placeholder {
            color: transparent;
        }

        ${hasError &&
        css`
            box-shadow: none;
            background-color: ${theme.color.white};
            border-color: ${theme.color.red};
        `}

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
    `,
);

export const SearchTextInputStyled = styled.input(
    ({ theme }) => css`
        box-sizing: border-box;
        height: ${localVariables.inputHeightSmall};
        width: 100%;
        padding: 0 45px 0 15px;
        margin-bottom: 0;

        border: ${localVariables.inputBorderWidth} solid ${theme.color.white};
        color: ${theme.color.base};
        background-color: ${theme.color.white};
        border-radius: ${theme.radius.big};
        font-size: ${theme.fontSize.default};

        /* iOS ugly appearance fix */
        -webkit-appearance: none !important;
        -moz-appearance: textfield !important;

        &::placeholder {
            color: ${theme.color.grey};
            opacity: 1;
        }

        &:-webkit-autofill,
        &:-webkit-autofill:hover,
        &:-webkit-autofill:focus,
        &:-internal-autofill-selected {
            box-shadow: 0 0 0 1000px ${theme.color.white} inset !important;
            background-color: ${theme.color.white} !important;
        }

        &::-webkit-search-decoration,
        &::-webkit-search-cancel-button,
        &::-webkit-search-results-button,
        &::-webkit-search-results-decoration {
            -webkit-appearance: none;
        }

        &:focus {
            outline: none;
        }
    `,
);

export const TextInputLoadingWrapper = styled.div`
    position: absolute;
    top: calc(50% - 16px);
    right: 15px;

    width: 32px;
    height: 32px;
    display: flex;
    justify-content: center;
    align-items: center;
`;

export const PasswordTextInputStyled = styled(TextInputStyled)<TextInputStyledProps>(
    ({ theme }) => css`
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
