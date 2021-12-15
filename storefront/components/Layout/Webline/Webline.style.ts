import { styled, Theme } from 'components/Theme/main';
import { css } from 'styled-components';
import { WeblinePropType } from './propTypes';

type WeblineStyledProps = WeblinePropType;

const getWeblineType = (type: WeblinePropType['type'], theme: Theme) => {
    switch (type) {
        case 'colored':
            return css`
                background-color: ${theme.color.primary};
            `;
        case 'dark':
            return css`
                background-color: ${theme.color.greyDark};
            `;
        case 'light':
            return css`
                background-color: ${theme.color.orangeLight};
            `;
        case 'blog':
            return css`
                background: url('/images/blog-background.png') center/cover no-repeat;
            `;
        default:
            return '';
    }
};

export const WeblineStyled = styled.div<WeblineStyledProps>`
    ${({ type, theme }) => css`
        ${getWeblineType(type, theme)};
    `}
`;

export const ContainerStyled = styled.div`
    ${({ theme }) => css`
        padding: 0 ${theme.layout.padding};

        @media ${theme.mediaQueries.queryXl} {
            width: ${theme.layout.width};
            margin: 0 auto;
        }
    `}
`;
