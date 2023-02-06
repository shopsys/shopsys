import { ToastsStyle } from 'components/Helpers/Toasts/Toasts.style';
import { FontFaceStyle } from 'components/Theme/FontFaceStyle';
import { theme } from 'components/Theme/main';
import { FC } from 'react';
import { ThemeProvider } from 'styled-components';

export const ShopsysGlobalProvider: FC = ({ children }) => {
    return (
        <ThemeProvider theme={theme}>
            <FontFaceStyle />
            <ToastsStyle />
            {children}
        </ThemeProvider>
    );
};
