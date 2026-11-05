import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { AccountLoginPopup } from 'components/Blocks/Popup/AccountLoginPopup';
import { TIDs } from 'cypress/tids';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { MenuIconicItemLink } from './MenuIconicElements';

type MenuIconicItemUserUnauthenticatedProps = {
    loginFormName: string;
    shouldShowLabel?: boolean;
};

export const MenuIconicItemUserUnauthenticated: FC<MenuIconicItemUserUnauthenticatedProps> = ({
    loginFormName,
    shouldShowLabel = true,
}) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const isActive = useSessionStore((s) => s.portalContent?.type === AccountLoginPopup);

    return (
        <MenuIconicItemLink
            ariaExpanded={isActive}
            ariaHaspopup="dialog"
            ariaLabel={t('Show registration and login popup', { ns: 'accessibility' })}
            className="min-size-12 cursor-pointer text-nowrap focus-visible:outline-2 focus-visible:outline-offset-2"
            tid={TIDs.my_account_link}
            title={t('Login form')}
            onClick={(event) => {
                // Safari does not focus buttons on pointer clicks; the popup must restore focus here too.
                event.currentTarget.focus({ preventScroll: true });
                updatePortalContent(<AccountLoginPopup loginFormName={loginFormName} />);
            }}
        >
            <span
                className="flex flex-col items-center gap-1"
                data-tid={TIDs.layout_header_menuiconic_login_link_popup}
            >
                <UserIcon className="size-6" />
                {shouldShowLabel && <span className="hidden lg:inline-block">{t('Account')}</span>}
            </span>
        </MenuIconicItemLink>
    );
};
