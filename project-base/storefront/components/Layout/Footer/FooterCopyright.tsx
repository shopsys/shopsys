import imageLogo from '/public/images/logo.svg';
import { Image } from 'components/Basic/Image/Image';
import useTranslation from 'next-translate/useTranslation';

const TEST_IDENTIFIER = 'layout-footer-footercopyright';

export const FooterCopyright: FC = () => {
    const { t } = useTranslation();

    return (
        <div className="flex flex-col items-center text-center" data-testid={TEST_IDENTIFIER}>
            <div className="flex items-center text-sm text-greyLight">
                {t('Copyright © 2021, Shopsys s.r.o. All rights reserved.')}
            </div>
            <div className="flex items-center text-sm text-greyLight">
                {t('Customized E-shop by')}
                <a className="ml-2 flex w-20" href="https://www.shopsys.com" rel="noreferrer" target="_blank">
                    <Image alt="footer logo" src={imageLogo} />
                </a>
            </div>
        </div>
    );
};
