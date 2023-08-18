import useTranslation from 'next-translate/useTranslation';
import Image from 'next/image';

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
                <a className="ml-2 flex w-20" href="https://www.shopsys.com" target="_blank" rel="noreferrer">
                    <Image src="/images/logo.svg" width={77} height={18} alt="footer logo" />
                </a>
            </div>
        </div>
    );
};
