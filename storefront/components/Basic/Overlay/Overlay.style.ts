import { OverlayProps } from './propTypes';
import { styled, Theme } from 'components/Theme/main';
import { css, CSSProperties, FlattenSimpleInterpolation } from 'styled-components';

const localVariables = {
    backgroundColor: 'rgba(0, 0, 0, 0.6)',
} as const;

export type OverlayStyledProps = OverlayProps & {
    theme: Theme;
    $zIndex?: CSSProperties['zIndex'];
};

export const overlayStyle = ({
    theme,
    $zIndex = theme.zIndex.overlay,
    $isActive = true,
    $isHiddenOnDesktop = false,
}: OverlayStyledProps): FlattenSimpleInterpolation => css`
    align-items: center;
    bottom: 0;
    display: flex;
    inset: 0;
    justify-content: center;
    position: fixed;
    z-index: ${$zIndex};

    background-color: ${localVariables.backgroundColor};
    cursor: pointer;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s cubic-bezier(0.8, 0.2, 0.48, 1);

    ${!!$isActive &&
    css`
        opacity: 1;
        /* pointer-events: auto; */
    `}

    ${!!$isHiddenOnDesktop &&
    css`
        @media ${theme.mediaQueries.queryVl} {
            display: none;
        }
    `}
`;

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

export const OverlayStyled = styled.div<OverlayStyledProps>(
    ({ theme, $zIndex, $isActive, $isHiddenOnDesktop }) => css`
        ${overlayStyle({ theme, $zIndex, $isActive, $isHiddenOnDesktop })}
    `,
);
