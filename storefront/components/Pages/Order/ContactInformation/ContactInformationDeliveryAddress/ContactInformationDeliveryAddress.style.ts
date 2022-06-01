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

export const ListStyled = styled.div`
    display: flex;
    flex-direction: column;
    width: 100%;
`;

export const ListItemStyled = styled.div(
    ({ theme }) => css`
        position: relative;
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        width: 100%;
        margin-top: 16px;
        padding: 20px;

        border-radius: ${theme.radius.big};
        border: 2px solid ${theme.color.border};

        strong {
            margin-right: 5px;
        }
    `,
);
