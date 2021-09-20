import { styled, Theme } from 'components/Theme/main';
import { css } from 'styled-components';

type ButtonSize = 'small';

type ButtonPropsStyled = {
    size?: ButtonSize;
    borderRadius?: 'big';
    isDisabled?: boolean;
};

type ButtonAsLinkProps = {
    isDisabled?: boolean;
};

const localVariables = {
    btnPaddingVertical: '10px',
    btnPaddingHorizontal: '32px',
    btnBackgroundColorHover: '#dea700', // darken version of theme.color.orange
    btnSmallPaddingVertical: '3px',
    btnPrimaryBackgroundColorHover: '#3b4cfc', // darken version of theme.color.primary
};

const getSize = (theme: Theme, size?: ButtonSize) => {
    switch (size) {
        case 'small':
            return css`
                padding: ${localVariables.btnSmallPaddingVertical} 17px ${localVariables.btnSmallPaddingVertical};
                min-height: 30px;
                line-height: 23px;

                font-size: ${theme.fontSize.small};
            `;
        default:
            return css`
                padding: ${localVariables.btnPaddingVertical} ${localVariables.btnPaddingHorizontal};
                min-height: ${theme.btnHeight};
                line-height: 27px;

                font-size: ${theme.fontSize.default};
            `;
    }

    throw new Error('Wrong size provided for Button.');
};

export const ButtonStyled = styled.button<ButtonPropsStyled>`
    ${({ theme, size, borderRadius, isDisabled }) => css`
        ${getSize(theme, size)};
        width: auto;
        vertical-align: middle;
        display: inline-block;
        transition: ${theme.transition} background-color, ${theme.transition} color;
        text-align: center;

        border: 0;
        border-radius: ${borderRadius === 'big' ? theme.radius.big : theme.radius.medium};
        color: ${theme.color.white};
        background-color: ${theme.color.orange};
        cursor: pointer;
        text-decoration: none;
        font-weight: 700;
        outline: 0;
        text-transform: uppercase;

        ${isDisabled &&
        css`
            opacity: 0.5;
            cursor: no-drop;
            pointer-events: none;
        `}

        &:hover {
            color: ${theme.color.white};
            background-color: ${localVariables.btnBackgroundColorHover};
            text-decoration: none;
        }
    `}
`;

export const ButtonPrimaryStyled = styled(ButtonStyled)`
    ${({ theme }) => css`
        color: ${theme.color.white};
        background-color: ${theme.color.primary};

        &:hover {
            color: ${theme.color.white};
            background-color: ${localVariables.btnPrimaryBackgroundColorHover};
        }
    `}
`;

export const ButtonSecondaryStyled = styled(ButtonStyled)`
    ${({ theme }) => css`
        color: ${theme.color.black};
        background-color: ${theme.color.orangeLight};

        &:hover {
            color: ${theme.color.black};
            background-color: ${theme.color.white};
        }
    `}
`;

export const ButtonAsLinkStyled = styled.button<ButtonAsLinkProps>`
    ${({ theme, isDisabled }) => css`
        padding: 0;
        min-height: 0;

        border: 0;
        background: none;
        outline: 0;
        color: ${theme.color.black};
        cursor: pointer;

        ${isDisabled &&
        css`
            opacity: 0.5;
            cursor: no-drop;
            pointer-events: none;
        `}

        &:hover {
            background: none;
            color: ${theme.color.primary};
            text-decoration: underline;
        }
    `}
`;
