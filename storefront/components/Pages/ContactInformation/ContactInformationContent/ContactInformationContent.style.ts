import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

type ContactInformationProps = {
    contentElementHeight: number;
};

export const ContactInformationContentStyled = styled.div<ContactInformationProps>`
    ${({ contentElementHeight }) => css`
        .contactInformationContent-enter {
            height: 0;
        }

        .contactInformationContent-enter-active {
            height: ${contentElementHeight}px;
            transition: 0.5s all ease;
        }

        .contactInformationContent-exit {
            height: ${contentElementHeight}px;
        }

        .contactInformationContent-exit-active {
            height: 0;
            transition: 0.5s all ease;
        }
    `}
`;

export const ContactInformationContentWrapperStyled = styled.div`
    overflow: hidden;
`;
