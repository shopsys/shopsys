import { ButtonDefaultPropType } from './propTypes';
import { styled, Theme } from 'components/Theme/main';
import { css } from 'styled-components';

type ButtonStyledProps = ButtonDefaultPropType & {
    isDisabled?: boolean;
    hasDisabledLook?: boolean;
    isLink?: boolean;
};

type ButtonAsLinkStyledProps = {
    isDisabled?: boolean;
    hasDisabledLook?: boolean;
};

export const buttonSettings = (
    theme: Theme,
    size?: ButtonDefaultPropType['size'],
    variant?: ButtonDefaultPropType['variant'],
    borderRadius?: ButtonDefaultPropType['borderRadius'],
): any => {
    const buttonSize = size === undefined ? 'default' : size;
    const buttonVariant = variant === undefined ? 'default' : variant;
    const buttonBorderRadius = borderRadius === undefined ? 'default' : borderRadius;

    return css`
        padding: ${theme.button.size[buttonSize].paddingVertical} ${theme.button.size[buttonSize].paddingHorizontal};
        min-height: ${theme.button.size[buttonSize].height};
        line-height: ${theme.button.size[buttonSize].lineHeight};

        color: ${theme.button.variant[buttonVariant].color};
        background-color: ${theme.button.variant[buttonVariant].background};
        font-size: ${theme.button.size[buttonSize].fontSize};
        border-radius: ${theme.button.borderRadius[buttonBorderRadius]};

        &:hover {
            color: ${theme.button.variant[buttonVariant].colorHover};
            background-color: ${theme.button.variant[buttonVariant].backgroundHover};
        }
    `;
};

export const ButtonStyled = styled.button<ButtonStyledProps>`
    ${({ theme, size, variant, borderRadius, isDisabled, hasDisabledLook }) => css`
        ${buttonSettings(theme, size, variant, borderRadius)}
        width: auto;
        vertical-align: middle;
        display: inline-block;
        transition: ${theme.transition} background-color, ${theme.transition} color;
        text-align: center;

        border: 0;
        cursor: pointer;
        text-decoration: none;
        font-weight: 700;
        outline: 0;
        text-transform: uppercase;

        ${(isDisabled || hasDisabledLook) &&
        css`
            opacity: 0.5;
            cursor: no-drop;
        `}

        ${isDisabled &&
        css`
            pointer-events: none;
        `}
    `}
`;

export const ButtonAsLinkStyled = styled.button<ButtonAsLinkStyledProps>`
    ${({ theme, isDisabled, hasDisabledLook }) => css`
        padding: 0;
        min-height: 0;

        border: 0;
        background: none;
        outline: 0;
        color: ${theme.color.black};
        cursor: pointer;

        ${(isDisabled || hasDisabledLook) &&
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
