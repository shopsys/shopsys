import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

type ContactInformationDeliveryAddressStyledProps = {
    contentElementHeight: number;
};

export const ContactInformationDeliveryAddressStyled = styled.div<ContactInformationDeliveryAddressStyledProps>`
    ${({ contentElementHeight }) => css`
        .contactInformationDeliveryAddress-enter {
            height: 0;
            overflow: hidden;
        }

        .contactInformationDeliveryAddress-enter-active {
            height: ${contentElementHeight}px;
            transition: 0.5s all ease;
            overflow: hidden;
        }

        .contactInformationDeliveryAddress-exit {
            height: ${contentElementHeight}px;
            overflow: hidden;
        }

        .contactInformationDeliveryAddress-exit-active {
            height: 0;
            transition: 0.5s all ease;
            overflow: hidden;
        }
    `}
`;

export const ContactInformationDeliveryAddressContentStyled = styled.div`
    padding-bottom: 40px;
`;
