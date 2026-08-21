import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { TIDs } from 'cypress/tids';
import dynamic from 'next/dynamic';
import { useState } from 'react';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';

const PromoCodeForm = dynamic(() => import('./PromoCodeForm').then((component) => component.PromoCodeForm), {
    ssr: false,
});

export const PromoCode: FC = () => {
    const { promoCodes } = useCurrentCart();
    const { t } = useTranslation();
    const hasAppliedPromoCode = promoCodes.length > 0;
    const [isContentVisible, setIsContentVisible] = useState(hasAppliedPromoCode);
    const [wasContentRequested, setWasContentRequested] = useState(hasAppliedPromoCode);

    const toggleContentVisibility = () => {
        if (!isContentVisible) {
            setWasContentRequested(true);
        }

        setIsContentVisible((currentValue) => !currentValue);
    };

    if (hasAppliedPromoCode) {
        return null;
    }

    return (
        <div className="flex flex-col gap-2.5">
            <div data-tid={TIDs.blocks_promocode_add_button}>
                <Checkbox
                    aria-expanded={isContentVisible}
                    aria-label={t('Toggle promo code', { ns: 'accessibility' })}
                    id="promo-code"
                    data-tid={TIDs.blocks_promocode_add_button}
                    label={t('I have a discount coupon')}
                    value={isContentVisible}
                    onChange={toggleContentVisibility}
                />
            </div>

            {wasContentRequested && <PromoCodeForm isContentVisible={isContentVisible} />}
        </div>
    );
};
