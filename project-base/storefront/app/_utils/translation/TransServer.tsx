import { TransProps } from './TransProps';
import formatElements from './formatElements';
import { getTranslation } from 'app/_utils/translation/getTranslation';

/**
 * Translate transforming:
 * <0>This is an <1>example</1><0>
 * to -> <h1>This is an <b>example</b><h1>
 */
export default async function TransServer({ i18nKey, values, components, fallback, defaultTrans }: TransProps) {
    const t = await getTranslation();

    /**
     * Memoize the transformation
     */
    const text = t(i18nKey, values, {
        fallback,
        default: defaultTrans,
    });

    if (!text) {
        return text;
    }

    if (!components || components.length === 0) {
        return Array.isArray(text) ? text.map((item) => item) : text;
    }

    if (Array.isArray(text)) {
        return text.map((item) => formatElements(item, components));
    }

    return formatElements(text, components);
}
