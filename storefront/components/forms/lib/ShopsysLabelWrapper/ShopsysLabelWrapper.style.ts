import styled, { css } from 'styled-components';
import { Theme } from 'theme/main';

const localVariables = {
    labelFontSizeSmall: '11px',
    labelActivePositionTop: '9px',
    labelLeftPosition: '12px',
    choiceMarginLeft: '30px',
    choiceFilterMarginLeft: '25px',
    choiceIconSize: '18px',
} as const;

type StyledShopsysLabelWrapperProps = {
    inputType: 'text-input' | 'textarea' | 'checkbox' | 'radio';
};

export const StyledShopsysLabelWrapper = styled.div<StyledShopsysLabelWrapperProps>`
    ${({ theme, inputType }: { theme: Theme } & StyledShopsysLabelWrapperProps) => css`
        position: relative;
        width: 100%;

        ${inputType === 'text-input' || inputType === 'textarea'
            ? css`
                  input,
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
              `
            : ''}

        ${inputType === 'text-input' &&
        css`
            input {
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
        `}

        ${inputType === 'textarea' &&
        css`
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
        `}

        ${inputType === 'checkbox' &&
        css`
            input {
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
            }
        `}
        
        ${inputType === 'radio' &&
        css`
            input {
                & ~ label {
                    cursor: pointer;

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

                        img {
                            height: ${localVariables.choiceIconSize};
                            margin-right: 10px;
                        }
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

                        div > span {
                            cursor: no-drop;
                            color: ${theme.color.greyLight};
                        }

                        div > img {
                            cursor: no-drop;
                            filter: grayscale(100%);
                        }

                        &:before {
                            background-position-x: right;
                        }
                    }
                }
            }
        `}
    `}
`;

export const StyledShopsysRequiredSymbol = styled.span`
    ${({ theme }) => css`
        margin-left: 5px;

        color: ${theme.color.red};
    `}
`;
