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
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Link from 'next/link';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';

type FooterBoxInfoProps = {
    orderStep?: boolean;
};

// TODO PRG
const dummyData = {
    phone: '+420 111 222 333',
    opening: 'Po - Út, 10 - 16 hod',
};

const FooterBoxInfo: FC<FooterBoxInfoProps> = (props) => {
    const testIdentifier = 'layout-footer-footerboxinfo';

    const t = useTypedTranslationFunction();
    const { url } = useShopsysSelector((state) => state.domain);
    const [contactUrl] = getInternationalizedStaticUrls(['/contact'], url);

    return (
        <FooterBoxInfoStyled orderStep={props.orderStep} data-testid={testIdentifier}>
            <picture>
                <source srcSet="/images/need_advice2x.png 2x, /images/need_advice2x.png 1x" />
                <FooterBoxInfoImageStyled src="/images/need_advice.png" alt={t('Need advice?')} />
            </picture>
            <FooterBoxInfoContentStyled orderStep={props.orderStep}>
                <FooterBoxInfoTitleStyled>{t('Need advice?')}</FooterBoxInfoTitleStyled>
                <FooterBoxInfoContactStyled orderStep={props.orderStep}>
                    <FooterBoxInfoContactIconStyled iconType="icon" icon="Phone" />
                    <FooterBoxInfoContactPhoneStyled orderStep={props.orderStep} href={'tel:' + dummyData.phone}>
                        {dummyData.phone}
                    </FooterBoxInfoContactPhoneStyled>
                    <FooterBoxInfoContactHoursStyled orderStep={props.orderStep}>
                        {dummyData.opening}
                    </FooterBoxInfoContactHoursStyled>
                </FooterBoxInfoContactStyled>
                <Link href={contactUrl} passHref>
                    <FooterBoxInfoButtonStyled variant="secondary">{t('Write Us')}</FooterBoxInfoButtonStyled>
                </Link>
            </FooterBoxInfoContentStyled>
        </FooterBoxInfoStyled>
    );
};

export default FooterBoxInfo;
