import { MenuIconicItemLink } from './MenuIconicElements';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';

const LoginPopup = dynamic(() =>
    import('components/Blocks/Popup/LoginPopup').then((component) => component.LoginPopup),
);

export const MenuIconicItemUserUnauthenticated: FC = () => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const handleLogin = () => {
        updatePortalContent(<LoginPopup />);
    };

    return (
        <MenuIconicItemLink
            className="cursor-pointer lg:w-[72px]"
            tid={TIDs.layout_header_menuiconic_login_link_popup}
            onClick={handleLogin}
        >
            <UserIcon className="w-6 max-h-[22px]" />
            <span className="hidden lg:inline-block">{t('Login')}</span>
        </MenuIconicItemLink>
    );
};
