import { css } from 'styled-components';
import { styled } from '../../../Theme/main';

export const ProductAvailabilityStyled = styled.div`
    ${({ theme }) => css`
        line-height: 18px;
        margin-bottom: 10px;

        font-size: 13px;
        color: ${theme.color.black};
    `}
`;
