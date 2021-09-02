import { css } from 'styled-components';
import { styled } from 'theme/main';

const localVariables = {
    shopsysRangeSliderTrackBackground: '#4c5bfd',
    shopsysRangeSliderThumbTopPosition: '25px',
    shopsysRangeSliderThumbColor: '#ecb200',
    shopsysRangeSliderThumbSize: '16px',
    shopsysRangeSliderThumbBorder: 'none',
} as const;

const getThumbStyle = () => {
    return css`
        position: absolute;
        height: 0;
        width: 100%;
        top: ${localVariables.shopsysRangeSliderThumbTopPosition};
        outline: none;

        /* Special styling for WebKit/Blink */
        &::-webkit-slider-thumb {
            -webkit-appearance: none;
            border: ${localVariables.shopsysRangeSliderThumbBorder};
            height: ${localVariables.shopsysRangeSliderThumbSize};
            width: ${localVariables.shopsysRangeSliderThumbSize};
            border-radius: 50%;
            background-color: ${localVariables.shopsysRangeSliderThumbColor};
            cursor: pointer;
            margin-top: -14px; /* You need to specify a margin in Chrome, but in Firefox and IE it is automatic */
        }

        /* All the same stuff for Firefox */
        &::-moz-range-thumb {
            border: ${localVariables.shopsysRangeSliderThumbBorder};
            height: ${localVariables.shopsysRangeSliderThumbSize};
            width: ${localVariables.shopsysRangeSliderThumbSize};
            border-radius: 50%;
            background-color: ${localVariables.shopsysRangeSliderThumbColor};
            cursor: pointer;
        }

        /* All the same stuff for IE */
        &::-ms-thumb {
            border: ${localVariables.shopsysRangeSliderThumbBorder};
            height: ${localVariables.shopsysRangeSliderThumbSize};
            width: ${localVariables.shopsysRangeSliderThumbSize};
            border-radius: 50%;
            background: ${localVariables.shopsysRangeSliderThumbColor};
            cursor: pointer;
        }
    `;
};

export const ShopsysRangeSliderContainerStyled = styled.div`
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 50px;
    width: 100%;
    padding: 8px;
`;

export const ShopsysRangeSliderStyled = styled.div`
    position: relative;
    width: 100%;
`;

export const ShopsysRangeSliderTrackStyled = styled.div`
    position: absolute;
    height: 5px;
    width: 100%;
    z-index: 1;

    background-color: #ced4da;
    border-radius: 3px;
`;

export const ShopsysRangeSliderRangeStyled = styled.div`
    position: absolute;
    height: 5px;

    z-index: 2;
    border-radius: 3px;
    background-color: ${localVariables.shopsysRangeSliderTrackBackground};
`;

export const ShopsysRangeSliderLeftThumbStyled = styled.input`
    ${getThumbStyle()};
    z-index: 4;
`;

export const ShopsysRangeSliderRightThumbStyled = styled.input`
    ${getThumbStyle()};
    z-index: 3;
`;

export const ShopsysRangeSliderLeftValueStyled = styled.div`
    position: absolute;
    margin-top: 20px;
    left: 6px;

    color: #000;
    font-size: 12px;
`;

export const ShopsysRangeSliderRightValueStyled = styled.div`
    position: absolute;
    margin-top: 20px;
    right: -4px;

    color: #000;
    font-size: 12px;
`;
