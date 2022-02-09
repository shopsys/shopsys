import { css } from 'styled-components';
import { styled } from 'components/Theme/main';
type LabelWrapperStyledProps = {
    inputType: 'text-input' | 'textarea' | 'checkbox' | 'radio' | 'selectbox';
    selectBoxLabelIsFloated?: boolean;
};

const localVariables = {
    labelFontSizeSmall: '11px',
    labelActivePositionTop: '9px',
    labelLeftPosition: '12px',
    choiceMarginLeft: '30px',
    choiceFilterMarginLeft: '25px',
    choiceIconSize: '18px',
} as const;

export const LabelWrapperStyled = styled.div<LabelWrapperStyledProps>`
    ${({ theme, inputType, selectBoxLabelIsFloated }) => css`
        position: relative;
        width: 100%;

        ${inputType === 'text-input' || inputType === 'selectbox' || inputType === 'textarea'
            ? css`
                  input,
                  textarea,
                  .selectbox {
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
                              ${(selectBoxLabelIsFloated === undefined || selectBoxLabelIsFloated === true) &&
                              css`
                                  transform: none;
                              `}
                          }
                      }

                      & ~ label {
                          position: absolute;
                          display: block;
                          left: ${localVariables.labelLeftPosition};

                          transition: ${theme.transition};
                          z-index: ${theme.zIndex.above + 1};
                          line-height: 14px;
                          color: ${theme.color.grey};
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

        ${inputType === 'selectbox' &&
        css`
            .selectbox {
                & ~ label {
                    top: 50%;
                    transform: translateY(-50%);

                    ${selectBoxLabelIsFloated &&
                    css`
                        top: ${localVariables.labelActivePositionTop};

                        font-size: ${localVariables.labelFontSizeSmall};
                    `}
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

                    > div {
                        align-items: center;
                        position: relative;
                        display: flex;
                        padding-left: ${localVariables.choiceMarginLeft};
                        min-height: ${localVariables.choiceIconSize};
                    }

                    &:before {
                        content: '';
                        position: absolute;
                        display: inline-block;
                        top: 50%;
                        transform: translateY(-50%);
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

export const RequiredSymbolStyled = styled.span`
    ${({ theme }) => css`
        margin-left: 5px;

        color: ${theme.color.red};
    `}
`;
