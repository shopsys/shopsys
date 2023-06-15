import { getDomainConfig } from 'helpers/domain/domain';

export const GtmHeadScript: FC = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    const GTM_ID = getDomainConfig(window.location.host).gtmId;
    if (GTM_ID === undefined || GTM_ID.length === 0) {
        return null;
    }

    return (
        <script
            dangerouslySetInnerHTML={{
                __html: `
                (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
                })(window,document,'script','dataLayer','${GTM_ID}');
            `,
            }}
        />
    );
};
