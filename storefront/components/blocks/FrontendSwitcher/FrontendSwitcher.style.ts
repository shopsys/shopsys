import { css } from 'styled-components';
import { styled } from 'theme/main';

export const FrontendSwitcherWrapper = styled.div`
    ${() => css`
        position: fixed;
        right: 0;
        top: 15vh;
        box-shadow: -4px 0 14px 0 rgba(0, 0, 0, 0.73);
        padding: 5px;
        border-radius: 8px 0 0 8px;
        z-index: 999;
        background-color: greenyellow;
    `}
`;
