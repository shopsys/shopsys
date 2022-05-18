import { Theme } from 'components/Theme/main';
import { createGlobalStyle, css } from 'styled-components';

const localVariables = {
    infoToastBackground: '#f2f2ff',
    successToastBackground: '#ddf9f5',
    errorToastBackground: '#ffc4c4',
};

export const ToastsStyle = createGlobalStyle`
    ${({ theme }: { theme: Theme }) => css`
        .Toastify__toast {
            border-radius: 11px;
        }

        .Toastify__toast-body {
            color: ${theme.color.base};
        }

        .Toastify__close-button {
            align-self: center;
            opacity: 1;
            color: ${theme.color.base};
        }

        .Toastify__toast-theme--colored.Toastify__toast--info {
            background-color: ${localVariables.infoToastBackground};
            border: 1px solid ${theme.color.primary};

            .Toastify__toast-icon {
                color: ${theme.color.primary};
            }

            .Toastify__progress-bar-theme--colored.Toastify__progress-bar--info {
                background-color: ${theme.color.primaryLight};
            }
        }

        .Toastify__toast-theme--colored.Toastify__toast--success {
            background-color: ${localVariables.successToastBackground};
            border: 1px solid ${theme.color.green};

            .Toastify__toast-icon {
                color: ${theme.color.green};
            }

            .Toastify__progress-bar-theme--colored.Toastify__progress-bar--success {
                background-color: ${theme.color.greenLight};
            }
        }

        .Toastify__toast-theme--colored.Toastify__toast--error {
            background-color: ${localVariables.errorToastBackground};
            border: 1px solid ${theme.color.red};

            .Toastify__toast-icon {
                color: ${theme.color.red};
            }

            .Toastify__progress-bar-theme--colored.Toastify__progress-bar--error {
                background-color: ${theme.color.redLight};
            }
        }

        :root {
            --toastify-toast-min-height: 48px;
            --toastify-toast-width: 700px;
            --toastify-font-family: sans-serif;
            --toastify-z-index: ${theme.zIndex.maximum};
        }
    `}
`;
