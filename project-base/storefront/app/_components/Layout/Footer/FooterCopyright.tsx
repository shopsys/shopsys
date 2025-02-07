import imageLogo from '/public/images/logo.svg';
import { getDomainConfigServer } from 'app/_utils/domain/domainConfigServer';
import { Image } from 'components/Basic/Image/Image';
import { headers } from 'next/headers';
import { getDictionary } from 'utils/getDictionary';
import { getServerT } from 'utils/getServerTranslation';

export default async function FooterCopyright() {
    const { defaultLocale: lang } = getDomainConfigServer(headers().get('host')!);
    const dictionary = await getDictionary(lang);
    const t = await getServerT({ defaultLang: lang, defaultDictionary: dictionary });

    const currentYear = new Date().getFullYear();

    return (
        <div className="flex flex-col items-center py-4 text-center">
            <div className="flex items-center text-sm text-textDisabled">{currentYear}</div>
            <div className="flex items-center text-sm text-textDisabled">
                {t('Customized E-shop by')}
                <a className="ml-2 flex w-20" href="https://www.shopsys.com" rel="noreferrer" target="_blank">
                    <Image alt="footer logo" src={imageLogo} />
                </a>
            </div>
        </div>
    );
}
