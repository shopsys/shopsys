import { styled } from 'components/Theme/main';
import { css } from 'styled-components';

const localVariables = {
    userTextUlPointSize: '5px',
    userTextHeadingFontSize: '18px',
    userTextHeadingMarginBottom: '0.4rem',
    userTextParagraphMargin: '0 0 1.75rem',
    userTextComponentMargin: '0 0 1.75rem',
    userTextFontSize: '15px',
    userTextLineHeight: '1.6',
} as const;

export const UserTextBasicStyled = css(
    ({ theme }) => css`
        font-size: ${localVariables.userTextFontSize};
        line-height: ${localVariables.userTextLineHeight};
        -webkit-font-smoothing: antialiased;

        color: ${theme.color.greyLight};

        p,
        ul,
        ol,
        li {
            line-height: inherit;

            font-size: inherit;
            color: inherit;
        }

        h2,
        h3,
        h4,
        h5 {
            margin-bottom: ${localVariables.userTextHeadingMarginBottom};
            text-transform: none;

            font-size: ${localVariables.userTextHeadingFontSize};
        }

        p {
            margin: ${localVariables.userTextParagraphMargin};

            &:last-of-type {
                margin-bottom: 0;
            }
        }

        strong,
        b {
            color: ${theme.color.primary};
            font-weight: 400;
        }

        a {
            color: ${theme.color.primary};
            text-decoration: underline;

            &:hover {
                text-decoration: none;
            }
        }

        ul {
            padding: 0;
            margin: ${localVariables.userTextComponentMargin};
            list-style: none;

            li {
                position: relative;
                padding-left: 17px;
                margin-bottom: 20px;

                &:after {
                    position: absolute;
                    content: '';
                    top: ${localVariables.userTextUlPointSize} * 2;
                    left: 0;
                    width: ${localVariables.userTextUlPointSize};
                    height: ${localVariables.userTextUlPointSize};

                    border-radius: 100%;
                    background-color: ${theme.color.primary};
                }
            }

            ul {
                margin: 5px 0 0 15px;
            }
        }

        img {
            display: block;
            height: auto;

            border-radius: ${theme.radius.big};
        }
    `,
);

export const UserTextStyled = styled.section`
    ${UserTextBasicStyled}
`;

export const GrapesJsStyled = styled.section(
    ({ theme }) => css`
        ${UserTextBasicStyled}
        div, p {
            padding: 4px 0;
        }

        ul,
        ol {
            display: grid;
        }

        .row {
            display: block;
            width: 100%;

            @media ${theme.mediaQueries.queryLg} {
                display: flex;
                flex-direction: row;
                flex-wrap: wrap;
            }

            & .column {
                display: flex;
                flex-direction: column;
                flex: 1;
                padding: 10px;

                &:first-child {
                    padding-left: 0;
                }

                &:last-child {
                    padding-right: 0;
                }
            }
        }

        .video {
            overflow: hidden;
            padding-bottom: 56.25%;
            position: relative;
            height: 0;

            iframe {
                left: 0;
                top: 0;
                height: 100%;
                width: 100%;
                position: absolute;
            }
        }

        .gjs-text-with-image {
            width: 100%;

            & .clear {
                clear: both;
                padding: 0;
            }

            & .inner {
                & .image {
                    width: 100%;
                    margin-bottom: 16px;

                    @media ${theme.mediaQueries.queryLg} {
                        width: 200px;
                        margin-bottom: 14px;
                    }

                    @media ${theme.mediaQueries.queryXl} {
                        width: 350px;
                        margin-bottom: 14px;
                    }
                }

                &.left .image {
                    @media ${theme.mediaQueries.queryLg} {
                        float: left;
                        margin-right: 30px;
                        margin-left: 0;
                    }

                    @media ${theme.mediaQueries.queryXl} {
                        float: left;
                        margin-right: 30px;
                        margin-left: -200px;
                    }
                }

                &.right .image {
                    @media ${theme.mediaQueries.queryLg} {
                        float: right;
                        margin-left: 30px;
                        margin-right: 0;
                    }

                    @media ${theme.mediaQueries.queryXl} {
                        float: right;
                        margin-left: 30px;
                        margin-right: -200px;
                    }
                }
            }
        }
    `,
);
