import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const OverlayWrapperStyled = styled.div(
    ({ theme }) => css`
        .overlay-enter {
            opacity: 0;
        }
        .overlay-enter-active {
            opacity: 1;
            transition: opacity ${theme.transition};
        }
        .overlay-exit {
            opacity: 1;
        }
        .overlay-exit-active {
            opacity: 0;
            transition: opacity ${theme.transition};
        }
    `,
);
