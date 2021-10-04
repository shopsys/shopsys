import { css } from 'styled-components';
import { styled } from '../../Theme/main';

export const ContactInformationStyled = styled.div`
    ${({ theme }) => css`
        margin-bottom: 20px;

        color: ${theme.color.greyLight};
        font-size: 13px;
    `}
`;
