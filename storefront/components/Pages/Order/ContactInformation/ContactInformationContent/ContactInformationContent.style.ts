import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

type ContactInformationProps = {
    contentElementHeight: number;
};

export const ContactInformationContentStyled = styled.div<ContactInformationProps>`
    ${({ contentElementHeight }) => css`
        .contactInformationContent-enter {
            height: 0;
            overflow: hidden;
        }

        .contactInformationContent-enter-active {
            height: ${contentElementHeight}px;
            transition: 0.5s all ease;
            overflow: hidden;
        }

        .contactInformationContent-exit {
            height: ${contentElementHeight}px;
            overflow: hidden;
        }

        .contactInformationContent-exit-active {
            height: 0;
            transition: 0.5s all ease;
            overflow: hidden;
        }
    `}
`;

export const ContactInformationContentSectionStyled = styled.div`
    margin-bottom: 40px;
`;
