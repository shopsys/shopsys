import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const UserConsentContainerStyled = styled.div(
    ({ theme }) => css`
        position: fixed;
        width: 100%;
        left: 0;
        bottom: 0;
        z-index: ${theme.zIndex.maximum};
        display: flex;
        justify-content: right;
    `,
);

export const UserConsentStyled = styled.div(
    ({ theme }) => css`
        width: calc(100vw - 32px);
        max-width: 480px;
        position: absolute;
        right: 16px;
        bottom: 10px;
        padding: 20px;

        background-color: ${theme.color.creamWhite};
        border: 4px solid ${theme.color.primaryLight};
        box-shadow: 0 0 15px #505050;
        border-radius: ${theme.radius.biggest};
    `,
);
