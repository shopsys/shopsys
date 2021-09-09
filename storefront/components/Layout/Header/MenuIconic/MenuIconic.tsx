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
import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';

const MenuIconic: FC = () => {
    const t = useTypedTranslationFunction();

    return (
        <>
            <MenuIconicListStyled>
                <MenuIconicItemStyled>
                    <NextLink href="/" passHref>
                        <MenuIconicItemLinkStyled>
                            <MenuIconicItemIconStyled icon="Chat" />
                            {t<string>('Customer service')}
                        </MenuIconicItemLinkStyled>
                    </NextLink>
                </MenuIconicItemStyled>
                <MenuIconicItemStyled>
                    <NextLink href="/" passHref>
                        <MenuIconicItemLinkStyled>
                            <MenuIconicItemIconStyled icon="Marker" />
                            {t<string>('Stores')}
                        </MenuIconicItemLinkStyled>
                    </NextLink>
                </MenuIconicItemStyled>
                <MenuIconicItemStyled>
                    <NextLink href="/" passHref>
                        <MenuIconicItemLinkStyled>
                            <MenuIconicItemIconStyled icon="User" />
                            {t<string>('Sign in')}
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
