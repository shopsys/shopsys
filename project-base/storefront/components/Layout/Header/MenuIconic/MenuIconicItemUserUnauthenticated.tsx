import { MenuIconicItemLink } from './MenuIconicElements';
import { Heading } from 'components/Basic/Heading/Heading';
import { UserIcon } from 'components/Basic/Icon/IconsSvg';
import { Login } from 'components/Blocks/Popup/Login/Login';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useState } from 'react';

const Popup = dynamic(() => import('components/Layout/Popup/Popup').then((component) => component.Popup));

export const MenuIconicItemUserUnauthenticated: FC = ({ dataTestId }) => {
    const { t } = useTranslation();
    const [isLoginPopupOpened, setIsLoginPopupOpened] = useState(false);
    const handleLogin = () => setIsLoginPopupOpened(true);

    return (
        <>
            <MenuIconicItemLink
                className="cursor-pointer"
                dataTestId={dataTestId + '-link-popup'}
                onClick={handleLogin}
            >
                <UserIcon className="w-5 lg:w-4" />
                <span className="hidden lg:inline-block">{t('Login')}</span>
            </MenuIconicItemLink>

            {isLoginPopupOpened && (
                <Popup onCloseCallback={() => setIsLoginPopupOpened(false)}>
                    <Heading type="h2">{t('Login')}</Heading>
                    <Login />
                </Popup>
            )}
        </>
    );
};
