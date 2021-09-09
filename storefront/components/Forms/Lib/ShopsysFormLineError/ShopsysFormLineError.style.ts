import { css } from 'styled-components';
import { styled } from '../../../Theme/main';

const localVariables = {
    smallTextInputErrorIconTopOffset: '-29px',
    defaultTextInputErrorIconTopOffset: '-33px',
} as const;

type StyledShopsysErrorIconProps = {
    inputType: 'textarea' | 'text-input' | 'checkbox';
    textInputSize: 'default' | 'small';
};

export const StyledShopsysFormFieldError = styled.div`
    position: relative;
    margin-top: 6px;
`;

export const StyledShopsysErrorMessage = styled.span`
    ${({ theme }) => css`
        line-height: 21px;
        color: ${theme.color.red};
        font-size: ${theme.fontSize.small};
    `}
`;

export const StyledShopsysErrorIcon = styled.div<StyledShopsysErrorIconProps>`
    ${({ inputType, textInputSize }: StyledShopsysErrorIconProps) => css`
        display: flex;
        position: absolute;

        ${inputType === 'textarea' &&
        css`
            top: 2px;
            right: 0;
        `}

        ${inputType === 'text-input' &&
        css`
            transform: translateY(-50%);
            top: ${textInputSize === 'small'
                ? localVariables.smallTextInputErrorIconTopOffset
                : localVariables.defaultTextInputErrorIconTopOffset};
            right: 19px;
        `}

        ${inputType === 'checkbox' &&
        css`
            top: 2px;
            right: -19px;
        `}
    `}
`;
