import {
    NotificationBarsBlockStyled,
    NotificationBarsImageStyled,
    NotificationBarsStyled,
} from './NotificationBars.style';
import { Image } from 'components/Basic/Image/Image';
import { Button } from 'components/Forms/Button/Button';
import { Webline } from 'components/Layout/Webline/Webline';
import { Theme } from 'components/Theme/main';
import { useNotificationBars } from 'connectors/notificationBars/NotificationBars';
import { useAuth } from 'hooks/auth/useAuth';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import decode from 'jwt-decode';
import Trans from 'next-translate/Trans';
import { parseCookies } from 'nookies';
import { FC, useEffect, useState } from 'react';
import { useTheme } from 'styled-components';
import { NotificationBarsType } from 'types/notificationBars';

export const NotificationBars: FC = () => {
    const notificationBarItems = useNotificationBars();
    const { isUserLoggedIn, user } = useCurrentUserData();
    const [isAdminLoggedInAsUser, setIsAdminLoggedAsUser] = useState(false);
    const theme = useTheme() as Theme;
    const { logout } = useAuth();

    useEffect(() => {
        try {
            const cookies = parseCookies();
            const decodedAccessToken = decode<Record<string, any>>(cookies.accessToken);
            if ('administratorUuid' in decodedAccessToken && decodedAccessToken.administratorUuid !== null) {
                setIsAdminLoggedAsUser(true);
                return;
            }
            setIsAdminLoggedAsUser(false);
        } catch (e) {
            setIsAdminLoggedAsUser(false);
        }
    }, [isUserLoggedIn]);

    const extendByAdminLoggedInAsUserNotificationBar = (
        mappedNotificationBarItems: NotificationBarsType[],
        shouldExtend: boolean,
    ) => {
        if (shouldExtend) {
            mappedNotificationBarItems.push({
                text: (
                    <Trans
                        i18nKey="adminLoggedInAsCustomerWarning"
                        defaultTrans="Warning! You are logged in as a customer with the email {{ email }} <button>Log out</button>"
                        values={{ email: user?.email }}
                        components={{
                            button: (
                                <Button
                                    type="button"
                                    size="small"
                                    variant="secondary"
                                    style={{ marginLeft: '10px' }}
                                    onClick={logout}
                                ></Button>
                            ),
                        }}
                    />
                ),
                rgbColor: theme.color.red,
                image: null,
            });
        }

        return mappedNotificationBarItems;
    };

    return (
        <>
            {extendByAdminLoggedInAsUserNotificationBar(notificationBarItems, isAdminLoggedInAsUser).map(
                (item, index) => {
                    return (
                        <NotificationBarsStyled key={index} backgroundColor={item.rgbColor}>
                            <Webline>
                                <NotificationBarsBlockStyled backgroundColor={item.rgbColor}>
                                    {item.image !== null && (
                                        <NotificationBarsImageStyled>
                                            <Image image={item.image} type="default" alt="" />
                                        </NotificationBarsImageStyled>
                                    )}
                                    {typeof item.text === 'string' ? (
                                        <div dangerouslySetInnerHTML={{ __html: item.text }} />
                                    ) : (
                                        item.text
                                    )}
                                </NotificationBarsBlockStyled>
                            </Webline>
                        </NotificationBarsStyled>
                    );
                },
            )}
        </>
    );
};
