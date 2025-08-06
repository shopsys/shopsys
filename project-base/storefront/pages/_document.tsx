import { Head, Html, Main, NextScript } from 'next/document';
import Document, { DocumentContext, DocumentInitialProps } from 'next/document';
import { getDomainConfig } from 'utils/domain/domainConfig';
import { logException } from 'utils/errors/logException';

interface MyDocumentInitialProps extends DocumentInitialProps {
    htmlLang: string;
}

class MyDocument extends Document<MyDocumentInitialProps> {
    static async getInitialProps(context: DocumentContext): Promise<MyDocumentInitialProps> {
        const initialProps = await Document.getInitialProps(context);

        let htmlLang = 'en';

        try {
            const domainConfig = getDomainConfig(context);
            htmlLang = domainConfig.defaultLocale;
        } catch (error) {
            logException(error);
        }

        return { ...initialProps, htmlLang };
    }

    render() {
        const { htmlLang } = this.props;

        return (
            <Html lang={htmlLang}>
                <Head />
                <body>
                    <Main />
                    <NextScript />
                </body>
            </Html>
        );
    }
}

export default MyDocument;
