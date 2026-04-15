import { Translate, TranslationQuery } from 'next-translate';
import { I18N_NAMESPACES } from './constants';
import origUseTranslation from './useTranslationOriginal';

type UseTranslationReturn = { t: Translate; lang: string };

export default function useTranslation(ns?: string): UseTranslationReturn {
    if (ns) {
        const { t, lang } = origUseTranslation(ns);
        return { t: t as Translate, lang };
    }

    const { t: tCommon, lang } = origUseTranslation();
    const { t: tAccessibility } = origUseTranslation(I18N_NAMESPACES.ACCESSIBILITY);

    const omitNs = (opts?: any) => {
        if (!opts || typeof opts !== 'object') {
            return opts;
        }
        const { ns: _, ...rest } = opts as any;
        return rest;
    };

    const t: Translate = (key: string | TemplateStringsArray, options?: TranslationQuery | null) => {
        const nsOpt = options?.ns as string | undefined;
        if (nsOpt === I18N_NAMESPACES.ACCESSIBILITY) {
            return tAccessibility(key, omitNs(options));
        }
        return tCommon(key, omitNs(options));
    };

    return { t, lang };
}
