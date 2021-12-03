import { css } from 'styled-components';
import SelectReact from 'react-select';
import { styled } from 'components/Theme/main';

type SelectProps = {
    inputStateError: boolean;
};

export const SelectStyled = styled(SelectReact)<SelectProps>`
    ${({ theme, inputStateError }) => css`
        .select__control {
            min-height: 54px;

            border: 2px solid ${theme.color.border};
            border-radius: ${theme.radius.big};
            box-shadow: none;
            cursor: pointer;
            z-index: calc(${theme.zIndex.above} + 1);

            &:hover {
                border: 2px solid ${theme.color.border};
            }

            ${inputStateError === true &&
            css`
                box-shadow: none;
                background-color: ${theme.color.white};
                border-color: ${theme.color.red};

                &:hover {
                    border: 2px solid ${theme.color.red};
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

            svg {
                width: 27px;
                height: 27px;

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
            .select__indicator {
                transform: rotate(180deg);

                svg {
                    color: ${theme.color.primary};
                }
            }
        }
    `}
`;
