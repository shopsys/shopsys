import styled, { css } from 'styled-components';

const localVariables = {
    textareaPaddingVertical: '20px',
    textareaPaddingHorizontal: '10px',
    textareaBorderWidth: '2px',
    textareaLabelFontSizeSmall: '11px',
    textareaLabelActivePositionTop: '9px',
};

type ShopsysTextareaStyledProps = {
    inputState?: string;
};

export const StyledShopsysInputFormLine = styled.div`
    margin-bottom: 12px;
`;

export const StyledShopsysTextarea = styled.div<ShopsysTextareaStyledProps>`
    position: relative;
    width: 100%;

    textarea {
        resize: vertical;
        box-sizing: border-box;
        width: 100%;
        padding: ${localVariables.textareaPaddingVertical} ${localVariables.textareaPaddingHorizontal};

        border: ${localVariables.textareaBorderWidth} solid ${(props) => props.theme.color.border};
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
                top: ${localVariables.textareaLabelActivePositionTop};

                font-size: ${localVariables.textareaLabelFontSizeSmall};
            }
        }

        :not(:placeholder-shown) {
            ~ label {
                transform: none;
                top: ${localVariables.textareaLabelActivePositionTop};

                font-size: ${localVariables.textareaLabelFontSizeSmall};
            }
        }
    }

    label {
        position: absolute;
        transform: translateY(0);
        top: ${localVariables.textareaPaddingVertical};
        left: ${`calc(${localVariables.textareaPaddingHorizontal} + ${localVariables.textareaBorderWidth})`};
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

export const StyledShopsysRequiredSymbol = styled.span`
    ${({ theme }) => css`
        margin-left: 5px;

        color: ${theme.color.red};
    `}
`;

export const StyledShopsysFormFieldError = styled.div`
    position: relative;
    margin-top: 6px;
`;

export const StyledShopsysErrorMessage = styled.span`
    line-height: 21px;
    color: ${(props) => props.theme.color.red};
    font-size: ${(props) => props.theme.fontSize.small};
`;

export const StyledShopsysErrorIcon = styled.img`
    display: flex;
    width: 16px;
    position: absolute;
    top: 2px;
    right: 19px;
`;
