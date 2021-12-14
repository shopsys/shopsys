import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

type ContactInformationTextWrapperStyledProps = {
    isEmailEntered: boolean;
};

export const ContactInformationTextWrapperStyled = styled.div<ContactInformationTextWrapperStyledProps>`
    ${({ isEmailEntered }) => css`
        ${!isEmailEntered &&
        css`
            opacity: 0.5;
            pointer-events: none;
        `}
    `}
`;

export const ContactInformationTextStyled = styled.p`
    ${({ theme }) => css`
        margin-bottom: 16px;

        font-size: ${theme.fontSize.default};
    `}
`;
