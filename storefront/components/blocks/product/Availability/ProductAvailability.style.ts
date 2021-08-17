import styled, { css } from 'styled-components';
import { Theme } from 'theme/main';

export const ProductAvailabilityStyled = styled.div`
    ${({ theme }: { theme: Theme }) => css`
        line-height: 18px;
        margin-bottom: 10px;

        font-size: 13px;
        color: ${theme.color.black};
    `}
`;
