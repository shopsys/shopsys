import { FC, useState } from 'react';
import {
    FooterContactHeadingStyled,
    FooterContactInstagramIconStyled,
    FooterContactLangsItemStyled,
    FooterContactLangsItemTextStyled,
    FooterContactLangsStyled,
    FooterContactSocialsItemStyled,
    FooterContactSocialsStyled,
    FooterContactStyled,
    FooterContactYoutubeIconStyled,
} from './FooterContact.style';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import Icon from 'components/Basic/Icon';
import { useGetWindowSize } from 'hooks/ui/UseGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/UseResizeWidthEffect';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const FooterContact: FC = () => {
    const t = useTypedTranslationFunction();
    const [isDesktop, setIsDesktop] = useState(false);
    const { width } = useGetWindowSize();

    useResizeWidthEffect(
        width,
        desktopFirstSizes.tablet,
        () => setIsDesktop(true),
        () => setIsDesktop(false),
    );

    return (
        <FooterContactStyled>
            {isDesktop && (
                <>
                    <FooterContactHeadingStyled type="h4">{t('Follow Us')}</FooterContactHeadingStyled>
                    <FooterContactSocialsStyled>
                        <FooterContactSocialsItemStyled href="#">
                            <FooterContactInstagramIconStyled icon="Instagram" />
                        </FooterContactSocialsItemStyled>
                        <FooterContactSocialsItemStyled href="#">
                            <Icon iconImage="facebook" width={32} height={32} alt={t('Facebook')} />
                        </FooterContactSocialsItemStyled>
                        <FooterContactSocialsItemStyled href="#">
                            <FooterContactYoutubeIconStyled icon="Youtube" />
                        </FooterContactSocialsItemStyled>
                    </FooterContactSocialsStyled>
                </>
            )}
            <FooterContactLangsStyled>
                <FooterContactLangsItemStyled href="#">
                    <Icon iconImage="cz" width={24} height={16} alt={t('Czechia')} />
                    <FooterContactLangsItemTextStyled>{t('Czechia')}</FooterContactLangsItemTextStyled>
                </FooterContactLangsItemStyled>
                <FooterContactLangsItemStyled href="#">
                    <Icon iconImage="sk" width={24} height={16} alt={t('Slovakia')} />
                    <FooterContactLangsItemTextStyled>{t('Slovakia')}</FooterContactLangsItemTextStyled>
                </FooterContactLangsItemStyled>
            </FooterContactLangsStyled>
        </FooterContactStyled>
    );
};

export default FooterContact;
