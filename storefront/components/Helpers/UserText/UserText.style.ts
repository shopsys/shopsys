import { styled } from '../../Theme/main';

const localVariables = {
    userTextUlPointSize: '5px',
    userTextHeadingFontSize: '18px',
    userTextHeadingMarginBottom: '0.4rem',
    userTextParagraphMargin: '0 0 1.75rem',
    userTextComponentMargin: '0 0 1.75rem',
    userTextFontSize: '15px',
    userTextLineHeight: '1.6',
} as const;

export const UserTextStyled = styled.section`
    ${({ theme }) => `
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
            color: ${theme.color.orangeLight};
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
    `}
`;
