import styled from 'styled-components';
import { Theme } from 'theme/main';

const styleVariables = {
    inUserTextUlPointSize: '5px',
    inUserTextHeadingFontSize: '18px',
    inUserTextHeadingMarginBottom: '0.4rem',
    inUserTextParagraphMargin: '0 0 1.75rem',
    inUserTextComponentMargin: '0 0 1.75rem',
    inUserTextFontSize: '15px',
    inUserTextLineHeight: '1.6',
};

export const StyledShopsysInUserText = styled.section`
    ${({ theme }: { theme: Theme }) => `
        font-size: ${styleVariables.inUserTextFontSize};
        line-height: ${styleVariables.inUserTextLineHeight};
        -webkit-font-smoothing: antialiased;
        color: ${theme.color.baseLighter};

        p,
        ul,
        ol,
        li {
            font-size: inherit;
            line-height: inherit;
            color: inherit;
        }

        h2,
        h3,
        h4,
        h5 {
            margin-bottom: ${styleVariables.inUserTextHeadingMarginBottom};
            font-size: ${styleVariables.inUserTextHeadingFontSize};
            text-transform: none;
        }

        p {
            margin: ${styleVariables.inUserTextParagraphMargin};

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
            color: ${theme.color.orangeLight};
            text-decoration: underline;

            &:hover {
                text-decoration: none;
            }
        }

        ul {
            padding: 0;
            margin: 0;
            list-style: none;
            margin: ${styleVariables.inUserTextComponentMargin};

            li {
                position: relative;
                padding-left: 17px;
                margin-bottom: 20px;

                &:after {
                    position: absolute;
                    content: '';
                    top: ${styleVariables.inUserTextUlPointSize} * 2;
                    left: 0;
                    width: ${styleVariables.inUserTextUlPointSize};
                    height: ${styleVariables.inUserTextUlPointSize};
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
    `}
`;
