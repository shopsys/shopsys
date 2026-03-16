import { getPublicConfigProperty } from 'envConfig';
import Script from 'next/script';
import { useCookiesStore } from 'store/useCookiesStore';
import { isClient } from 'utils/isClient';

export const PageHeadScripts: FC = () => {
    const isUserSnapEnabled = useCookiesStore((store) => store.isUserSnapEnabled);
    const userSnapApiKey = getPublicConfigProperty('userSnapApiKey');

    if (!isUserSnapEnabled || !isClient || !userSnapApiKey) {
        return null;
    }

    return (
        <Script
            id="usersnap-code"
            strategy="afterInteractive"
            dangerouslySetInnerHTML={{
                __html: `window.onUsersnapLoad=function(api){api.init();}
                        var script = document.createElement('script');script.defer = 1;script.src = 'https://widget.usersnap.com/global/load/${userSnapApiKey}?onload=onUsersnapLoad';document.getElementsByTagName('head')[0].appendChild(script);`,
            }}
        />
    );
};
