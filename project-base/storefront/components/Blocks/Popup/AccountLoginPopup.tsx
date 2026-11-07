import { MenuIconicItemUserUnauthenticatedContent } from 'components/Layout/Header/MenuIconic/MenuIconicItemUserUnauthenticatedContent';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { LoginPopupLayout } from './LoginPopupLayout';

export const AccountLoginPopup: FC<{ loginFormName: string }> = ({ loginFormName }) => {
    const { t } = useTranslation();
    const closePortalContent = useSessionStore((s) => s.closePortalContent);

    return (
        <LoginPopupLayout isWide focusFallbackSelector='button[name="account-login"]' title={t('Login')}>
            <MenuIconicItemUserUnauthenticatedContent loginFormName={loginFormName} onMenuClose={closePortalContent} />
        </LoginPopupLayout>
    );
};
