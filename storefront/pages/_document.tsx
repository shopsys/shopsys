import { logException } from 'helpers/errors/logException';
import Document, { DocumentContext, Head, Html, Main, NextScript } from 'next/document';
import { ReactElement } from 'react';
import { ServerStyleSheet } from 'styled-components';
import getGtmHeadScript from 'utils/Gtm/GtmHeadScript';
import { GTM_ID } from 'utils/Gtm/Helpers';

process.on('unhandledRejection', (err) => {
    logException(err);
});

process.on('uncaughtException', (err) => {
    logException(err);
});

/**
 * @see https://styled-components.com/docs/advanced#nextjs
 */
export default class MyDocument extends Document {
    // eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
    static async getInitialProps(ctx: DocumentContext) {
        const sheet = new ServerStyleSheet();
        const originalRenderPage = ctx.renderPage;

        try {
            ctx.renderPage = () =>
                originalRenderPage({
                    enhanceApp: (App) => (props) => sheet.collectStyles(<App {...props} />),
                });

            const initialProps = await Document.getInitialProps(ctx);
            return {
                ...initialProps,
                styles: (
                    <>
                        {initialProps.styles}
                        {sheet.getStyleElement()}
                    </>
                ),
            };
        } finally {
            sheet.seal();
        }
    }

    render(): ReactElement {
        return (
            <Html>
                <Head>
                    <script src="https://widget.packeta.com/v6/www/js/library.js" />
                    {getGtmHeadScript(GTM_ID)}
                </Head>
                <body>
                    <Main />
                    <NextScript />
                </body>
            </Html>
        );
    }
}
