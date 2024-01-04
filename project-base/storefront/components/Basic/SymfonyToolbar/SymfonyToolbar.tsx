import { isEnvironment } from 'helpers/isEnvironment';
import { useDomainConfig } from 'hooks/useDomainConfig';
import Script from 'next/script';

export const SymfonyToolbar: FC = () => {
    const { url } = useDomainConfig();

    if (!isEnvironment('development')) {
        return null;
    }

    return <Script src={url + '/bundles/symfonyprofilerspa/load_toolbar_spa.js'} strategy="beforeInteractive" />;
};
