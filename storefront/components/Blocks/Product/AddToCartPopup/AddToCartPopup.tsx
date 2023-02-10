import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { Image } from 'components/Basic/Image/Image';
import { Link } from 'components/Basic/Link/Link';
import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { mapPriceForCalculations } from 'helpers/mappers/price';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import NextLink from 'next/link';
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
        <Popup isVisible={isVisible} onCloseCallback={onCloseCallback} className="w-11/12 max-w-2xl" hideCloseButton>
            <Heading type="h2" className="mt-4 mb-4 flex w-full items-center text-xl normal-case text-primary md:mb-6">
                <Icon iconType="icon" icon="Checkmark" width={30} height={30} className="mr-4" />
                {t('Great choice! We have added your item to the cart')}
            </Heading>
            <div
                className="mb-4 flex flex-col items-center rounded border border-greyLighter p-3 md:flex-row md:p-4"
                data-testid={TEST_IDENTIFIER}
            >
                {product.image !== null && (
                    <div className="mb-4 flex w-24 items-center justify-center md:mb-0">
                        <Image image={product.image} type="thumbnailMedium" alt={product.fullName} />
                    </div>
                )}
                <div className="w-full md:pl-4 lg:flex lg:items-center lg:justify-between">
                    <div className="block break-words text-primary" data-testid={TEST_IDENTIFIER + '-name'}>
                        <NextLink href={product.slug}>{product.fullName}</NextLink>
                    </div>
                    <div className="mt-2 lg:mt-0 lg:w-5/12 lg:pl-4 lg:text-right">
                        <div className="block text-primary" data-testid={TEST_IDENTIFIER + '-price'}>
                            {`${product.quantity} ${product.unitName}, ${formatPrice(
                                product.quantity * mapPriceForCalculations(product.price.priceWithVat),
                            )}`}
                        </div>
                    </div>
                </div>
            </div>

            <div className="-mt-2 -mr-2 flex flex-col text-center md:flex-row md:items-center md:justify-between md:p-0">
                <Button
                    onClick={onCloseCallback}
                    type="button"
                    testIdentifier={TEST_IDENTIFIER + '-button-back'}
                    className="mt-2 mr-2 lg:w-auto lg:justify-start"
                >
                    {t('Back to shop')}
                </Button>
                <Link className="mt-2 mr-2 lg:w-auto lg:justify-start" href={cartUrl} isButton>
                    {t('To cart')}
                </Link>
            </div>
        </Popup>
    );
};
