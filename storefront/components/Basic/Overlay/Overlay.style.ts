import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

type OverlayProps = {
    isHiddenOnDesktop?: boolean;
};

export const OverlayStyled = styled.div<OverlayProps>`
    ${({ theme, isHiddenOnDesktop }) => css`
        bottom: 0;
        left: 0;
        position: fixed;
        right: 0;
        top: 0;
        width: 100%;
        height: 100%;
        z-index: ${theme.zIndex.overlay};
        display: flex;
        justify-content: center;
        align-items: center;

        background-color: rgba(0, 0, 0, 0.6);
        transition: ${theme.transition};
        cursor: pointer;

        ${
            isHiddenOnDesktop &&
            css`
                @media ${theme.mediaQueries.queryVl} {
                    display: none;
                }
            `
        }
        };
    `}
`;
