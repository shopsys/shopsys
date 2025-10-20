import { getDomainConfig } from 'app/_utils/getDomainConfig';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import imageLogo from 'public/images/logo.svg';
import { getDictionary } from 'utils/getDictionary';

export const FooterCopyright = async () => {
    const { defaultLocale: lang } = await getDomainConfig();
    const dictionary = await getDictionary(lang);
    const t = await getTranslation({ defaultLang: lang, defaultDictionary: dictionary });

    const currentYear = new Date().getFullYear();

    return (
        <>
            <div
                className="text-text-disabled flex items-center justify-center text-center text-sm"
                tid={TIDs.footer_copyright}
            >
                {t('footerCopyright', { currentYear })}
            </div>
            <div className="text-text-disabled flex items-center justify-center text-sm">
                {t('Customized E-shop by')}
                <a className="ml-2 flex w-20" href="https://www.shopsys.com" rel="noreferrer" target="_blank">
                    <Image alt="footer logo" src={imageLogo} />
                </a>
            </div>
        </>
    );
};
