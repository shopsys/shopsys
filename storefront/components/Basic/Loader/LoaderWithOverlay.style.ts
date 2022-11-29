import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const LoadingOverlayStyled = styled.div(
    ({ theme }) => css`
        position: absolute;
        z-index: ${theme.zIndex.overlay};
        inset: 0;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;

        background-color: ${theme.color.greyLighter};
        opacity: 50%;
    `,
);
