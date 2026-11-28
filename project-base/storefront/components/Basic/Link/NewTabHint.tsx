import useTranslation from 'utils/i18n/useTranslationWrapper';

/**
 * Tells screen reader users that a link opens in a new tab. Render it after the link content.
 */
export const NewTabHint: FC = () => {
    const { t } = useTranslation();

    return <span className="sr-only"> ({t('Open in new tab')})</span>;
};

/**
 * An `aria-label` replaces the link content, so it has to carry the new tab hint itself.
 */
export const useGetNewTabAriaLabel = () => {
    const { t } = useTranslation();

    const getNewTabAriaLabel = (ariaLabel: string | undefined, target: string | undefined) => {
        if (!ariaLabel || target !== '_blank') {
            return ariaLabel;
        }

        return `${ariaLabel} (${t('Open in new tab')})`;
    };

    return getNewTabAriaLabel;
};
