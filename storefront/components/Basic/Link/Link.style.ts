import { ButtonDefaultProps } from 'components/Forms/Button/types';
import { buttonSettings } from 'components/Forms/Button/Button.style';
import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

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

export const ButtonStyled = styled.a<ButtonDefaultProps>`
    ${({ theme, size, variant, borderRadius }) => css`
        ${buttonSettings(theme, size, variant, borderRadius)}
        display: inline-block;
        transition: ${theme.transition} background-color, ${theme.transition} color;
        text-align: center;

        border: 0;
        cursor: pointer;
        text-decoration: none;
        font-weight: 700;
        outline: 0;
        text-transform: uppercase;

        &:hover {
            text-decoration: none;
        }
    `}
`;
