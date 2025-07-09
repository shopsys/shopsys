import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { Popup } from 'components/Layout/Popup/Popup';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import useTranslation from 'next-translate/useTranslation';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const ProductComparePopup: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [productComparisonUrl] = getInternationalizedStaticUrls(['/product-comparison'], url);

    return (
        <Popup title={t('Comparison')}>
            <div className="flex flex-col">
                <p className="font-semiBold mt-[15px] mb-5">{t('Product added to comparison.')}</p>

                <LinkButton href={productComparisonUrl} type="comparison">
                    <span>{t('Show products comparison')}</span>
                    <ArrowSecondaryIcon className="-rotate-90" />
                </LinkButton>
            </div>
        </Popup>
    );
};
