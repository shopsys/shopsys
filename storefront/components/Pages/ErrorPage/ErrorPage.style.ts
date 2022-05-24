import { ButtonStyled } from 'components/Basic/Link/Link.style';
import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

const localVariables = {
    pageHeight: '350px',
    pageWidth: '400px',
    detailInfoWidth: '512px',
} as const;

export const ErrorPageStyled = styled.div`
    ${({ theme }) => css`
        position: relative;
        display: flex;
        flex-wrap: wrap;
        width: 100%;
        max-width: ${localVariables.pageWidth};
        margin: 32px auto;
        padding-bottom: 200px;

        border-radius: ${theme.radius.big};
        background-color: ${theme.color.blueLight};
        overflow: hidden;

        @media ${theme.mediaQueries.queryLg} {
            width: 100%;
            max-width: 100%;
            height: ${localVariables.pageHeight};
            margin: 48px 0 80px;
            padding-bottom: 0;

            background-color: ${theme.color.white};
        }
    `}
`;

export const ErrorPageTextStyled = styled.div`
    ${({ theme }) => css`
        position: relative;
        padding: 20px 16px 0 16px;
        z-index: 1;
        text-align: center;

        @media ${theme.mediaQueries.queryLg} {
            padding: 48px 0 0 48px;
            text-align: left;
        }
    `}
`;

export const ErrorPageTextHeadingStyled = styled.div`
    ${({ theme }) => css`
        line-height: 1.2;
        margin-bottom: 0;
        width: 100%;
        text-align: center;

        text-transform: none;
        font-size: 26px;
        font-weight: 400;

        @media ${theme.mediaQueries.queryLg} {
            text-align: left;
            max-width: ${localVariables.pageWidth};
        }
    `}
`;

export const ErrorPageTextMainStyled = styled.div`
    ${({ theme }) => css`
        margin: 16px 0 0;

        font-size: 15px;
        color: rgba(21, 22, 30, 0.7);

        @media ${theme.mediaQueries.queryLg} {
            margin: 0;
        }
    `}
`;

export const ErrorPageButtonLinkStyled = styled(ButtonStyled)`
    ${({ theme }) => css`
        margin-top: 8px;

        @media ${theme.mediaQueries.queryLg} {
            margin-top: 32px;
        }
    `}
`;

export const ErrorPageImageStyled = styled.div`
    ${({ theme }) => css`
        position: absolute;
        bottom: 20px;
        width: 100%;
        height: 100%;
        max-height: 150px;
        object-fit: contain;
        object-position: right bottom;

        @media ${theme.mediaQueries.queryLg} {
            top: 0;
            left: 0;
            right: auto;
            bottom: auto;
            max-height: 100%;
            object-fit: cover;
        }
    `}
`;
