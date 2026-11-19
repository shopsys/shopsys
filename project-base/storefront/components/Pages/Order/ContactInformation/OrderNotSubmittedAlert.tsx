import { Alert } from 'components/Basic/Alert/Alert';
import { WarningIcon } from 'components/Basic/Icon/WarningIcon';
import { linkPlaceholderTwClass } from 'components/Basic/Link/Link';
import { CART_MODIFICATIONS_ID } from 'components/Blocks/CartModificationsBox/CartModificationsBox';
import { TIDs } from 'cypress/tids';
import { useEffect, useRef } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';

const focusCartModifications = () => document.getElementById(CART_MODIFICATIONS_ID)?.focus();

/**
 * Shown next to the submit button after the API refused the order because the cart changed.
 * The details are in the cart modifications box at the top of the page, which can be far away on mobile.
 */
export const OrderNotSubmittedAlert: FC = () => {
    const { t } = useTranslation();
    const alertRef = useRef<HTMLDivElement>(null);

    useEffect(() => alertRef.current?.focus(), []);

    return (
        <div className="mt-5" ref={alertRef} tabIndex={-1}>
            <Alert
                icon={WarningIcon}
                tid={TIDs.pages_order_not_submitted_alert}
                title={t('The order was not submitted')}
                variant="error"
            >
                <span className="text-text-less">
                    {t('Your cart changed in the meantime. Check the changes and submit the order again.')}{' '}
                    {/* A button, because the contact form blocks Enter on everything except buttons */}
                    <button className={linkPlaceholderTwClass} type="button" onClick={focusCartModifications}>
                        {t('Show changes')}
                    </button>
                </span>
            </Alert>
        </div>
    );
};
