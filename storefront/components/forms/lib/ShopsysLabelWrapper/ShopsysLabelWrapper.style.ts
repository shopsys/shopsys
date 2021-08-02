import styled, { css } from 'styled-components';

const localVariables = {
    labelFontSizeSmall: '11px',
    labelActivePositionTop: '9px',
    labelLeftPosition: '12px',
    choiceMarginLeft: '30px',
    choiceFilterMarginLeft: '25px',
    choiceIconSize: '18px',
} as const;

export const StyledShopsysLabelWrapper = styled.div`
    ${({ theme }) => css`
        position: relative;
        width: 100%;

        input[type='text'],
        input[type='password'],
        input[type='email'],
        input[type='tel'],
        textarea {
            &:disabled,
            &[readonly] {
                & ~ label {
                    opacity: 0.5;
                    pointer-events: none;
                    cursor: no-drop;
                }
            }

            &:focus,
            &:not(input:placeholder-shown) {
                & ~ label {
                    transform: none;
                }
            }

            & ~ label {
                position: absolute;
                display: block;
                left: ${localVariables.labelLeftPosition};

                transition: ${theme.transition};
                z-index: ${theme.zIndex.above + 1};
                line-height: 14px;
                color: ${theme.color.greyDark};
                font-size: ${theme.fontSize.small};
            }
        }

        input[type='text'],
        input[type='password'],
        input[type='email'],
        input[type='tel'] {
            & ~ label {
                top: 50%;
                transform: translateY(-50%);
            }

            &:focus,
            &:not(input:placeholder-shown) {
                & ~ label {
                    top: ${localVariables.labelActivePositionTop};

                    font-size: ${localVariables.labelFontSizeSmall};
                }
            }
        }

        textarea {
            & ~ label {
                top: 20px;
                transform: translateY(0);
            }

            &:focus,
            &:not(textarea:placeholder-shown) {
                & ~ label {
                    top: ${localVariables.labelActivePositionTop};

                    font-size: ${localVariables.labelFontSizeSmall};
                }
            }
        }

        input[type='checkbox'] {
            &:focus,
            &:active {
                & ~ label {
                    &:before {
                        background-position-x: center;
                    }
                }
            }

            &:checked {
                & ~ label {
                    &:before {
                        background-position-y: bottom;
                    }
                }
            }

            &:disabled {
                & ~ label {
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

            & ~ label {
                position: relative;
                display: inline-block;
                padding-left: ${localVariables.choiceMarginLeft};
                min-height: ${localVariables.choiceIconSize};

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
                    width: ${localVariables.choiceIconSize};
                    height: ${localVariables.choiceIconSize};

                    background: no-repeat left top/
                        ${`calc(3 * ${localVariables.choiceIconSize}) calc(2 * ${localVariables.choiceIconSize})`};
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
        }

        input[type='radio'] {
            & ~ label {
                div {
                    align-items: center;
                    position: relative;
                    display: flex;
                    padding-left: ${localVariables.choiceMarginLeft};
                    min-height: ${localVariables.choiceIconSize};

                    span {
                        font-size: ${theme.fontSize.small};
                        color: ${theme.color.base};
                        cursor: pointer;
                        user-select: none;
                    }
                }

                img {
                    height: ${localVariables.choiceIconSize};
                    margin-right: 10px;
                }

                &:before {
                    content: '';
                    position: absolute;
                    display: inline-block;
                    top: 0;
                    left: 0;
                    width: ${localVariables.choiceIconSize};
                    height: ${localVariables.choiceIconSize};

                    background: no-repeat left top/
                        ${`calc(3 * ${localVariables.choiceIconSize}) calc(2 * ${localVariables.choiceIconSize})`};
                    background-image: url('/images/custom_radio.png');
                }

                &:hover {
                    &:before {
                        background-position-x: center;
                    }
                }
            }

            &:focus,
            &:active {
                & ~ label {
                    &:before {
                        background-position-x: center;
                    }
                }
            }

            &:checked {
                & ~ label {
                    &:before {
                        background-position-y: bottom;
                    }
                }
            }

            &:disabled {
                & ~ label {
                    cursor: no-drop;
                    color: ${theme.color.greyLight};

                    &:before {
                        cursor: no-drop;
                        background-position-x: right;
                    }
                }
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
