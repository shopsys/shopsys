import { css } from 'styled-components';
import { styled } from 'theme/main';

export const OverlayWrapperStyled = styled.div`
    .overlay-enter {
        opacity: 0;
    }
    .overlay-enter-active {
        opacity: 1;
        transition: all 0.3s ease;
    }
    .overlay-exit {
        opacity: 1;
    }
    .overlay-exit-active {
        opacity: 0;
        transition: all 0.3s ease;
    }
`;

export const OverlayStyled = styled.div`
    ${({ theme }) => css`
        bottom: 0;
        left: 0;
        position: fixed;
        right: 0;
        top: 0;
        z-index: ${theme.zIndex.overlay};

        background-color: rgba(0, 0, 0, 0.6);
    `}
`;
