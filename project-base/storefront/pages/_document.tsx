import { Head, Html, Main, NextScript } from 'next/document';
import Document, { DocumentContext, DocumentInitialProps } from 'next/document';
import { getDomainConfig } from 'utils/domain/domainConfig';
import { logException } from 'utils/errors/logException';

interface MyDocumentInitialProps extends DocumentInitialProps {
    htmlLang: string;
}

// Browser warning translations (must be simple object, no imports - this runs before React hydrates)
const BROWSER_WARNING_TRANSLATIONS: Record<string, { message: string; dismissLabel: string }> = {
    en: {
        message:
            'Your browser is outdated and may not display this website correctly. Please update your browser or operating system for the best experience.',
        dismissLabel: 'Dismiss warning',
    },
    cs: {
        message:
            'Váš prohlížeč je zastaralý a nemusí tuto stránku zobrazovat správně. Pro nejlepší zážitek prosím aktualizujte svůj prohlížeč nebo operační systém.',
        dismissLabel: 'Zavřít upozornění',
    },
    sk: {
        message:
            'Váš prehliadač je zastaraný a nemusí zobrazovať túto webovú stránku správne. Pre najlepší zážitok prosím aktualizujte prehliadač alebo operačný systém.',
        dismissLabel: 'Zavrieť upozornenie',
    },
};

const getBrowserWarningTranslation = (locale: string) => {
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
    return BROWSER_WARNING_TRANSLATIONS[locale] || BROWSER_WARNING_TRANSLATIONS.en;
};

// If the browser can't parse optional chaining / nullish coalescing, this script
// silently fails and the flag stays false — no eval() needed.
const BROWSER_SYNTAX_TEST_SCRIPT = `window.__browserModern=false;`;
const BROWSER_SYNTAX_PROBE_SCRIPT = `var _t={a:{b:1}};if(_t?.a?.b&&(null??"x")){window.__browserModern=true;}`;

const BROWSER_WARNING_SCRIPT = `
(function() {
    if (window.__browserModern) return;

    var STORAGE_KEY = 'browser-warning-dismissed';
    var wasDismissed = false;
    try {
        wasDismissed = localStorage.getItem(STORAGE_KEY) === '1';
    } catch (e) {}

    if (wasDismissed) return;

    document.addEventListener('DOMContentLoaded', function() {
        var banner = document.getElementById('browser-warning-banner');
        var dismissBtn = document.getElementById('browser-warning-dismiss');
        var originalPaddingTop = '';

        if (banner) {
            originalPaddingTop = window.getComputedStyle(document.body).paddingTop;
            var originalPaddingValue = parseInt(originalPaddingTop, 10) || 0;

            banner.style.display = 'block';
            document.body.style.paddingTop = (originalPaddingValue + banner.offsetHeight) + 'px';
        }

        if (dismissBtn) {
            dismissBtn.onclick = function() {
                if (banner) {
                    banner.style.display = 'none';
                    document.body.style.paddingTop = originalPaddingTop;
                }
                try {
                    localStorage.setItem(STORAGE_KEY, '1');
                } catch (e) {}
            };
        }
    });
})();
`;

const BROWSER_WARNING_STYLES = `
    #browser-warning-banner {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 10100;
        padding: 12px 16px;
        background: #fcf6e2;
        border-bottom: 1px solid #fcbd46;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        box-shadow: 0 2px 8px rgba(37, 40, 61, 0.1);
    }
    #browser-warning-banner .bwb-content {
        display: flex;
        align-items: center;
        justify-content: center;
        max-width: 1240px;
        margin: 0 auto;
    }
    #browser-warning-banner .bwb-icon {
        flex-shrink: 0;
        width: 20px;
        height: 20px;
        color: #d49215;
        margin-right: 16px;
    }
    #browser-warning-banner .bwb-text {
        flex: 1;
        font-size: 14px;
        line-height: 1.5;
        color: #25283d;
        margin: 0;
        text-align: center;
    }
    #browser-warning-banner .bwb-dismiss {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        margin-left: 16px;
        padding: 0;
        border: none;
        background: transparent;
        cursor: pointer;
        border-radius: 4px;
        color: #7892bc;
    }
    #browser-warning-banner .bwb-dismiss:hover {
        color: #25283d;
        background: rgba(37, 40, 61, 0.05);
    }
    #browser-warning-banner .bwb-dismiss:focus-visible {
        outline: 2px solid #25283d;
        outline-offset: 2px;
    }
    @media (max-width: 600px) {
        #browser-warning-banner {
            padding: 10px 12px;
        }
        #browser-warning-banner .bwb-icon {
            margin-right: 12px;
        }
        #browser-warning-banner .bwb-text {
            font-size: 13px;
            text-align: left;
        }
        #browser-warning-banner .bwb-dismiss {
            margin-left: 12px;
        }
    }
`;

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
        const warningTranslation = getBrowserWarningTranslation(htmlLang);

        return (
            <Html lang={htmlLang}>
                <Head>
                    <style dangerouslySetInnerHTML={{ __html: BROWSER_WARNING_STYLES }} />
                    <script dangerouslySetInnerHTML={{ __html: BROWSER_SYNTAX_TEST_SCRIPT }} />
                    <script dangerouslySetInnerHTML={{ __html: BROWSER_SYNTAX_PROBE_SCRIPT }} />
                    <script dangerouslySetInnerHTML={{ __html: BROWSER_WARNING_SCRIPT }} />
                </Head>
                <body>
                    <div id="browser-warning-banner" role="alert">
                        <div className="bwb-content">
                            <svg
                                className="bwb-icon"
                                fill="none"
                                stroke="currentColor"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth="2"
                                viewBox="0 0 24 24"
                            >
                                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                                <path d="M12 9v4" />
                                <circle cx="12" cy="17" fill="currentColor" r="0.5" />
                            </svg>
                            <p className="bwb-text">{warningTranslation.message}</p>
                            <button
                                aria-label={warningTranslation.dismissLabel}
                                className="bwb-dismiss"
                                id="browser-warning-dismiss"
                                type="button"
                            >
                                <svg
                                    fill="none"
                                    height="16"
                                    stroke="currentColor"
                                    strokeWidth="2"
                                    viewBox="0 0 24 24"
                                    width="16"
                                >
                                    <path d="M18 6L6 18M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <Main />
                    <NextScript />
                </body>
            </Html>
        );
    }
}

export default MyDocument;
