import styled, { css } from 'styled-components';

const localVariables = {
    labelFontSizeSmall: '11px',
    labelActivePositionTop: '9px',
    labelLeftPosition: '12px',
} as const;

export const StyledShopsysLabelWrapper = styled.div`
    ${({ theme }) => css`
        position: relative;
        width: 100%;

        input:disabled,
        input[readonly],
        textarea:disabled,
        textarea[readonly] {
            & ~ label {
                opacity: 0.5;
                pointer-events: none;
                cursor: no-drop;
            }
        }

        input:focus,
        textarea:focus,
        input:not(input:placeholder-shown),
        textarea:not(textarea:placeholder-shown) {
            & ~ label {
                transform: none;
            }
        }

        input:focus,
        input:not(input:placeholder-shown) {
            & ~ label {
                top: ${localVariables.labelActivePositionTop};

                font-size: ${localVariables.labelFontSizeSmall};
            }
        }

        textarea:focus,
        textarea:not(textarea:placeholder-shown) {
            & ~ label {
                top: ${localVariables.labelActivePositionTop};

                font-size: ${localVariables.labelFontSizeSmall};
            }
        }

        label {
            position: absolute;
            display: block;
            left: ${localVariables.labelLeftPosition};

            transition: ${theme.transition};
            z-index: ${theme.zIndex.above + 1};
            line-height: 14px;
            color: ${theme.color.greyDark};
            font-size: ${theme.fontSize.small};
        }

        input {
            & ~ label {
                top: 50%;
                transform: translateY(-50%);
            }
        }

        textarea {
            & ~ label {
                top: 20px;
                transform: translateY(0);
            }
        }
    `}
`;

export const StyledShopsysRequiredSymbol = styled.span`
    ${({ theme }) => css`
        margin-left: 5px;

        color: ${theme.color.red};
    `}
`;
