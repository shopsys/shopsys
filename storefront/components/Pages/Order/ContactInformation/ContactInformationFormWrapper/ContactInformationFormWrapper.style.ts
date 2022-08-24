import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

type ContactInformationFormWrapperStyledProps = {
    contentElementHeight: number;
};

export const ContactInformationFormWrapperStyled = styled.div<ContactInformationFormWrapperStyledProps>`
    ${({ contentElementHeight }) => css`
        .contactInformationFormWrapper-enter {
            height: 0;
            overflow: hidden;
        }

        .contactInformationFormWrapper-enter-active {
            height: ${contentElementHeight}px;
            transition: 0.5s all ease;
            overflow: hidden;
        }

        .contactInformationFormWrapper-exit {
            height: ${contentElementHeight}px;
            overflow: hidden;
        }

        .contactInformationFormWrapper-exit-active {
            height: 0;
            transition: 0.5s all ease;
            overflow: hidden;
        }
    `}
`;

export const ContactInformationFormWrapperSectionStyled = styled.div`
    margin-bottom: 40px;
`;
