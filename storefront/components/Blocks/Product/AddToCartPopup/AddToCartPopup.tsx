import {
    ButtonsStyled,
    ButtonStyled,
    ContentStyled,
    ImageStyled,
    LinkStyled,
    NameStyled,
    PriceInfoStyled,
    PriceStyled,
    ProductStyled,
} from './AddToCartPopup.style';
import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { Image } from 'components/Basic/Image/Image';
import { Popup } from 'components/Layout/Popup/Popup';
import { PopupStyled } from 'components/Layout/Popup/Popup.style';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import NextLink from 'next/link';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';
import { AddToCartPopupDataType } from 'types/cart';

type AddToCartPopupProps = {
    isVisible: boolean;
    onCloseCallback: () => void;
    product: AddToCartPopupDataType;
};

const TEST_IDENTIFIER = 'blocks-product-addtocartpopup-product';

export const AddToCartPopup: FC<AddToCartPopupProps> = ({ isVisible, onCloseCallback, product }) => {
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();
    const domainConfig = useShopsysSelector((state) => state.domain);
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], domainConfig.url);

    return (
        <Popup
            isVisible={isVisible}
            onCloseCallback={onCloseCallback}
            wrapperComponent={PopupStyled}
            className="w-11/12 max-w-2xl"
            hideCloseButton
        >
            <Heading type="h2" className="mt-4 mb-4 flex w-full items-center text-xl normal-case text-primary md:mb-6">
                <Icon iconType="icon" icon="Checkmark" width={30} height={30} className="mr-4 block" />
                {t('Great choice! We have added your item to the cart')}
            </Heading>
            <ProductStyled data-testid={TEST_IDENTIFIER}>
                {product.image !== null && (
                    <ImageStyled>
                        <Image image={product.image} type="thumbnailMedium" alt={product.fullName} />
                    </ImageStyled>
                )}
                <ContentStyled>
                    <NameStyled data-testid={TEST_IDENTIFIER + '-name'}>
                        <NextLink href={product.slug}>{product.fullName}</NextLink>
                    </NameStyled>
                    <PriceInfoStyled>
                        <PriceStyled data-testid={TEST_IDENTIFIER + '-price'}>
                            {`${product.quantity} ${product.unitName}, ${formatPrice(
                                product.quantity * product.price.priceWithVat,
                            )}`}
                        </PriceStyled>
                    </PriceInfoStyled>
                </ContentStyled>
            </ProductStyled>

            <ButtonsStyled>
                <ButtonStyled onClick={onCloseCallback} type="button" testIdentifier={TEST_IDENTIFIER + '-button-back'}>
                    {t('Back to shop')}
                </ButtonStyled>
                <LinkStyled href={cartUrl} isButton>
                    {t('To cart')}
                </LinkStyled>
            </ButtonsStyled>
        </Popup>
    );
};
