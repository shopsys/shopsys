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
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import NextLink from 'next/link';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';
import { AddToCartPopupDataType } from 'types/cart';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';

type AddToCartPopupProps = {
    isVisible: boolean;
    onCloseCallback: () => void;
    product: AddToCartPopupDataType;
};

export const AddToCartPopup: FC<AddToCartPopupProps> = (props) => {
    const testIdentifier = 'blocks-product-addtocartpopup-product';

    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();
    const domainConfig = useShopsysSelector((state) => state.domain);
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], domainConfig.url);

    return (
        <Popup
            isVisible={props.isVisible}
            onCloseCallback={props.onCloseCallback}
            wrapperComponent={AddToCartPopupWrapperStyled}
            hideCloseButton={true}
        >
            <HeadingStyled type="h2">
                <Checkmark iconType="icon" icon="Checkmark" />
                {t('Great choice! We have added your item to the cart')}
            </HeadingStyled>
            <ProductStyled data-testid={testIdentifier}>
                {props.product.image !== null && (
                    <ImageStyled>
                        <Image image={props.product.image} type="thumbnailMedium" alt={props.product.fullName} />
                    </ImageStyled>
                )}
                <ContentStyled>
                    <NameStyled data-testid={testIdentifier + '-name'}>
                        <NextLink href={props.product.slug}>{props.product.fullName}</NextLink>
                    </NameStyled>
                    <PriceInfoStyled>
                        <PriceStyled data-testid={testIdentifier + '-price'}>
                            {`${props.product.quantity} ${props.product.unitName}, ${formatPrice(
                                props.product.quantity * props.product.price.priceWithVat,
                            )}`}
                        </PriceStyled>
                    </PriceInfoStyled>
                </ContentStyled>
            </ProductStyled>

            <ButtonsStyled>
                <ButtonStyled
                    onClick={props.onCloseCallback}
                    type="button"
                    data-testid={testIdentifier + '-button-back'}
                >
                    {t('Back to shop')}
                </ButtonStyled>
                <LinkStyled href={cartUrl} isButton={true}>
                    {t('To cart')}
                </LinkStyled>
            </ButtonsStyled>
        </Popup>
    );
};
