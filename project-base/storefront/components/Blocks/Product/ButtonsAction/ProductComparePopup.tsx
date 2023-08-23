import { Heading } from 'components/Basic/Heading/Heading';
import { ArrowSecondaryIcon } from 'components/Basic/Icon/IconsSvg';
import { Link } from 'components/Basic/Link/Link';
import { Popup } from 'components/Layout/Popup/Popup';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import useTranslation from 'next-translate/useTranslation';
import { useDomainConfig } from 'hooks/useDomainConfig';

type ProductComparePopupProps = {
    onCloseCallback: () => void;
};

export const ProductComparePopup: FC<ProductComparePopupProps> = ({ onCloseCallback }) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [productComparisonUrl] = getInternationalizedStaticUrls(['/product-comparison'], url);

    return (
        <Popup onCloseCallback={onCloseCallback}>
            <Heading type="h3">{t('Comparison')}</Heading>

            <div className="flex flex-col">
                <p className="text-bigger font-semiBold mt-[15px] mb-5">{t('Product added to comparison.')}</p>
                <Link isButton href={productComparisonUrl}>
                    <>
                        <span>{t('Show products comparison')}</span>
                        <ArrowSecondaryIcon className="rotate-90" />
                    </>
                </Link>
            </div>
        </Popup>
    );
};

ProductComparePopup.displayName = 'ProductComparePopup';
