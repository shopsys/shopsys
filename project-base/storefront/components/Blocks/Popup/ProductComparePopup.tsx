import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { Popup } from 'components/Layout/Popup/Popup';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const ProductComparePopup: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [productComparisonUrl] = getInternationalizedStaticUrls(['/product-comparison'], url);
    const successMessage = t('Product added to comparison.');

    return (
        <Popup ariaDescription={successMessage} role="alertdialog" title={t('Comparison')}>
            <div className="flex flex-col" data-tid={TIDs.comparison_popup}>
                <p className="mt-[15px] mb-5 font-semiBold">{successMessage}</p>

                <LinkButton href={productComparisonUrl} tid={TIDs.comparison_popup_link} type="comparison">
                    <span>{t('Show products comparison')}</span>
                    <ArrowSecondaryIcon className="-rotate-90" />
                </LinkButton>
            </div>
        </Popup>
    );
};
