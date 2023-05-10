import { FooterBoxInfo } from './FooterBoxInfo';
import { FooterContact } from './FooterContact';
import { FooterCopyright } from './FooterCopyright';
import { FooterMenu } from './FooterMenu';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import NextLink from 'next/link';

type FooterProps = {
    simpleFooter?: boolean;
};

const FOOTER_TEST_IDENTIFIER = 'layout-footer';

export const Footer: FC<FooterProps> = ({ simpleFooter }) => {
    const t = useTypedTranslationFunction();
    const { url } = useDomainConfig();
    const [cookieConsentUrl] = getInternationalizedStaticUrls(['/cookie-consent'], url);

    return (
        <div className="relative mt-auto" data-testid={FOOTER_TEST_IDENTIFIER}>
            <div className="flex flex-col pt-5 pb-11 lg:py-11">
                {!simpleFooter && (
                    <>
                        <FooterBoxInfo />
                        <div className="mb-12 vl:mb-24 vl:flex">
                            <FooterMenu />
                            <FooterContact />
                        </div>
                    </>
                )}
                <FooterCopyright />
                <NextLink href={cookieConsentUrl}>
                    <a className="self-center text-greyLight no-underline transition hover:text-whitesmoke hover:no-underline">
                        {t('Cookie consent update')}
                    </a>
                </NextLink>
            </div>
        </div>
    );
};
