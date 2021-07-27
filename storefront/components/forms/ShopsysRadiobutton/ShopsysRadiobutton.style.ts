import styled, { css } from 'styled-components';

const localVariables = {
    inputMarginLeft: '30px',
    inputFilterMarginLeft: '25px',
    inputIconSize: '18px',
};

export const StyledShopsysChoiceFormLine = styled.div`
    margin-bottom: 16px;
`;

export const StyledShopsysRadiobutton = styled.div`
    ${({ theme }) => css`
        input {
            position: absolute !important;
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
                & + div label {
                    &:before {
                        background-position-x: center;
                    }
                }
            }

            &:checked {
                & + div label {
                    &:before {
                        background-position-y: bottom;
                    }
                }
            }

            &:disabled {
                & + div label {
                    &:before {
                        cursor: no-drop;
                        background-position-x: right;
                    }
                }

                & ~ div label {
                    cursor: no-drop;
                    color: ${theme.color.greyLight};
                }
            }
        }
    `}
`;

export const StyledShopsysRadiobuttonLabel = styled.div`
    ${({ theme }) => css`
        align-items: center;
        position: relative;
        display: flex;
        padding-left: ${localVariables.inputMarginLeft};
        min-height: ${localVariables.inputIconSize};

        label {
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

            &:before {
                background-image: url('/images/custom_radio.png');
            }
    `}
`;

export const StyledShopsysRadiobuttonImage = styled.img`
    height: ${localVariables.inputIconSize};
    margin-right: 10px;
`;
