import styled from 'styled-components';

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
    font-size: ${styleVariables.inUserTextFontSize};
    line-height: ${styleVariables.inUserTextLineHeight};
    -webkit-font-smoothing: antialiased;
    color: ${(props) => props.theme.color.baseLighter};

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
        color: ${(props) => props.theme.color.primary};
        font-weight: 400;
    }

    a {
        color: ${(props) => props.theme.color.orangeLight};
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
                background-color: ${(props) => props.theme.color.primary};
            }
        }

        ul {
            margin: 5px 0 0 15px;
        }
    }

    img {
        display: block;
        height: auto;
        border-radius: ${(props) => props.theme.radius.default};
    }
`;
