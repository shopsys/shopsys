import { styled, Theme } from 'components/Theme/main';
import { css } from 'styled-components';

type ButtonSize = 'small';

type ButtonProps = {
    variant?: 'default' | 'primary' | 'secondary';
    size?: 'small';
    borderRadius?: 'big';
};

const localVariables = {
    btnPaddingVertical: '10px',
    btnPaddingHorizontal: '32px',
    btnBackgroundColorHover: '#dea700',
    btnSmallPaddingVertical: '3px',
    btnPrimaryBackgroundColorHover: '#3b4cfc',
};

export const getSize = (theme: Theme, size?: ButtonSize) => {
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

export const LinkStyled = styled.a`
    ${({ theme }) => css`
        display: inline-flex;
        align-items: center;

        color: ${theme.color.greyDark};
        text-decoration: underline;
        cursor: pointer;
        outline: none;
        background-color: transparent;

        &:hover {
            color: ${theme.color.primary};
        }

        img {
            margin-right: 15px;

            font-size: 0;
        }
    `}
`;

export const ButtonStyled = styled.a<ButtonProps>`
    ${({ theme, size, variant, borderRadius }) => css`
        ${getSize(theme, size)};
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

        &:hover {
            color: ${theme.color.white};
            background-color: ${localVariables.btnBackgroundColorHover};
            text-decoration: none;
        }

        ${variant === 'primary' &&
        css`
            color: ${theme.color.white};
            background-color: ${theme.color.primary};

            &:hover {
                color: ${theme.color.white};
                background-color: ${localVariables.btnPrimaryBackgroundColorHover};
            }
        `}

        ${variant === 'secondary' &&
        css`
            color: ${theme.color.black};
            background-color: ${theme.color.orangeLight};

            &:hover {
                color: ${theme.color.black};
                background-color: ${theme.color.white};
            }
        `}
    `}
`;
