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
import { AddToCartPopupDataType } from 'components/Blocks/Product/AddToCartPopup/types';
import { FC } from 'react';
import { formatPrice } from 'utils/formatting';
import Image from 'components/Basic/Image';
import NextLink from 'next/link';
import Popup from 'components/Layout/Popup';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type AddToCartPopupProps = {
    isVisible: boolean;
    onCloseCallback: () => void;
    product: AddToCartPopupDataType;
};

const AddToCartPopup: FC<AddToCartPopupProps> = (props) => {
    const t = useTypedTranslationFunction();
    const domainConfig = useShopsysSelector((state) => state.domain);
    const [cartUrl] = useGetInternationalizedStaticUrls(['/cart'], domainConfig.url);

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
            <ProductStyled>
                {props.product.image !== null && (
                    <ImageStyled>
                        <Image image={props.product.image} alt={props.product.name} />
                    </ImageStyled>
                )}
                <ContentStyled>
                    <NameStyled>
                        <NextLink href={props.product.slug}>{props.product.name}</NextLink>
                    </NameStyled>
                    <PriceInfoStyled>
                        <PriceStyled>
                            {`${props.product.quantity} ${props.product.unitName}, ${formatPrice(
                                props.product.quantity * props.product.price.priceWithVat,
                                props.product.price.currencyCode,
                                t,
                            )}`}
                        </PriceStyled>
                    </PriceInfoStyled>
                </ContentStyled>
            </ProductStyled>

            <ButtonsStyled>
                <ButtonStyled onClick={props.onCloseCallback} type="button">
                    {t('Back to shop')}
                </ButtonStyled>
                <LinkStyled href={cartUrl} isButton={true}>
                    {t('To cart')}
                </LinkStyled>
            </ButtonsStyled>
        </Popup>
    );
};

export default AddToCartPopup;
