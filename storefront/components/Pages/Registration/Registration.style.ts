import { css } from 'styled-components';
import { styled } from 'components/Theme/main';

export const ButtonWrapperStyled = styled.div`
    width: 100%;
    display: flex;
    justify-content: center;
    margin-top: 30px;
`;

export const ContentSectionStyled = styled.div`
    margin-bottom: 40px;
`;

export const LoginProfileStyled = styled.div`
    ${({ theme }) => css`
        position: relative;
        padding: 16px;
        margin: 24px -15px 24px 0;
        width: 100%;

        background-color: ${theme.color.blueLight};
        border-radius: ${theme.radius.big};

        @media ${theme.mediaQueries.queryLg} {
            margin: 0;
        }
    `}
`;

export const LoginProfileTextStyled = styled.div`
    ${({ theme }) => css`
        display: block;
        line-height: 1.4;

        font-size: ${theme.fontSize.bigger};
        font-weight: 400;
        color: ${theme.color.primary};

        @media ${theme.mediaQueries.queryMd} {
            padding-right: 130px;
        }

        @media ${theme.mediaQueries.queryLg} {
            font-size: 20px;
        }
    `}
`;

export const LoginProfileTextStrongStyled = styled.div`
    display: block;

    font-size: 20px;
    font-weight: 600;
`;

export const LoginProfileIconStyled = styled.div`
    ${({ theme }) => css`
        display: none;
        position: absolute;
        right: 20px;
        bottom: 0;
        height: 110px;
        overflow: hidden;

        @media ${theme.mediaQueries.queryMd} {
            display: block;
            right: 40px;
        }

        img {
            max-width: none;
        }
    `}
`;
