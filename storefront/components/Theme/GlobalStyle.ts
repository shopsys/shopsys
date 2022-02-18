import { createGlobalStyle, css } from 'styled-components';
import reset from 'styled-reset';
import { Theme } from './main';

const GlobalStyle = createGlobalStyle`
    ${({ theme }: { theme: Theme }) => css`
        ${reset};

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html {
            height: 100%;
        }

        html,
        body {
            min-height: 100%;
        }

        body,
        form,
        input,
        select,
        button,
        p,
        pre,
        dfn,
        address,
        ul,
        ol,
        li,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        img,
        table,
        tr,
        td,
        th,
        textarea {
            color: ${theme.color.base};
            font-size: ${theme.fontSize.small};
            line-height: ${theme.lineHeight.default};
            font-family: ${theme.fontFamily.base};

            @media @query-lg {
                font-size: ${theme.fontSize.default};
            }
        }

        img:not(.icon) {
            max-width: 100%;
            height: auto;

            image-rendering: -webkit-optimize-contrast;
        }

        td,
        th {
            text-align: left;
        }

        hr {
            height: 1px;
        }

        dfn {
            font-style: normal;
        }

        table {
            border-collapse: collapse;
        }

        p {
            word-wrap: break-word;
        }

        a {
            text-decoration: underline;
            color: ${theme.color.greyDark};
            cursor: pointer;
            outline: none;

            &:hover {
                text-decoration: underline;
                color: ${theme.color.primary};
            }
        }

        b,
        strong {
            font-weight: 800;
        }
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Firefox */
        input[type='number'] {
            -moz-appearance: textfield;
        }
    `}
`;

export default GlobalStyle;
