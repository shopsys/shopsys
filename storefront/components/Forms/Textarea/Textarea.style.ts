import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

const localVariables = {
    textareaPaddingVertical: '20px',
    textareaPaddingHorizontal: '10px',
    textareaBorderWidth: '2px',
    textareaLabelFontSizeSmall: '11px',
    textareaLabelActivePositionTop: '9px',
} as const;

type TextareaStyledProps = {
    inputState?: 'success' | 'error';
};

export const TextareaStyled = styled.textarea<TextareaStyledProps>(
    ({ theme, inputState }) => css`
        resize: vertical;
        box-sizing: border-box;
        width: 100%;
        padding: ${localVariables.textareaPaddingVertical} ${localVariables.textareaPaddingHorizontal};

        border: ${localVariables.textareaBorderWidth} solid ${theme.color.border};
        color: ${theme.color.base};
        background-color: ${theme.color.white};
        border-radius: ${theme.radius.big};
        font-size: ${theme.fontSize.default};

        /* iOS ugly appearance fix */
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
            box-shadow: 0 0 0 1000px ${theme.color.white} inset !important;
            background-color: ${theme.color.white} !important;
        }

        &:focus {
            outline: 0;
        }
    `,
);
