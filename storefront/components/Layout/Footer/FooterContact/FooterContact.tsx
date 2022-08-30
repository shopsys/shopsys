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
import { Icon } from 'components/Basic/Icon/Icon';
import { desktopFirstSizes } from 'components/Theme/mediaQueries';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/useResizeWidthEffect';
import { FC, useState } from 'react';

const TEST_IDENTIFIER = 'layout-footer-footercontact';

export const FooterContact: FC = () => {
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
        <FooterContactStyled data-testid={TEST_IDENTIFIER}>
            {isDesktop && (
                <>
                    <FooterContactHeadingStyled type="h4">{t('Follow Us')}</FooterContactHeadingStyled>
                    <FooterContactSocialsStyled>
                        <FooterContactSocialsItemStyled href="#">
                            <FooterContactInstagramIconStyled iconType="icon" icon="Instagram" />
                        </FooterContactSocialsItemStyled>
                        <FooterContactSocialsItemStyled href="#">
                            <Icon iconType="image" icon="facebook" width={32} height={32} alt={t('Facebook')} />
                        </FooterContactSocialsItemStyled>
                        <FooterContactSocialsItemStyled href="#">
                            <FooterContactYoutubeIconStyled iconType="icon" icon="Youtube" />
                        </FooterContactSocialsItemStyled>
                    </FooterContactSocialsStyled>
                </>
            )}
            <FooterContactLangsStyled>
                <FooterContactLangsItemStyled href="#">
                    <Icon iconType="image" icon="cz" width={24} height={16} alt={t('Czechia')} />
                    <FooterContactLangsItemTextStyled>{t('Czechia')}</FooterContactLangsItemTextStyled>
                </FooterContactLangsItemStyled>
                <FooterContactLangsItemStyled href="#">
                    <Icon iconType="image" icon="sk" width={24} height={16} alt={t('Slovakia')} />
                    <FooterContactLangsItemTextStyled>{t('Slovakia')}</FooterContactLangsItemTextStyled>
                </FooterContactLangsItemStyled>
            </FooterContactLangsStyled>
        </FooterContactStyled>
    );
};
