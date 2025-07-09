import { MenuIcon } from 'components/Basic/Icon/MenuIcon';
import { UserMenu } from 'components/Blocks/UserMenu/UserMenu';
import { Button } from 'components/Forms/Button/Button';
import useTranslation from 'next-translate/useTranslation';
import { useSessionStore } from 'store/useSessionStore';

export const UserNavigation: FC = () => {
    const { t } = useTranslation();
    const setIsUserMenuOpen = useSessionStore((s) => s.setIsUserMenuOpen);

    return (
        <aside>
            <div className="min-w-[275px]">
                <UserMenu hideFocusTrap className="hidden lg:flex" />
            </div>

            <Button className="w-full lg:hidden" variant="secondary" onClick={() => setIsUserMenuOpen(true)}>
                <MenuIcon className="size-4" />
                {t('My menu')}
            </Button>
        </aside>
    );
};
