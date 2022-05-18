import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const ContactTextStyled = styled.p`
    ${({ theme }) => css`
        margin-bottom: 16px;

        font-size: ${theme.fontSize.default};
    `}
`;
