import { css } from 'styled-components';
import { styled } from 'components/Theme/main';
import tinycolor from 'tinycolor2';

type NotificationBarsStyledProps = {
    backgroundColor: string;
};

export const NotificationBarsStyled = styled.div<NotificationBarsStyledProps>`
    ${({ backgroundColor }) => css`
        padding: 6px 0;

        background-color: ${backgroundColor};
    `}
`;

export const NotificationBarsBlockStyled = styled.div<NotificationBarsStyledProps>`
    ${({ theme, backgroundColor }) => css`
        display: flex;
        justify-content: center;
        align-items: center;
        line-height: 24px;
        text-align: center;

        font-weight: 700;
        font-size: 13px;
        color: ${tinycolor(backgroundColor).isLight() ? theme.color.base : theme.color.white};

        img {
            margin-right: 10px;
        }
    `}
`;

export const NotificationBarsImageStyled = styled.div`
    display: flex;
    min-width: 42px;
    margin-right: 10px;
`;
