import { css } from 'styled-components';
import { styled } from '../../../Theme/main';

const localVariables = {
    flagItemDefaultBg: '#cdb3ff',
} as const;

type ProductFlagsItemStyledProps = {
    color?: string;
};

export const ProductFlagsStyled = styled.div`
    position: absolute;
    display: flex;
    flex-direction: column;
    top: 10px;
    left: 14px;

    font-size: 0;
`;

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
        background-color: ${color || localVariables.flagItemDefaultBg};

        &:hover {
            color: ${theme.color.black};
            text-decoration: none;
        }
    `}
`;
