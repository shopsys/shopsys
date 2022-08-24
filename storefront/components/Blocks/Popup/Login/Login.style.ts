import { Icon } from 'components/Basic/Icon/Icon';
import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

export const ButtonsStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 15px 20px;
        margin: 0 -15px 40px;

        border-bottom: 1px solid ${theme.color.primary};

        @media ${theme.mediaQueries.queryLg} {
            display: block;
            padding: 0;
            margin: 0;

            border: 0;
        }
    `}
`;

export const ButtonWrapperStyled = styled.div`
    ${({ theme }) => css`
        width: 100%;
        display: flex;
        justify-content: flex-end;
        margin-top: 20px;
        order: 1;

        button {
            @media ${theme.mediaQueries.queryTablet} {
                padding: 0 10px;
            }
        }
    `}
`;

export const LoginStyled = styled.div`
    ${({ theme }) => css`
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        width: 280px;

        @media ${theme.mediaQueries.querySm} {
            width: 450px;
            max-width: 100%;
        }

        @media ${theme.mediaQueries.queryMd} {
            width: 100%;
            max-width: 720px;
        }

        @media ${theme.mediaQueries.queryLg} {
            width: 760px;
            flex-direction: row;

            &::before {
                position: absolute;
                top: -55px;
                left: calc(50% - 1px);
                height: calc(100% + 55px);
                width: 1px;
                content: '';

                background-color: ${theme.color.primary};
            }
        }
    `}
`;

export const LoginColumnStyled = styled.div`
    ${({ theme }) => css`
        width: 100%;

        @media ${theme.mediaQueries.queryLg} {
            width: 50%;
            padding-right: 20px;

            &:nth-child(2) {
                padding-right: 20px;
                padding-left: 20px;
            }
        }
    `}
`;

export const LoginProfileStyled = styled.div`
    ${({ theme }) => css`
        position: relative;
        padding: 16px;
        margin: 24px -15px 24px 0;
        width: 100%;

        background-color: ${theme.color.blueLight};
        border-top-left-radius: ${theme.radius.big};
        border-bottom-left-radius: ${theme.radius.big};

        @media ${theme.mediaQueries.queryLg} {
            margin: 0 -@window-popup-inner-padding 0 0;
        }
    `}
`;

export const LoginProfileTextStyled = styled.div`
    ${({ theme }) => css`
        display: block;
        max-width: 170px;

        font-size: ${theme.fontSize.bigger};
        font-weight: 400;
        color: ${theme.color.primary};
        line-height: 1.4;

        @media ${theme.mediaQueries.queryLg} {
            max-width: 280px;

            font-size: 20px;
        }
    `}
`;

export const LoginProfileIconStyled = styled.div`
    ${({ theme }) => css`
        position: absolute;
        right: 0;
        top: -20px;
        height: 102px;
        overflow: hidden;

        @media ${theme.mediaQueries.queryMd} {
            right: 40px;
        }

        @media ${theme.mediaQueries.queryLg} {
            top: -23px;
            height: 111px;
        }

        img {
            max-width: none;
        }
    `}
`;

export const LoginMessageStyled = styled.p`
    ${({ theme }) => css`
        display: none;

        @media ${theme.mediaQueries.queryLg} {
            display: block;
            margin-bottom: 30px;
            line-height: 1.3;

            font-size: ${theme.fontSize.default};
        }
    `}
`;

export const LoginLostPassStyled = styled.div`
    ${({ theme }) => css`
        display: flex;
        align-items: center;
        padding: 6px 8px;
        min-height: 43px;
        margin-top: 20px;

        border: 2px solid ${theme.color.primary};
        border-radius: ${theme.radius.big};
        font-size: 14px;
        color: ${theme.color.primary};
        white-space: nowrap;

        @media ${theme.mediaQueries.queryLg} {
            padding: 8px 12px;
        }
    `}
`;

export const LoginLostPassIconStyled = styled(Icon)`
    ${({ theme }) => css`
        display: none;

        @media ${theme.mediaQueries.querySm} {
            display: block;
            width: 31px;
            height: 27px;
            margin: 0 3px 0 -5px;

            color: ${theme.color.red};
        }
    `}
`;

export const LoginLostPassTextStyled = styled.div`
    ${({ theme }) => css`
        display: none;

        @media ${theme.mediaQueries.queryLg} {
            display: block;
            flex-grow: 1;

            font-size: 15px;
        }
    `}
`;

export const LoginLostPassLinkStyled = styled.div`
    ${({ theme }) => css`
        display: none;
        text-decoration: underline;
        cursor: pointer;

        &:hover {
            text-decoration: none;
        }

        @media ${theme.mediaQueries.queryLg} {
            display: block;

            color: ${theme.color.primary};
        }
    `}
`;

export const LoginLostPassLinkMobileStyled = styled.div`
    ${({ theme }) => css`
        display: block;
        text-decoration: underline;

        font-size: 13px;

        &:hover {
            text-decoration: none;
        }

        @media ${theme.mediaQueries.queryLg} {
            display: none;
            color: ${theme.color.primary};
        }
    `}
`;
