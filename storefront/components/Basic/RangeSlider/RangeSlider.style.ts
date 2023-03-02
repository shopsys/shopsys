import { styled } from 'components/Theme/main';
import { HTMLAttributes } from 'react';
import { css } from 'styled-components';

const localVariables = {
    RangeSliderRangeBackground: '#4c5bfd',
    RangeSliderTrackBackground: '#ced4da',
    RangeSliderThumbTopPosition: '25px',
    RangeSliderThumbColor: '#ecb200',
    RangeSliderThumbSize: '16px',
    RangeSliderThumbBorder: 'none',
    RangeSliderThumbBorderRadius: '50%',
} as const;

type RangeSliderThumbStyledProps = HTMLAttributes<HTMLInputElement> & {
    active: boolean;
};

export const RangeSliderThumbStyled = styled.input<RangeSliderThumbStyledProps>(
    ({ active, theme }) => css`
        -webkit-appearance: none;
        pointer-events: none;
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
            background-color: ${active ? localVariables.RangeSliderThumbColor : theme.color.greyLight};
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
            background-color: ${active ? localVariables.RangeSliderThumbColor : theme.color.greyLight};
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
            background: ${active ? localVariables.RangeSliderThumbColor : theme.color.greyLight};
            cursor: pointer;
        }

        &::-webkit-slider-runnable-track,
        &::-moz-range-track,
        &::-ms-track,
        &::-ms-fill-lower,
        &::-ms-fill-upper {
            pointer-events: none;
        }
    `,
);
