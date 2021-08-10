import styled, { css } from 'styled-components';
import { Theme } from 'theme/main';

export const NewsletterFormWrapper = styled.div`
    ${({ theme }: { theme: Theme }) => css`
        display: flex;
        flex-direction: column;
        padding: 33px 0 26px;
        position: relative;

        &::before {
            background: url('/images/lines.png') 0 no-repeat;
            bottom: 0;
            content: '';
            height: 133px;
            left: -20px;
            position: absolute;
            transform: translateX(-100%);
            width: 106px;
        }

        h2 {
            flex: 1;
        }

        @media ${theme.mediaQueries.queryLg} {
            flex-direction: row;
            align-items: center;

            h2 {
                padding-right: 20px;
            }
        }
    `}
`;

export const NewsletterFormColumn = styled.div`
    ${({ theme }: { theme: Theme }) => css`
        width: 100%;

        @media ${theme.mediaQueries.queryMobileXs} {
            form {
                margin-top: 15px;
            }
        }

        @media ${theme.mediaQueries.queryLg} {
            width: 400px;
        }

        @media ${theme.mediaQueries.queryVl} {
            width: 510px;
        }
    `}
`;

export const NewsletterFormInputWrapper = styled.div`
    ${({ theme }: { theme: Theme }) => css`
        display: flex;
        margin-bottom: 12px;

        @media ${theme.mediaQueries.queryTablet} {
            flex-direction: column;
            margin-bottom: 6px;
        }
    `}
`;

export const NewsletterFormButtonWrapper = styled.div`
    ${({ theme }: { theme: Theme }) => css`
        display: flex;
        flex-direction: column;

        button {
            margin-left: 12px;
        }

        @media ${theme.mediaQueries.queryTablet} {
            button {
                margin-left: 0;
                margin-top: 12px;
            }
        }
    `}
`;
