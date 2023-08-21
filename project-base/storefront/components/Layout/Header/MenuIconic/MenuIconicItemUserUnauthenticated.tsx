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
                className="cursor-pointer max-lg:hidden"
                dataTestId={dataTestId + '-link-popup'}
            >
                <UserIcon className="mr-2 w-4 text-white" />
                {t('Login')}
            </MenuIconicItemLink>

            <div className="order-2 ml-1 flex h-9 w-9 cursor-pointer items-center justify-center text-lg outline-none lg:hidden">
                <div
                    className="relative flex h-full w-full items-center justify-center text-white transition-colors"
                    onClick={handleLogin}
                >
                    <UserIcon className="w-4 text-white" />
                </div>
            </div>

            {isLoginPopupOpened && (
                <Popup onCloseCallback={() => setIsLoginPopupOpened(false)}>
                    <Heading type="h2">{t('Login')}</Heading>
                    <Login />
                </Popup>
            )}
        </>
    );
};