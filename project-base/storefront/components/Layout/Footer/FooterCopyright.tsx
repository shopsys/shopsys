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
                className="flex items-center justify-center text-center text-sm text-textDisabled"
                tid={TIDs.footer_copyright}
            >
                {t('footerCopyright', { currentYear })}
            </div>
            <div className="flex items-center justify-center text-sm text-textDisabled">
                {t('Customized E-shop by')}
                <a className="ml-2 flex w-20" href="https://www.shopsys.com" rel="noreferrer" target="_blank">
                    <Image alt="footer logo" src={imageLogo} />
                </a>
            </div>
        </>
    );
};
