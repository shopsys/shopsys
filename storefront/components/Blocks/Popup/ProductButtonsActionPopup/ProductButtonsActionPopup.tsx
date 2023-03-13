import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { Link } from 'components/Basic/Link/Link';
import { Popup } from 'components/Layout/Popup/Popup';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';

type ProductButtonsActionPopupProps = {
    isVisible: boolean;
    onCloseCallback: (a: boolean) => void;
};

export const ProductButtonsActionPopup: FC<ProductButtonsActionPopupProps> = ({ isVisible, onCloseCallback }) => {
    const t = useTypedTranslationFunction();
    const domainConfig = useShopsysSelector((state) => state.domain);
    const [productsComparisonUrl] = getInternationalizedStaticUrls(['/products-comparison'], domainConfig.url);

    return (
        <Popup isVisible={isVisible} onCloseCallback={() => onCloseCallback(false)}>
            <Heading type="h3">{t('Comparison')}</Heading>

            <div className="flex flex-col">
                <p className="text-bigger font-semiBold mt-[15px] mb-5">{t('Product added to comparison.')}</p>
                <Link isButton href={productsComparisonUrl} variant="primary">
                    <span>{t('Show products comparison')}</span>
                    <Icon className="rotate-90" iconType="icon" icon="ArrowSecondary" />
                </Link>
            </div>
        </Popup>
    );
};

ProductButtonsActionPopup.displayName = 'ProductButtonsActionPopup';

export default ProductButtonsActionPopup;
