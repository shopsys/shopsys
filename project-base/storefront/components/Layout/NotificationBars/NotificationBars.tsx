import { Image } from 'components/Basic/Image/Image';
import { Button } from 'components/Forms/Button/Button';
import { Webline } from 'components/Layout/Webline/Webline';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import dayjs from 'dayjs';
import isBetween from 'dayjs/plugin/isBetween';
import { jwtDecode } from 'jwt-decode';
import Trans from 'next-translate/Trans';
import { memo, useEffect, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { getTokensFromCookies } from 'utils/auth/getTokensFromCookies';
import { useLogout } from 'utils/auth/useLogout';
import { getYIQContrastTextColor } from 'utils/colors/colors';
import { useCountdown } from 'utils/useCountdown';
import { useNotificationBarsWithRevalidation } from 'utils/useNotificationBarRevalidation';

dayjs.extend(isBetween);

const COUNTDOWN_REVALIDATION_INTERVAL = 10000;

export const NotificationBars: FC = memo(function NotificationBars() {
    const { activeNotificationBars, fetchNotificationBars, nextRevalidationTime } =
        useNotificationBarsWithRevalidation();
    const user = useCurrentCustomerData();
    const [loggedAsUserEmail, setLoggedAsUserEmail] = useState<string>();
    const logout = useLogout();

    useEffect(() => {
        const { accessToken: encodedAccessToken } = getTokensFromCookies();
        if (!encodedAccessToken) {
            return;
        }

        const decodedAccessToken = jwtDecode(encodedAccessToken) as { administratorUuid?: string };
        const isUserAdmin = !!decodedAccessToken.administratorUuid;

        setLoggedAsUserEmail(isUserAdmin ? user!.email : undefined);
    }, [user]);

    useCountdown(nextRevalidationTime, () => fetchNotificationBars(), COUNTDOWN_REVALIDATION_INTERVAL);

    return (
        <>
            {activeNotificationBars?.map((item, index) => (
                <section key={index} className="py-2" style={{ backgroundColor: item.rgbColor }}>
                    <Webline>
                        <div
                            className={twJoin(
                                'flex items-center justify-center text-center text-sm font-bold',
                                getYIQContrastTextColor(item.rgbColor),
                            )}
                        >
                            {!!item.mainImage && (
                                <div className="mr-3 flex h-11 w-11 items-center justify-center">
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
                </section>
            ))}
            {loggedAsUserEmail && (
                <section className="bg-background-error py-2">
                    <Webline>
                        <div className="text-text-default flex items-center justify-center text-center text-sm font-bold">
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
                                            variant="inverted"
                                            onClick={logout}
                                        />
                                    ),
                                }}
                            />
                        </div>
                    </Webline>
                </section>
            )}
        </>
    );
});
