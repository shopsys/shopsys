import { CookieConsentLinkStyled, FooterBlockStyled, FooterBottomStyled, FooterStyled } from './Footer.style';
import { FooterBoxInfo } from './FooterBoxInfo/FooterBoxInfo';
import { FooterContact } from './FooterContact/FooterContact';
import { FooterCopyright } from './FooterCopyright/FooterCopyright';
import { FooterMenu } from './FooterMenu/FooterMenu';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import NextLink from 'next/link';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';

type FooterProps = {
    simpleFooter?: boolean;
};

const FOOTER_TEST_IDENTIFIER = 'layout-footer';

export const Footer: FC<FooterProps> = ({ simpleFooter }) => {
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
