import { styled } from 'components/Theme/main';
import SelectReact from 'react-select';
import { css } from 'styled-components';
import tinycolor from 'tinycolor2';

const localVariables = {
    selectBorderWidth: '2px',
    selectScrollbarSize: '5px',
    selectScrollbarColor: '#828890',
} as const;

type SelectStyledProps = {
    inputStateError: boolean;
};

export const SelectStyled = styled(SelectReact)<SelectStyledProps>(
    ({ theme, inputStateError }) => css`
        .select__placeholder {
            display: none;
        }

        .select__control {
            min-height: 54px;

            align-self: flex-end;
            align-items: flex-end;
            border: ${localVariables.selectBorderWidth} solid ${theme.color.border};
            border-radius: ${theme.radius.big};
            box-shadow: none;
            cursor: pointer;
            z-index: ${theme.zIndex.above} + 1;

            &:hover {
                border: ${localVariables.selectBorderWidth} solid ${theme.color.border};
            }

            ${inputStateError === true &&
            css`
                box-shadow: none;
                background-color: ${theme.color.white};
                border-color: ${theme.color.red};

                &:hover {
                    border: ${localVariables.selectBorderWidth} solid ${theme.color.red};
                }
            `}
        }

        .select__indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            width: 50px;
            height: 50px;

            transition: ${theme.transition};

            & i {
                width: 20px;
                height: 20px;

                color: ${theme.color.greyDark};
            }
        }

        .select__menu {
            margin: 0;
            top: calc(100% - 9px);
            overflow: hidden;

            border: 2px solid ${theme.color.border};
            border-radius: 0 0 ${theme.radius.big} ${theme.radius.big};
            box-shadow: none;
        }

        .select__menu-list {
            padding: 17px 0 0;

            &::-webkit-scrollbar {
                width: ${localVariables.selectScrollbarSize};
                height: 0px;
            }

            &::-webkit-scrollbar-track {
                background: #f0f0f0;
            }

            &::-webkit-scrollbar-thumb {
                background: ${localVariables.selectScrollbarColor};
            }

            &::-webkit-scrollbar-thumb:hover {
                background: ${tinycolor(localVariables.selectScrollbarColor).darken(10).toString()};
            }
        }

        .select__option {
            padding: 10px;

            color: ${theme.color.base};
            cursor: pointer;

            &:active {
                background-color: ${theme.color.blueLight};
            }
        }

        .select__option--is-selected,
        .select__option--is-focused {
            background-color: ${theme.color.blueLight};
        }

        .select__control--menu-is-open {
            align-items: flex-end;

            .select__indicator {
                transform: rotate(180deg);

                svg {
                    color: ${theme.color.primary};
                }
            }
        }
    `,
);
