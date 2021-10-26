import {
    MenuIconicButtonMobileLinkStyled,
    MenuIconicButtonMobileStyled,
    MenuIconicItemIconStyled,
    MenuIconicItemLinkStyled,
    MenuIconicItemStyled,
    MenuIconicListStyled,
} from './MenuIconic.style';
import { FC } from 'react';
import NextLink from 'next/link';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const MenuIconic: FC = () => {
    const t = useTypedTranslationFunction();
    const domainConfig = useShopsysSelector((state) => state.domain);
    const [storesUrl] = useGetInternationalizedStaticUrls(['/stores'], domainConfig.url);

    return (
        <>
            <MenuIconicListStyled>
                <MenuIconicItemStyled>
                    <NextLink href="/" passHref>
                        <MenuIconicItemLinkStyled>
                            <MenuIconicItemIconStyled icon="Chat" />
                            {t('Customer service')}
                        </MenuIconicItemLinkStyled>
                    </NextLink>
                </MenuIconicItemStyled>
                <MenuIconicItemStyled>
                    <NextLink href={storesUrl} passHref>
                        <MenuIconicItemLinkStyled>
                            <MenuIconicItemIconStyled icon="Marker" />
                            {t('Stores')}
                        </MenuIconicItemLinkStyled>
                    </NextLink>
                </MenuIconicItemStyled>
                <MenuIconicItemStyled>
                    <NextLink href="/" passHref>
                        <MenuIconicItemLinkStyled>
                            <MenuIconicItemIconStyled icon="User" />
                            {t('Sign in')}
                        </MenuIconicItemLinkStyled>
                    </NextLink>
                </MenuIconicItemStyled>
            </MenuIconicListStyled>
            <MenuIconicButtonMobileStyled>
                <NextLink href="/" passHref>
                    <MenuIconicButtonMobileLinkStyled>
                        <MenuIconicItemIconStyled icon="User" />
                    </MenuIconicButtonMobileLinkStyled>
                </NextLink>
            </MenuIconicButtonMobileStyled>
        </>
    );
};

/* @component */
export default MenuIconic;
