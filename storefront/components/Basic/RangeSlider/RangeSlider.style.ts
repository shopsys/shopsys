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

type RangeSliderThumbStyledProps = {
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
        z-index: 3;
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
            z-index: 3;
            margin: -6px 0;

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
            z-index: 3;
            margin: -6px 0;

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
            z-index: 3;
            margin: -6px 0;

            border-radius: ${localVariables.RangeSliderThumbBorderRadius};
            background: ${localVariables.RangeSliderThumbColor};
            cursor: pointer;
        }

        &::-webkit-slider-runnable-track,
        &::-moz-range-track,
        &::-ms-track,
        &::-ms-fill-lower,
        &::-ms-fill-upper {
            pointer-events: none;
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
    ${({ theme }) => css`
        position: absolute;
        height: 5px;
        width: 100%;
        z-index: ${theme.zIndex.above};

        background-color: ${localVariables.RangeSliderTrackBackground};
        border-radius: 3px;
    `}
`;

export const RangeSliderRangeStyled = styled.div`
    ${({ theme }) => css`
        position: absolute;
        height: 5px;
        z-index: calc(${theme.zIndex.above} + 1);

        border-radius: 3px;
        background-color: ${localVariables.RangeSliderRangeBackground};
    `}
`;

export const RangeSliderLeftThumbStyled = styled.input<RangeSliderThumbStyledProps>`
    ${getThumbStyle()};
`;

export const RangeSliderRightThumbStyled = styled.input`
    ${getThumbStyle()};
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
