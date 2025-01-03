import imageLogo from '/public/images/logo.svg';
import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import Trans from 'next-translate/Trans';
import useTranslation from 'next-translate/useTranslation';

export const FooterCopyright: FC = () => {
    const { t } = useTranslation();
    const currentYear = new Date().getFullYear();

    return (
        <div className="flex flex-col items-center text-center">
            <div className="text-sm text-textDisabled">
                <Trans
                    defaultTrans="Copyright © <currentYear />, Shopsys s.r.o. All rights reserved."
                    i18nKey="footerCopyright"
                    components={{
                        currentYear: <span tid={TIDs.footer_copyright}>{currentYear}</span>,
                    }}
                />
            </div>
            <div className="flex items-center text-sm text-textDisabled">
                {t('Customized E-shop by')}
                <a className="ml-2 flex w-20" href="https://www.shopsys.com" rel="noreferrer" target="_blank">
                    <Image alt="footer logo" src={imageLogo} />
                </a>
            </div>
        </div>
    );
};
