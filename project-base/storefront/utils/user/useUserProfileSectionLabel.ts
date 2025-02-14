import { useAuthorization } from 'components/providers/AuthorizationProvider';
import useTranslation from 'next-translate/useTranslation';

export const useUserProfileSectionLabel = (): string => {
    const { t } = useTranslation();
    const { canManagePersonalData } = useAuthorization();

    return canManagePersonalData ? t('Edit profile') : t('My profile');
};
