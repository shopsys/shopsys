import useTranslation from 'next-translate/useTranslation';
import { MenuIconicItemLink } from './MenuIconicElements';
import { Heading } from 'components/Basic/Heading/Heading';
import { Login } from 'components/Blocks/Popup/Login/Login';
import { useState } from 'react';
import dynamic from 'next/dynamic';
import { UserIcon } from 'components/Basic/Icon/IconsSvg';

const Popup = dynamic(() => import('components/Layout/Popup/Popup').then((component) => component.Popup));

export const MenuIconicItemUserUnauthenticated: FC = ({ dataTestId }) => {
    const { t } = useTranslation();
    const [isLoginPopupOpened, setIsLoginPopupOpened] = useState(false);
    const handleLogin = () => setIsLoginPopupOpened(true);

    return (
        <>
            <MenuIconicItemLink
                onClick={handleLogin}
                className="cursor-pointer"
                dataTestId={dataTestId + '-link-popup'}
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
