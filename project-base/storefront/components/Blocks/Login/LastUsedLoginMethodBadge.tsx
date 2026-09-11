import { TIDs } from 'cypress/tids';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const LastUsedLoginMethodBadge: FC = () => {
    const { t } = useTranslation();

    return (
        <span
            data-tid={TIDs.last_used_login_method_badge}
            className="absolute -top-2.5 -right-2 z-above inline-flex w-fit items-center text-nowrap rounded-full bg-background-accent px-2 py-0.5 font-secondary font-semibold text-text-inverted text-xs"
        >
            {t('Last used')}
        </span>
    );
};
