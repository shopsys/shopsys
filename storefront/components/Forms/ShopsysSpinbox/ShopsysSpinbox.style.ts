import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

const localVariables = {
    shopsysSpinboxHeight: '48px',
    shopsysSpinboxHeightSmall: '30px',
    shopsysSpinboxButtonWidth: '32px',
    shopsysSpinboxWidth: '120px',
    shopsysSpinboxWidthSmall: '84px',
};

export const ShopsysSpinboxStyled = styled.div`
    ${({ theme }) => css`
        display: inline-flex;
        width: ${localVariables.shopsysSpinboxWidth};
        height: ${localVariables.shopsysSpinboxHeight};

        border: 2px solid ${theme.color.border};
        border-radius: ${theme.radius.medium};
        background-color: ${theme.color.white};
        overflow: hidden;
    `}
`;

export const ShopsysSpinboxSmallStyled = styled(ShopsysSpinboxStyled)`
    height: ${localVariables.shopsysSpinboxHeightSmall};
    width: ${localVariables.shopsysSpinboxWidthSmall};
`;

export const ShopsysSpinboxInputStyled = styled.input`
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
    `}
`;

export const ShopsysSpinboxButtonStyled = styled.button`
    ${({ theme }) => css`
        display: flex;
        justify-content: center;
        align-items: center;
        width: ${localVariables.shopsysSpinboxButtonWidth};
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
