import {
    AddToCartPopupWrapperStyled,
    ButtonsStyled,
    ButtonStyled,
    Checkmark,
    ContentStyled,
    HeadingStyled,
    ImageStyled,
    LinkStyled,
    NameStyled,
    PriceInfoStyled,
    PriceStyled,
    ProductStyled,
} from './AddToCartPopup.style';
import { Image } from 'components/Basic/Image/Image';
import { Popup } from 'components/Layout/Popup/Popup';
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
            wrapperComponent={AddToCartPopupWrapperStyled}
            hideCloseButton
        >
            <HeadingStyled type="h2">
                <Checkmark alt="" iconType="icon" icon="Checkmark" />
                {t('Great choice! We have added your item to the cart')}
            </HeadingStyled>
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
                <ButtonStyled onClick={onCloseCallback} type="button" data-testid={TEST_IDENTIFIER + '-button-back'}>
                    {t('Back to shop')}
                </ButtonStyled>
                <LinkStyled href={cartUrl} isButton>
                    {t('To cart')}
                </LinkStyled>
            </ButtonsStyled>
        </Popup>
    );
};
