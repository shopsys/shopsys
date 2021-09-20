import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

const localVariables = {
    flagItemDefaultBg: '#cdb3ff',
} as const;

type ProductFlagsItemStyledProps = {
    color?: string;
};

export const ProductFlagsItemStyled = styled.div<ProductFlagsItemStyledProps>`
    ${({ theme, color }) => css`
        display: inline-flex;
        margin-bottom: 2px;
        margin-right: auto;
        padding: 3px 7px 2px;
        line-height: 11px;

        font-size: 11px;
        letter-spacing: 0.24px;
        text-transform: uppercase;
        border-radius: ${theme.radius.small};
        color: ${theme.color.black};
        text-decoration: none;
        background-color: ${color !== undefined ? color : localVariables.flagItemDefaultBg};

        &:hover {
            color: ${theme.color.black};
            text-decoration: none;
        }
    `}
`;
