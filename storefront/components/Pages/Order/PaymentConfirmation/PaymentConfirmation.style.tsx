import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const MessageWrapperStyled = styled.div`
    ${({ theme }) => css`
        align-items: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
        margin: 64px 0 40px;

        @media ${theme.mediaQueries.queryLg} {
            flex-direction: row;
            margin: 70px 0 90px;
        }
    `}
`;

export const ImageWrapperStyled = styled.div`
    ${({ theme }) => css`
        width: 160px;
        margin-bottom: 0;

        @media ${theme.mediaQueries.queryLg} {
            margin-right: 125px;
        }
    `}
`;

export const PaymentWrapperStyled = styled.div`
    ${({ theme }) => css`
        align-items: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
        margin: 64px 0 40px;

        @media ${theme.mediaQueries.queryLg} {
            flex-direction: row;
            margin: 70px 0 90px;
        }
    `}
`;

export const MessageStyled = styled.div`
    ${({ theme }) => css`
        text-align: center;

        @media ${theme.mediaQueries.queryLg} {
            text-align: left;
        }
    `}
`;
