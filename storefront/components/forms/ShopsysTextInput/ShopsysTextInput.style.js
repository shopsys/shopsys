import styled from 'styled-components';

const localVariables = {
    inputHeight: '54px',
    inputPaddingVertical: '20px',
    inputPaddingHorizontal: '10px',
    inputBorderWidth: '2px',
    inputLabelPositionTop: '50%',
    inputLabelFontSizeSmall: '11px',
    inputLabelActivePositionTop: '9px',
};

export const ShopsysInputFormLine = styled.div`
    margin-bottom: 12px;
`;

export const ShopsysTextInputStyled = styled.div`
    position: relative;
    width: 100%;

    input {
        box-sizing: border-box;
        height: ${localVariables.inputHeight};
        width: 100%;
        padding: ${localVariables.inputPaddingVertical} ${localVariables.inputPaddingHorizontal} 0
            ${localVariables.inputPaddingHorizontal};

        border: ${localVariables.inputBorderWidth} solid ${(props) => props.theme.color.border};
        color: ${(props) => props.theme.color.base};
        background-color: ${(props) => props.theme.color.white};
        border-radius: ${(props) => props.theme.radius.default};
        font-size: ${(props) => props.theme.fontSize.default};

        // iOS ugly appearance fix
        -webkit-appearance: none !important;

        &::placeholder {
            color: transparent;
        }

        ${(props) =>
            props.inputState === 'error' &&
            `
            box-shadow: none;
            background-color: ${props.theme.color.white};
            border-color: ${props.theme.color.red};
        `}

        ${(props) =>
            props.inputState === 'success' &&
            `
            border: 1px solid ${props.theme.color.green};
            border-radius: ${props.theme.radius.medium};
            box-shadow: ${props.theme.boxShadow.green};
        `};

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
            color: ${(props) => props.theme.color.greyLight};

            &:focus-visible {
                color: ${(props) => props.theme.color.base};
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
            box-shadow: 0 0 0 1000px ${(props) => props.theme.color.white} inset !important;
            background-color: ${(props) => props.theme.color.white} !important;
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

        transition: ${(props) => props.theme.transition};
        z-index: ${(props) => props.theme.zIndex.above + 1};
        line-height: 14px;
        color: ${(props) => props.theme.color.greyDark};
        font-size: ${(props) => props.theme.fontSize.small};

        .required {
            margin-left: 5px;

            color: ${(props) => props.theme.color.red};
        }
    }
`;

export const ShopsysPasswordVisibilityToggle = styled.img`
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

export const ShopsysFormFieldErrorStyled = styled.div`
    position: relative;
    margin-top: 6px;
`;

export const ShopsysErrorMessage = styled.span`
    line-height: 21px;
    color: ${(props) => props.theme.color.red};
    font-size: ${(props) => props.theme.fontSize.small};
`;

export const ShopsysErrorIcon = styled.img`
    transform: translateY(-50%);
    display: flex;
    width: 16px;
    position: absolute;
    top: -33px;
    right: 19px;
`;
