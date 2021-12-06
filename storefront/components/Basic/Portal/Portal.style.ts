import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

export const PortalContainer = styled.div`
    ${({ theme }) => css`
        position: absolute;
        left: 0;
        top: 0;
        width: 1px;
        height: 1px;
        z-index: ${theme.zIndex.overlay};
    `}
`;
