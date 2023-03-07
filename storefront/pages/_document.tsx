import { logException } from 'helpers/errors/logException';
import { Head, Html, Main, NextScript } from 'next/document';

process.on('unhandledRejection', (err) => logException(err));

process.on('uncaughtException', (err) => logException(err));

export default function Document() {
    return (
        <Html>
            <Head>
                <script src="https://widget.packeta.com/v6/www/js/library.js" />
            </Head>
            <body>
                <Main />
                <NextScript />
            </body>
        </Html>
    );
}
