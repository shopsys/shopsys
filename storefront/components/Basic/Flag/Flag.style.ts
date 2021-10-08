import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

type FlagStyledProps = {
    color?: string;
};

export const FlagStyled = styled.a<FlagStyledProps>`
    ${({ theme, color }) => css`
        display: inline-flex;
        line-height: 11px;
        margin-bottom: 7px;
        margin-right: 10px;
        padding: 5px 7px 4px;

        background-color: ${color !== undefined ? color : theme.color.white};
        text-transform: uppercase;
        text-decoration: none;
        letter-spacing: 0.24px;
        font-size: 11px;
        border-radius: ${theme.radius.small};
        color: ${theme.color.base};

        &:hover {
            color: ${theme.color.base};
            text-decoration: none;
        }
    `}
`;
