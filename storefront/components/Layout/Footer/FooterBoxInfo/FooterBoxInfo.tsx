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
import { FC } from 'react';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

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
                <FooterBoxInfoButtonStyled type="button" variant="secondary">
                    {t('Write Us')}
                </FooterBoxInfoButtonStyled>
            </FooterBoxInfoContentStyled>
        </FooterBoxInfoStyled>
    );
};

export default FooterBoxInfo;
