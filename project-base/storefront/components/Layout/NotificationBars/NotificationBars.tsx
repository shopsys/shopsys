import { Image } from 'components/Basic/Image/Image';
import { Button } from 'components/Forms/Button/Button';
import { Webline } from 'components/Layout/Webline/Webline';
import { useNotificationBarsApi } from 'graphql/generated';
import { useAuth } from 'hooks/auth/useAuth';

import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import decode from 'jwt-decode';
import Trans from 'next-translate/Trans';
import { parseCookies } from 'nookies';
import { memo, useEffect, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import tinycolor from 'tinycolor2';

export const NotificationBars: FC = memo(function NotificationBars() {
    const [{ data: notificationBarsData }] = useNotificationBarsApi();
    const { isUserLoggedIn, user } = useCurrentUserData();
    const [loggedAsUserEmail, setLoggedAsUserEmail] = useState<string>();
    const bars = notificationBarsData?.notificationBars;
    const { logout } = useAuth();

    useEffect(() => {
        const cookies = parseCookies(undefined, { decode }) as unknown as {
            accessToken?: { administratorUuid: string };
        } | null;
        const isUserAdmin = !!cookies?.accessToken?.administratorUuid;

        setLoggedAsUserEmail(isUserAdmin ? user!.email : undefined);
    }, [isUserLoggedIn, user]);

    return (
        <>
            {bars?.map((item, index) => (
                <div className="py-2" style={{ backgroundColor: item.rgbColor }} key={index}>
                    <Webline>
                        <div
                            className={twJoin(
                                'flex items-center justify-center text-center text-sm font-bold',
                                tinycolor(item.rgbColor).isLight() ? 'text-dark' : 'text-white',
                            )}
                        >
                            {!!item.mainImage && (
                                <div className="mr-3 flex w-11">
                                    <Image
                                        image={item.mainImage}
                                        type="default"
                                        alt={item.mainImage.name || item.text}
                                        className="mr-3"
                                    />
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
            ))}
            {loggedAsUserEmail && (
                <div className="bg-red py-2">
                    <Webline>
                        <div className="flex items-center justify-center text-center text-sm font-bold text-dark">
                            <Trans
                                i18nKey="adminLoggedInAsCustomerWarning"
                                defaultTrans="Warning! You are logged in as a customer with the email {{ email }} <button>Log out</button>"
                                values={{ email: loggedAsUserEmail }}
                                components={{
                                    button: (
                                        <Button
                                            type="button"
                                            size="small"
                                            variant="secondary"
                                            style={{ marginLeft: '10px' }}
                                            onClick={logout}
                                        />
                                    ),
                                }}
                            />
                        </div>
                    </Webline>
                </div>
            )}
        </>
    );
});
