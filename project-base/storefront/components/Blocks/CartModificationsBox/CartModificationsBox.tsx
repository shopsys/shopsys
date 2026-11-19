import { Alert } from 'components/Basic/Alert/Alert';
import { WarningIcon } from 'components/Basic/Icon/WarningIcon';
import { getCartModificationMessages } from 'connectors/cart/Cart';
import { TIDs } from 'cypress/tids';
import { useRef } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const CART_MODIFICATIONS_ID = 'cart-modifications';

/**
 * Explains what the API changed in the cart (prices, availability, transport, codes) until the customer dismisses it.
 * The wrapper stays in the page as a live region, so screen readers announce modifications that arrive later.
 */
export const CartModificationsBox: FC = ({ className }) => {
    const { t } = useTranslation();
    const cartModificationsBatches = useSessionStore((store) => store.cartModificationsBatches);
    const dismissCartModifications = useSessionStore((store) => store.dismissCartModifications);
    const liveRegionRef = useRef<HTMLDivElement>(null);

    const messages = Array.from(
        new Set(cartModificationsBatches.flatMap((batch) => getCartModificationMessages(batch.modifications, t))),
    );

    // The close button disappears with the box, so the focus stays where the box was
    const dismissAndKeepFocusInPlace = () => {
        dismissCartModifications();
        liveRegionRef.current?.focus();
    };

    return (
        <div id={CART_MODIFICATIONS_ID} ref={liveRegionRef} role="status" tabIndex={-1}>
            {messages.length > 0 && (
                <Alert
                    className={className}
                    closeButtonTid={TIDs.cart_modifications_box_close_button}
                    icon={WarningIcon}
                    tid={TIDs.cart_modifications_box}
                    title={t('Your cart has changed')}
                    variant="warning"
                    onClose={dismissAndKeepFocusInPlace}
                >
                    <ul className="list-disc pl-4 text-text-less">
                        {messages.map((message) => (
                            <li key={message}>{message}</li>
                        ))}
                    </ul>
                </Alert>
            )}
        </div>
    );
};
