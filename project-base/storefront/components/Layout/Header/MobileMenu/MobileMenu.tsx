import { Drawer } from 'components/Basic/Drawer/Drawer';
import { HamburgerMenu } from 'components/Layout/Header/HamburgerMenu/HamburgerMenu';
import { useNavigationQuery } from 'graphql/requests/navigation/queries/NavigationQuery.generated';
import { useState } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { MobileMenuContent } from './MobileMenuContent';

type MobileMenuProps = {
    shouldRenderTrigger?: boolean;
    isMenuOpened?: boolean;
    onMenuToggle?: () => void;
};

export const MobileMenu: FC<MobileMenuProps> = ({
    shouldRenderTrigger = true,
    isMenuOpened: controlledIsMenuOpened,
    onMenuToggle,
}) => {
    const { t } = useTranslation();
    const [{ data: navigationData }] = useNavigationQuery();
    const [uncontrolledIsMenuOpened, setUncontrolledIsMenuOpened] = useState(false);
    const isMenuOpened = controlledIsMenuOpened ?? uncontrolledIsMenuOpened;
    const setIsMenuOpened =
        onMenuToggle ?? (() => setUncontrolledIsMenuOpened((currentIsMenuOpened) => !currentIsMenuOpened));

    if (!navigationData?.navigation.length) {
        return null;
    }

    const handleMenuToggle = () => setIsMenuOpened();
    const handleMenuActiveChange = (nextIsMenuOpened: boolean) => {
        if (nextIsMenuOpened !== isMenuOpened) {
            setIsMenuOpened();
        }
    };

    return (
        <>
            {shouldRenderTrigger && <HamburgerMenu isOpen={isMenuOpened} onClick={handleMenuToggle} />}

            <Drawer
                ariaLabel={t('Mobile navigation menu', { ns: 'accessibility' })}
                className="z-maximum w-78 overflow-x-hidden p-0"
                isActive={isMenuOpened}
                setIsActive={handleMenuActiveChange}
                shouldRenderHeader={false}
            >
                <MobileMenuContent navigationItems={navigationData.navigation} onMenuToggleHandler={handleMenuToggle} />
            </Drawer>
        </>
    );
};
