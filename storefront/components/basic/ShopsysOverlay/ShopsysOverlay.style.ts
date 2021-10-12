import { css } from 'styled-components';
import { styled } from 'theme/main';

type OverlayProps = {
    isHiddenOnDesktop?: boolean;
};

export const ShopsysOverlayStyled = styled.div<OverlayProps>`
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
        transition: all 0.2s cubic-bezier(0.8, 0.2, 0.48, 1);
        cursor: pointer;

        ${isHiddenOnDesktop
            ? css`
                  @media ${theme.mediaQueries.queryVl} {
                      display: none;
                  }
              `
            : null};
    `}
`;
