import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

const localVariables = {
    RangeSliderRangeBackground: '#4c5bfd',
    RangeSliderTrackBackground: '#ced4da',
    RangeSliderThumbTopPosition: '25px',
    RangeSliderThumbColor: '#ecb200',
    RangeSliderThumbSize: '16px',
    RangeSliderThumbBorder: 'none',
    RangeSliderThumbBorderRadius: '50%',
} as const;

type RangeSliderThumbPropsStyled = {
    value: number;
    max: number;
};

const getThumbStyle = () => {
    return css`
        -webkit-appearance: none;
        -webkit-tap-highlight-color: transparent;
        position: absolute;
        height: 0;
        width: 100%;
        top: ${localVariables.RangeSliderThumbTopPosition};
        outline: none;

        /* Special styling for WebKit/Blink */
        &::-webkit-slider-thumb {
            -webkit-appearance: none;
            -webkit-tap-highlight-color: transparent;
            position: relative;
            pointer-events: all;
            height: ${localVariables.RangeSliderThumbSize};
            width: ${localVariables.RangeSliderThumbSize};

            border: ${localVariables.RangeSliderThumbBorder};
            border-radius: ${localVariables.RangeSliderThumbBorderRadius};
            background-color: ${localVariables.RangeSliderThumbColor};
            cursor: pointer;
        }

        /* All the same stuff for Firefox */
        &::-moz-range-thumb {
            position: relative;
            pointer-events: all;
            height: ${localVariables.RangeSliderThumbSize};
            width: ${localVariables.RangeSliderThumbSize};

            border: ${localVariables.RangeSliderThumbBorder};
            border-radius: ${localVariables.RangeSliderThumbBorderRadius};
            background-color: ${localVariables.RangeSliderThumbColor};
            cursor: pointer;
        }

        /* All the same stuff for IE */
        &::-ms-thumb {
            border: ${localVariables.RangeSliderThumbBorder};
            height: ${localVariables.RangeSliderThumbSize};
            width: ${localVariables.RangeSliderThumbSize};
            border-radius: ${localVariables.RangeSliderThumbBorderRadius};
            background: ${localVariables.RangeSliderThumbColor};
            cursor: pointer;
        }
    `;
};

export const RangeSliderContainerStyled = styled.div`
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 50px;
    width: 100%;
    padding: 8px;
    margin-bottom: 40px;
    margin-top: -20px;
`;

export const RangeSliderStyled = styled.div`
    position: relative;
    width: 100%;
`;

export const RangeSliderTrackStyled = styled.div`
    position: absolute;
    height: 5px;
    width: 100%;
    z-index: 1;

    background-color: ${localVariables.RangeSliderTrackBackground};
    border-radius: 3px;
`;

export const RangeSliderRangeStyled = styled.div`
    position: absolute;
    height: 5px;
    z-index: 2;

    border-radius: 3px;
    background-color: ${localVariables.RangeSliderRangeBackground};
`;

export const RangeSliderLeftThumbStyled = styled.input<RangeSliderThumbPropsStyled>`
    ${({ value, max }) => css`
        ${getThumbStyle()};
        z-index: 4;

        ${value > max - 100 &&
        css`
            z-index: 5;
        `}
    `};
`;

export const RangeSliderRightThumbStyled = styled.input`
    ${getThumbStyle()};
    z-index: 3;
`;

export const RangeSliderLeftValueStyled = styled.div`
    ${({ theme }) => css`
        position: absolute;
        margin-top: 20px;
        left: -8px;
        width: 80px;

        color: ${theme.color.black};
        font-size: 12px;
    `};
`;

export const RangeSliderRightValueStyled = styled.div`
    ${({ theme }) => css`
        position: absolute;
        margin-top: 20px;
        right: -8px;
        width: 80px;

        color: ${theme.color.black};
        font-size: 12px;
    `};
`;
