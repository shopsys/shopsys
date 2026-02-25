import { useAfterUserEntry } from 'utils/app/useAfterUserEntry';
import { useHtmlLangLoader } from 'utils/app/useHtmlLangLoader';
import { useReloadCart } from 'utils/cart/useReloadCart';

export const SecondaryLoaders = () => {
    useReloadCart();
    useAfterUserEntry();
    useHtmlLangLoader();

    return null;
};
