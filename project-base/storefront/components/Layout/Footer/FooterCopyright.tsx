import imageLogo from '/public/images/logo.svg';
import { Image } from 'components/Basic/Image/Image';
import useTranslation from 'next-translate/useTranslation';

export const FooterCopyright: FC = () => {
    const { t } = useTranslation();
    const currentYear = new Date().getFullYear();

    return (
        <div className="flex flex-col items-center text-center">
            <div className="flex items-center text-sm text-textDisabled">
                {t('Copyright © {{ currentYear }}, Shopsys s.r.o. All rights reserved.', { currentYear })}
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
