import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { Link } from 'components/Basic/Link/Link';
import { Popup } from 'components/Layout/Popup/Popup';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';

type ProductComparePopupProps = {
    isVisible: boolean;
    onCloseCallback: () => void;
};

export const ProductComparePopup: FC<ProductComparePopupProps> = ({ isVisible, onCloseCallback }) => {
    const t = useTypedTranslationFunction();
    const { url } = useDomainConfig();
    const [productsComparisonUrl] = getInternationalizedStaticUrls(['/products-comparison'], url);

    return (
        <Popup isVisible={isVisible} onCloseCallback={onCloseCallback}>
            <Heading type="h3">{t('Comparison')}</Heading>

            <div className="flex flex-col">
                <p className="text-bigger font-semiBold mt-[15px] mb-5">{t('Product added to comparison.')}</p>
                <Link isButton href={productsComparisonUrl}>
                    <>
                        <span>{t('Show products comparison')}</span>
                        <Icon className="rotate-90" iconType="icon" icon="ArrowSecondary" />
                    </>
                </Link>
            </div>
        </Popup>
    );
};

ProductComparePopup.displayName = 'ProductComparePopup';
