import styled from 'styled-components';
import { Theme } from 'theme/main';

const localVariables = {
    inputHeight: '54px',
    inputPaddingVertical: '20px',
    inputPaddingHorizontal: '10px',
    inputBorderWidth: '2px',
    inputLabelPositionTop: '50%',
    inputLabelFontSizeSmall: '11px',
    inputLabelActivePositionTop: '9px',
};

type StyledShopsysInputState = 'error' | 'success' | undefined;

type ShopsysTextInputStyledProps = {
    inputState: StyledShopsysInputState;
};

export const StyledShopsysInputFormLine = styled.div`
    margin-bottom: 12px;
`;

export const StyledShopsysTextInput = styled.div<ShopsysTextInputStyledProps>`
    ${({ inputState, theme }: { inputState: StyledShopsysInputState; theme: Theme }) => `
        position: relative;
        width: 100%;

        input {
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

            ${
                inputState === 'error' &&
                `
                box-shadow: none;
                background-color: ${theme.color.white};
                border-color: ${theme.color.red};
            `
            }

            ${
                inputState === 'success' &&
                `
                border: 1px solid ${theme.color.green};
                border-radius: ${theme.radius.medium};
                box-shadow: ${theme.boxShadow.green};
            `
            }

            &:disabled,
            &[readonly] {
                opacity: 0.5;
                pointer-events: none;
                cursor: no-drop;

                ~ label {
                    opacity: 0.5;
                    pointer-events: none;
                    cursor: no-drop;
                }
            }

            &[type='password'] {
                font-size: 24px;
                color: ${theme.color.greyLight};

                &:focus-visible {
                    color: ${theme.color.base};
                }
            }

            &::-webkit-outer-spin-button,
            &::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }

            &:-webkit-autofill,
            &:-webkit-autofill:hover,
            &:-webkit-autofill:focus,
            &:-internal-autofill-selected {
                box-shadow: 0 0 0 1000px ${theme.color.white} inset !important;
                background-color: ${theme.color.white} !important;
            }

            &:focus {
                outline: 0;

                ~ label {
                    transform: none;
                    top: ${localVariables.inputLabelActivePositionTop};

                    font-size: ${localVariables.inputLabelFontSizeSmall};
                }
            }

            :not(:placeholder-shown) {
                ~ label {
                    transform: none;
                    top: ${localVariables.inputLabelActivePositionTop};

                    font-size: ${localVariables.inputLabelFontSizeSmall};
                }
            }
        }

        label {
            position: absolute;
            top: ${localVariables.inputLabelPositionTop};
            transform: translateY(-50%);
            left: ${`calc(${localVariables.inputPaddingHorizontal} + ${localVariables.inputBorderWidth})`};
            display: block;

            transition: ${theme.transition};
            z-index: ${theme.zIndex.above + 1};
            line-height: 14px;
            color: ${theme.color.greyDark};
            font-size: ${theme.fontSize.small};

            .required {
                margin-left: 5px;

                color: ${theme.color.red};
            }
        }
    `}
`;

export const StyledShopsysRequiredSymbol = styled.span`
    ${({ theme }: { theme: Theme }) => `
        margin-left: 5px;

        color: ${theme.color.red};
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

export const StyledShopsysFormFieldError = styled.div`
    position: relative;
    margin-top: 6px;
`;

export const StyledShopsysErrorMessage = styled.span`
    ${({ theme }: { theme: Theme }) => `
        line-height: 21px;
        color: ${theme.color.red};
        font-size: ${theme.fontSize.small};
    `}
`;

export const StyledShopsysErrorIcon = styled.img`
    transform: translateY(-50%);
    display: flex;
    width: 16px;
    position: absolute;
    top: -33px;
    right: 19px;
`;
