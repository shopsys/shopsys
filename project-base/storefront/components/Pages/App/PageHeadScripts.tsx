import getConfig from 'next/config';
import Head from 'next/head';
import { useCookiesStore } from 'store/useCookiesStore';
import { isClient } from 'utils/isClient';

export const PageHeadScripts: FC = () => {
    const isUserSnapEnabled = useCookiesStore((store) => store.isUserSnapEnabled);
    const { publicRuntimeConfig } = getConfig();
    const userSnapApiKey = publicRuntimeConfig.userSnapApiKey;

    return (
        <Head>
            {isUserSnapEnabled && isClient && userSnapApiKey && (
                <script
                    async
                    id="usersnap-code"
                    dangerouslySetInnerHTML={{
                        __html: `window.onUsersnapLoad=function(api){api.init();}
                                var script = document.createElement('script');script.defer = 1;script.src = 'https://widget.usersnap.com/global/load/${userSnapApiKey}?onload=onUsersnapLoad';document.getElementsByTagName('head')[0].appendChild(script);`,
                    }}
                />
            )}
        </Head>
    );
};
