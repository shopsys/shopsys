import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

const localVariables = {
    labelColorSize: '25px',
} as const;

type ColorLabelWrapperStyledProps = {
    isLightColor: boolean;
    bgColor: string;
    isDisabled: boolean;
    isActive: boolean;
};

export const ColorLabelWrapperStyled = styled.div<ColorLabelWrapperStyledProps>`
    position: relative;
    display: flex;
    width: 25px;
    margin: 0 5px 5px 0;

    input {
        & ~ label {
            ${({ bgColor, isDisabled, isActive }) => css`
                position: relative;
                display: block;
                height: ${localVariables.labelColorSize};
                width: ${localVariables.labelColorSize};

                font-size: 0;
                border: 1px solid hsla(0, 0%, 5%, 0.08);
                border-radius: 100%;
                cursor: pointer;
                background-color: ${bgColor};
                ${isDisabled &&
                !isActive &&
                css`
                    opacity: 0.3;
                    pointer-events: none;
                `}

                &:after {
                    opacity: 0;
                }
            `}
        }

        &:checked ~ label:after {
            ${({ theme, isLightColor }) => css`
                content: '';
                position: absolute;
                height: 10px;
                width: 6px;
                left: 9px;
                top: 6px;
                opacity: 1;
                transform: rotate(45deg);

                border: solid transparent;
                border-width: 0 2px 2px 0;
                pointer-events: none;

                ${isLightColor
                    ? css`
                          border-color: ${theme.color.black};
                      `
                    : css`
                          border-color: ${theme.color.white};
                      `};
            `}
        }

        ${({ theme, isDisabled, isActive, isLightColor }) =>
            isDisabled &&
            !isActive &&
            css`
                & ~ label:after {
                    content: '✕';
                    position: absolute;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);

                    opacity: 1;
                    font-size: 17px;

                    ${isLightColor
                        ? css`
                              color: ${theme.color.black};
                          `
                        : css`
                              color: ${theme.color.white};
                          `};
                }
            `}
    }
`;
