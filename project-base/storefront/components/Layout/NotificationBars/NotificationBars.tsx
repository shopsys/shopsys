import { Image } from 'components/Basic/Image/Image';
import { Button } from 'components/Forms/Button/Button';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { jwtDecode } from 'jwt-decode';
import Trans from 'next-translate/Trans';
import { useEffect, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { getAccessTokenFromCookies } from 'utils/auth/getTokensFromCookies';
import { useLogout } from 'utils/auth/useLogout';
import { getYIQContrastTextColor } from 'utils/colors/colors';
import { useNotificationBarsWithRevalidation } from 'utils/useNotificationBarRevalidation';

export const NotificationBars: FC = () => {
    const { activeNotificationBars } = useNotificationBarsWithRevalidation();
    const user = useCurrentCustomerData();
    const [loggedAsUserEmail, setLoggedAsUserEmail] = useState<string>();
    const logout = useLogout();
    const domainConfig = useDomainConfig();

    useEffect(() => {
        const encodedAccessToken = getAccessTokenFromCookies(domainConfig);
        if (!encodedAccessToken) {
            return;
        }

        const decodedAccessToken = jwtDecode(encodedAccessToken) as { administratorUuid?: string };
        const isUserAdmin = !!decodedAccessToken.administratorUuid;

        setLoggedAsUserEmail(isUserAdmin && user ? user.email : undefined);
    }, [user, domainConfig]);

    return (
        <>
            {activeNotificationBars?.map((item) => (
                <section key={item.uuid} className="py-2" style={{ backgroundColor: item.rgbColor }}>
                    <Webline>
                        <div
                            className={twJoin(
                                'flex items-center justify-center text-center font-semibold text-sm',
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
                        <div className="flex items-center justify-center text-center font-semibold text-sm text-text-default">
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
};
