import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

type ContactInformationTextWrapperStyledProps = {
    isEmailEntered: boolean;
};

export const ContactInformationTextWrapperStyled = styled.div<ContactInformationTextWrapperStyledProps>(
    ({ isEmailEntered }) =>
        !isEmailEntered &&
        css`
            opacity: 0.5;
            pointer-events: none;
        `,
);

export const ContactInformationTextStyled = styled.p(
    ({ theme }) => css`
        margin-bottom: 16px;

        font-size: ${theme.fontSize.default};
    `,
);
