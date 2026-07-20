import { Drawer } from 'components/Basic/Drawer/Drawer';
import { UserMenu } from 'components/Blocks/UserMenu/UserMenu';
import { MenuIconicItemUserUnauthenticatedContent } from 'components/Layout/Header/MenuIconic/MenuIconicItemUserUnauthenticatedContent';

type MobileBottomAccountDrawerProps = {
    isActive: boolean;
    isUserLoggedIn: boolean;
    setIsActive: (isActive: boolean) => void;
    title: string;
    onClose: () => void;
};

export const MobileBottomAccountDrawer: FC<MobileBottomAccountDrawerProps> = ({
    isActive,
    isUserLoggedIn,
    setIsActive,
    title,
    onClose,
}) => (
    <Drawer isActive={isActive} setIsActive={setIsActive} title={title}>
        {isUserLoggedIn ? (
            <UserMenu />
        ) : (
            <MenuIconicItemUserUnauthenticatedContent
                loginFormName="mobile-bottom-navigation-login-form"
                onMenuClose={onClose}
            />
        )}
    </Drawer>
);
