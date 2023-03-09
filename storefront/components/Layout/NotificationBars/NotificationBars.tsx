import { Image } from 'components/Basic/Image/Image';
import { Button } from 'components/Forms/Button/Button';
import { Webline } from 'components/Layout/Webline/Webline';
import { NotificationBarsFragmentApi, useNotificationBarsApi } from 'graphql/generated';
import { getFirstImageOrNull } from 'helpers/mappers/image';
import { LogoutHandler, useAuth } from 'hooks/auth/useAuth';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import decode from 'jwt-decode';
import Trans from 'next-translate/Trans';
import { parseCookies } from 'nookies';
import { useEffect, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import tinycolor from 'tinycolor2';
import { CurrentCustomerType } from 'types/customer';

export const NotificationBars: FC = () => {
    const [{ data: notificationBarsData }] = useQueryError(useNotificationBarsApi());
    const { isUserLoggedIn, user } = useCurrentUserData();
    const [isAdminLoggedInAsUser, setIsAdminLoggedAsUser] = useState(false);
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

    if (notificationBarsData?.notificationBars === undefined || notificationBarsData.notificationBars === null) {
        return null;
    }

    return (
        <>
            {extendByAdminLoggedInAsUserNotificationBar(
                notificationBarsData.notificationBars,
                isAdminLoggedInAsUser,
                user,
                logout,
            ).map((item, index) => {
                const firstImage = getFirstImageOrNull(item.images);

                return (
                    <div className="py-2" style={{ backgroundColor: item.rgbColor }} key={index}>
                        <Webline>
                            <div
                                className={twJoin(
                                    'flex items-center justify-center text-center text-sm font-bold',
                                    tinycolor(item.rgbColor).isLight() ? 'text-dark' : 'text-white',
                                )}
                            >
                                {!!firstImage && (
                                    <div className="mr-3 flex w-11">
                                        <Image image={firstImage} type="default" alt="" className="mr-3" />
                                    </div>
                                )}
                                {typeof item.text === 'string' ? (
                                    <div dangerouslySetInnerHTML={{ __html: item.text }} />
                                ) : (
                                    item.text
                                )}
                            </div>
                        </Webline>
                    </div>
                );
            })}
        </>
    );
};

const extendByAdminLoggedInAsUserNotificationBar = (
    mappedNotificationBarItems: (
        | NotificationBarsFragmentApi
        | (Omit<NotificationBarsFragmentApi, 'text'> & { text: JSX.Element })
    )[],
    shouldExtend: boolean,
    user: CurrentCustomerType | null | undefined,
    logout: LogoutHandler,
) => {
    if (shouldExtend) {
        mappedNotificationBarItems.push({
            __typename: 'NotificationBar',
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
            rgbColor: '#ec5353',
            images: [],
        });
    }

    return mappedNotificationBarItems;
};
