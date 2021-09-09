import { styled, Theme } from '../../Theme/main';
import { css } from 'styled-components';

type StyledWeblineType = 'default' | 'colored' | 'dark' | 'light';

type StyledWeblineProps = {
    type: StyledWeblineType;
};

const getWeblineType = (type: StyledWeblineType, theme: Theme) => {
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
        default:
            return '';
    }
};

export const StyledWebline = styled.div<StyledWeblineProps>`
    ${({ type, theme }: { type: StyledWeblineType; theme: Theme }) => css`
        ${getWeblineType(type, theme)};
    `}
`;

export const StyledContainer = styled.div`
    ${({ theme }) => css`
        padding: 0 ${theme.layout.padding};

        @media ${theme.mediaQueries.queryXl} {
            width: ${theme.layout.width};
            margin: 0 auto;
        }
    `}
`;
