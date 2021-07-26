import styled, { css } from 'styled-components';

const localVariables = {
    inputMarginLeft: '30px',
    inputFilterMarginLeft: '25px',
    inputIconSize: '18px',
};

export const StyledShopsysChoiceFormLine = styled.div`
    margin-bottom: 16px;
`;

export const StyledShopsysCheckbox = styled.div`
    ${({ theme }) => css`
        input {
            position: absolute;
            height: 1px;
            width: 1px;
            left: -1000px;
            margin: -1px;
            padding: 0;
            z-index: -1000;
            overflow: hidden;

            clip: rect(0 0 0 0);
            border: 0;

            &:focus,
            &:active {
                & + label {
                    &:before {
                        background-position-x: center;
                    }
                }
            }

            &:checked {
                & + label {
                    &:before {
                        background-position-y: bottom;
                    }
                }
            }

            &:disabled {
                & + label {
                    &:before {
                        cursor: no-drop;
                        background-position-x: right;
                    }
                }

                & ~ label {
                    cursor: no-drop;
                    color: ${theme.color.greyLight};
                }
            }
        }

        label {
            position: relative;
            display: inline-block;
            padding-left: ${localVariables.inputMarginLeft};
            min-height: ${localVariables.inputIconSize};

            font-size: ${theme.fontSize.small};
            color: ${theme.color.base};
            cursor: pointer;
            user-select: none;

            &:before {
                content: '';
                position: absolute;
                display: inline-block;
                top: 0;
                left: 0;
                width: ${localVariables.inputIconSize};
                height: ${localVariables.inputIconSize};

                background: no-repeat left top/
                    ${`calc(3 * ${localVariables.inputIconSize}) calc(2 * ${localVariables.inputIconSize})`};
            }

            &:hover {
                &:before {
                    background-position-x: center;
                }
            }

            &:active {
                &:before {
                    background-position-y: bottom !important;
                }
            }

            a {
                color: ${theme.color.base};

                &:hover,
                &:focus,
                &:active {
                    color: ${theme.color.orange};
                }
            }

            &:before {
                background-image: url('/images/custom_checkbox.png');
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

export const StyledShopsysFormFieldError = styled.div`
    margin-top: 6px;
    position: relative;
`;

export const StyledShopsysErrorMessage = styled.span`
    ${({ theme }) => css`
        line-height: 21px;
        color: ${theme.color.red};
        font-size: ${theme.fontSize.small};
    `}
`;

export const StyledShopsysErrorIcon = styled.img`
    display: flex;
    width: 16px;
    position: absolute;
    right: -19px;
    top: 2px;
`;
