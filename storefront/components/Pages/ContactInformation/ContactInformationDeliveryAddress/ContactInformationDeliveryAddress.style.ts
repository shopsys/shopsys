import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

type ContactInformationDeliveryAddressProps = {
    contentElementHeight: number;
};

export const ContactInformationDeliveryAddressStyled = styled.div<ContactInformationDeliveryAddressProps>`
    ${({ contentElementHeight }) => css`
        .contactInformationDeliveryAddress-enter {
            height: 0;
        }

        .contactInformationDeliveryAddress-enter-active {
            height: ${contentElementHeight}px;
            transition: 0.5s all ease;
        }

        .contactInformationDeliveryAddress-exit {
            height: ${contentElementHeight}px;
        }

        .contactInformationDeliveryAddress-exit-active {
            height: 0;
            transition: 0.5s all ease;
        }
    `}
`;

export const ContactInformationDeliveryAddressContentStyled = styled.div`
    padding-bottom: 40px;
`;
