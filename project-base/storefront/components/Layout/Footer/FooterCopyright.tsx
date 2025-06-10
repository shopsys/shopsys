import imageLogo from '/public/images/logo.svg';
import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';

export const FooterCopyright: FC = () => {
    const { t } = useTranslation();
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

                <a
                    className="ml-2 flex h-6 w-20 rounded-md focus-visible:ring-2 focus-visible:ring-offset-1"
                    href="https://www.shopsys.com"
                    rel="noreferrer"
                    tabIndex={0}
                    target="_blank"
                >
                    <Image alt="footer logo" src={imageLogo} />
                </a>
            </div>
        </>
    );
};
