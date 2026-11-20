import { useAfterUserEntry } from 'utils/app/useAfterUserEntry';
import { useHtmlLangLoader } from 'utils/app/useHtmlLangLoader';
import { useCartModificationsSideEffects } from 'utils/cart/useCartModificationsSideEffects';
import { useReloadCart } from 'utils/cart/useReloadCart';

export const SecondaryLoaders = () => {
    useReloadCart();
    useCartModificationsSideEffects();
    useAfterUserEntry();
    useHtmlLangLoader();

    return null;
};
