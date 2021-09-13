import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

const localVariables = {
    spinboxHeight: '48px',
    spinboxHeightSmall: '30px',
    spinboxButtonWidth: '32px',
    spinboxWidth: '120px',
    spinboxWidthSmall: '100px',
};

export const SpinboxStyled = styled.div`
    ${({ theme }) => css`
        display: inline-flex;
        width: ${localVariables.spinboxWidth};
        height: ${localVariables.spinboxHeight};

        border: 2px solid ${theme.color.border};
        border-radius: ${theme.radius.medium};
        background-color: ${theme.color.white};
        overflow: hidden;
    `}
`;

export const SpinboxSmallStyled = styled(SpinboxStyled)`
    height: ${localVariables.spinboxHeightSmall};
    width: ${localVariables.spinboxWidthSmall};
`;

export const SpinboxInputStyled = styled.input`
    ${({ theme }) => css`
        flex: 1;
        text-align: center;
        padding: 0;
        height: 100%;
        min-width: 0;

        font-size: 18px;
        color: ${theme.color.base};
        font-weight: 700;
        border: 0;
        outline: none;

        /** hides default spinbox */
        &::-webkit-outer-spin-button,
        &::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        &[type='number'] {
            -moz-appearance: textfield;
        }
    `}
`;

export const SpinboxButtonStyled = styled.button`
    ${({ theme }) => css`
        display: flex;
        justify-content: center;
        align-items: center;
        width: ${localVariables.spinboxButtonWidth};
        padding: 0;
        min-height: 0;

        color: ${theme.color.base};
        cursor: pointer;
        font-size: 12px;
        background: none;
        border: 0;
        outline: 0;
    `}
`;
