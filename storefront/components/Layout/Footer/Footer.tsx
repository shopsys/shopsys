import { CookieConsentLinkStyled, FooterBlockStyled, FooterBottomStyled, FooterStyled } from './Footer.style';
import FooterBoxInfo from './FooterBoxInfo';
import FooterContact from './FooterContact';
import FooterCopyright from './FooterCopyright';
import FooterMenu from './FooterMenu';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import NextLink from 'next/link';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';

type FooterProps = {
    simpleFooter?: boolean;
};

const FOOTER_TEST_IDENTIFIER = 'layout-footer';

const Footer: FC<FooterProps> = ({ simpleFooter }) => {
    const t = useTypedTranslationFunction();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [cookieConsentUrl] = getInternationalizedStaticUrls(['/cookie-consent'], domainUrl);

    return (
        <FooterStyled data-testid={FOOTER_TEST_IDENTIFIER}>
            <FooterBottomStyled>
                {!simpleFooter && (
                    <>
                        <FooterBoxInfo />
                        <FooterBlockStyled>
                            <FooterMenu />
                            <FooterContact />
                        </FooterBlockStyled>
                    </>
                )}
                <FooterCopyright />
                <NextLink href={cookieConsentUrl}>
                    <CookieConsentLinkStyled>{t('Cookie consent update')}</CookieConsentLinkStyled>
                </NextLink>
            </FooterBottomStyled>
        </FooterStyled>
    );
};

export default Footer;
