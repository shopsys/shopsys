import { FooterCopyrightLogoStyled, FooterCopyrightStyled, FooterCopyrightTextStyled } from './FooterCopyright.style';
import { FC } from 'react';
import Image from 'next/image';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const FooterCopyright: FC = () => {
    const testIdentifier = 'layout-footer-footercopyright';

    const t = useTypedTranslationFunction();

    return (
        <FooterCopyrightStyled data-testid={testIdentifier}>
            <FooterCopyrightTextStyled>
                {t('Copyright © 2021, Shopsys s.r.o. All rights reserved.')}
            </FooterCopyrightTextStyled>
            <FooterCopyrightTextStyled>
                {t('Customized E-shop by')}
                <FooterCopyrightLogoStyled href="https://www.shopsys.com" target="_blank">
                    <Image src="/images/logo.svg" width={77} height={18} />
                </FooterCopyrightLogoStyled>
            </FooterCopyrightTextStyled>
        </FooterCopyrightStyled>
    );
};

/* @component */
export default FooterCopyright;
