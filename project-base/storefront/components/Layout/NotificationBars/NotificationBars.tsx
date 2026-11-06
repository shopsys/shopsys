import { Image } from 'components/Basic/Image/Image';
import { Button } from 'components/Forms/Button/Button';
import { Webline } from 'components/Layout/Webline/Webline';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import Trans from 'next-translate/Trans';
import { twJoin } from 'tailwind-merge';
import { useLogout } from 'utils/auth/useLogout';
import { getYIQContrastTextColor } from 'utils/colors/colors';
import { getImageAlt } from 'utils/imageAltText';
import { useNotificationBarsWithRevalidation } from 'utils/useNotificationBarRevalidation';

export const NotificationBars: FC = () => {
    const { activeNotificationBars } = useNotificationBarsWithRevalidation();
    const user = useCurrentCustomerData();
    const logout = useLogout();
    const loggedAsUserEmail = user?.isAdministratorLoggedAsCustomer ? user.email : undefined;

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
                                <div className="relative mr-3 size-11 shrink-0">
                                    <Image
                                        fill
                                        alt={getImageAlt(item.mainImage.name, item.plainText)}
                                        className="object-contain"
                                        sizes="44px"
                                        src={item.mainImage.url}
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
