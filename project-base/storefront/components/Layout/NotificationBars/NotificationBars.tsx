import { Image } from 'components/Basic/Image/Image';
import { Button } from 'components/Forms/Button/Button';
import { Webline } from 'components/Layout/Webline/Webline';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { useNotificationBars } from 'graphql/requests/notificationBars/queries/NotificationBarsQuery.generated';
import { getTokensFromCookies } from 'helpers/auth/tokens';
import { useAuth } from 'hooks/auth/useAuth';
import { jwtDecode } from 'jwt-decode';
import Trans from 'next-translate/Trans';
import { memo, useEffect, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import tinycolor from 'tinycolor2';

export const NotificationBars: FC = memo(function NotificationBars() {
    const [{ data: notificationBarsData }] = useNotificationBars();
    const user = useCurrentCustomerData();
    const [loggedAsUserEmail, setLoggedAsUserEmail] = useState<string>();
    const bars = notificationBarsData?.notificationBars;
    const { logout } = useAuth();

    useEffect(() => {
        const { accessToken: encodedAccessToken } = getTokensFromCookies();
        if (!encodedAccessToken) {
            return;
        }

        const decodedAccessToken = jwtDecode(encodedAccessToken) as { administratorUuid?: string };
        const isUserAdmin = !!decodedAccessToken.administratorUuid;

        setLoggedAsUserEmail(isUserAdmin ? user!.email : undefined);
    }, [user]);

    return (
        <>
            {bars?.map((item, index) => (
                <div key={index} className="py-2" style={{ backgroundColor: item.rgbColor }}>
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
                                        alt={item.mainImage.name || item.text}
                                        height={44}
                                        src={item.mainImage.url}
                                        width={44}
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
                                defaultTrans="Warning! You are logged in as a customer with the email {{ email }} <button>Log out</button>"
                                i18nKey="adminLoggedInAsCustomerWarning"
                                values={{ email: loggedAsUserEmail }}
                                components={{
                                    button: (
                                        <Button
                                            size="small"
                                            style={{ marginLeft: '10px' }}
                                            type="button"
                                            variant="secondary"
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
