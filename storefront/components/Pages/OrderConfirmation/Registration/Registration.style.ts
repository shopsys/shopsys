import { Heading } from 'components/Basic/Heading/Heading';
import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const RegistrationStyled = styled.div`
    ${({ theme }) => css`
        flex-direction: row;
        display: flex;
        margin-bottom: 84px;
        position: relative;

        border: 3px solid ${theme.color.primary};
        border-radius: 11px;

        &::before {
            display: block;
            bottom: 0;
            content: '';
            left: 50%;
            position: absolute;
            top: 0;
            transform: translateX(-50%);
            width: 3px;

            background-color: ${theme.color.primary};
        }

        @media ${theme.mediaQueries.queryTablet} {
            flex-direction: column;

            &::before {
                display: none;
            }
        }
    `}
`;

export const RegistrationMessageColumnStyled = styled.div`
    ${({ theme }) => css`
        padding: 30px 40px;
        width: 50%;

        @media ${theme.mediaQueries.queryTablet} {
            padding: 20px;
            width: 100%;
        }
    `}
`;

export const RegistrationFormColumnStyled = styled.div`
    ${({ theme }) => css`
        align-items: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 30px 40px;
        width: 50%;

        @media ${theme.mediaQueries.queryTablet} {
            padding: 20px;
            width: 100%;
        }
    `}
`;

export const RegistrationFormStyled = styled.div`
    ${({ theme }) => css`
        width: 100%;

        @media ${theme.mediaQueries.queryLg} {
            max-width: 370px;
        }
    `}
`;

export const RegistrationFormItemStyled = styled.div`
    margin-bottom: 30px;
`;

export const RegistrationHeadingStyled = styled(Heading)`
    ${({ theme }) => css`
        line-height: 43px;
        margin-bottom: 21px;

        font-size: 36px;

        strong {
            color: ${theme.color.primary};
        }
    `}
`;

export const RegistrationBenefitsListItem = styled.li`
    line-height: 21px;
    margin-bottom: 12px;
    padding-left: 15px;
    position: relative;

    font-size: 16px;

    &::before {
        content: '';
        height: 5px;
        left: 0;
        position: absolute;
        top: 8px;
        width: 5px;

        border-radius: 100%;
        background-color: #4c5bfd;
    }
`;
