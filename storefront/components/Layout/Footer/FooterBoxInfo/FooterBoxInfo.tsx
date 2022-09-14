import {
    FooterBoxInfoButtonStyled,
    FooterBoxInfoContactHoursStyled,
    FooterBoxInfoContactIconStyled,
    FooterBoxInfoContactPhoneStyled,
    FooterBoxInfoContactStyled,
    FooterBoxInfoContentStyled,
    FooterBoxInfoImageStyled,
    FooterBoxInfoStyled,
    FooterBoxInfoTitleStyled,
} from './FooterBoxInfo.style';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import NextLink from 'next/link';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';

// TODO PRG
const dummyData = {
    phone: '+420 111 222 333',
    opening: 'Po - Út, 10 - 16 hod',
};

const TEST_IDENTIFIER = 'layout-footer-footerboxinfo';

export const FooterBoxInfo: FC = () => {
    const t = useTypedTranslationFunction();
    const { url } = useShopsysSelector((state) => state.domain);
    const [contactUrl] = getInternationalizedStaticUrls(['/contact'], url);

    return (
        <FooterBoxInfoStyled data-testid={TEST_IDENTIFIER}>
            <picture>
                <source srcSet="/images/need_advice2x.png 2x, /images/need_advice2x.png 1x" />
                <FooterBoxInfoImageStyled src="/images/need_advice.png" alt={t('Need advice?')} />
            </picture>
            <FooterBoxInfoContentStyled>
                <FooterBoxInfoTitleStyled>{t('Need advice?')}</FooterBoxInfoTitleStyled>
                <FooterBoxInfoContactStyled>
                    <FooterBoxInfoContactIconStyled iconType="icon" icon="Phone" />
                    <FooterBoxInfoContactPhoneStyled href={'tel:' + dummyData.phone}>
                        {dummyData.phone}
                    </FooterBoxInfoContactPhoneStyled>
                    <FooterBoxInfoContactHoursStyled>{dummyData.opening}</FooterBoxInfoContactHoursStyled>
                </FooterBoxInfoContactStyled>
                <NextLink href={contactUrl} passHref>
                    <FooterBoxInfoButtonStyled variant="secondary">{t('Write to us')}</FooterBoxInfoButtonStyled>
                </NextLink>
            </FooterBoxInfoContentStyled>
        </FooterBoxInfoStyled>
    );
};
