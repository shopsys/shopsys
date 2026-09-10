import { PlusIcon } from 'components/Basic/Icon/PlusIcon';
import { TagIcon } from 'components/Basic/Icon/TagIcon';
import { TIDs } from 'cypress/tids';
import dynamic from 'next/dynamic';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';

const ApplyCodeForm = dynamic(() => import('./ApplyCodeForm').then((component) => component.ApplyCodeForm), {
    ssr: false,
});

export const PromoCode: FC = () => {
    const { promoCodes, giftVouchers } = useCurrentCart();
    const { t } = useTranslation();
    const hasAppliedCode = promoCodes.length > 0 || giftVouchers.length > 0;
    const [isContentVisible, setIsContentVisible] = useState(hasAppliedCode);
    const [wasContentRequested, setWasContentRequested] = useState(hasAppliedCode);

    const togglePromoCodeVisibility = () => {
        if (!isContentVisible) {
            setWasContentRequested(true);
        }

        setIsContentVisible(!isContentVisible);
    };

    return (
        <div className="flex w-full flex-col overflow-hidden rounded-xl bg-background-more transition-colors hover:bg-background-most">
            <button
                aria-expanded={isContentVisible}
                aria-controls="apply-code-form"
                aria-label={t('Toggle promo code', { ns: 'accessibility' })}
                className={twJoin(
                    'flex w-full cursor-pointer items-center justify-between gap-3 px-4 py-3 text-left font-secondary font-semibold text-sm text-text-default outline-hidden transition-colors hover:text-link-default focus-visible:outline-2 focus-visible:outline-input-border-active focus-visible:-outline-offset-2',
                    isContentVisible ? 'rounded-t-xl' : 'rounded-xl',
                )}
                data-tid={TIDs.blocks_promocode_add_button}
                type="button"
                onClick={togglePromoCodeVisibility}
            >
                <span className="flex min-w-0 items-center gap-2">
                    <TagIcon aria-hidden className="size-4 shrink-0 text-link-default" />
                    <span>{t('Discount coupon or gift voucher')}</span>
                </span>

                <PlusIcon
                    className={twJoin(
                        'size-3.5 shrink-0 text-link-default transition-transform duration-200',
                        isContentVisible && 'rotate-45',
                    )}
                />
            </button>

            {wasContentRequested && <ApplyCodeForm isContentVisible={isContentVisible} />}
        </div>
    );
};
