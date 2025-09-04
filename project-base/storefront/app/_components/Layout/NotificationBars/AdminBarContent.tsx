'use client';

import { useLogout } from 'app/_hooks/useLogout';
import { Button } from 'components/Forms/Button/Button';
import Trans from 'next-translate/Trans';

type LoggedAsUserBarProps = {
    userEmail: string | undefined;
};

export const AdminBarContent = ({ userEmail }: LoggedAsUserBarProps) => {
    const logout = useLogout();

    if (!userEmail) {
        return null;
    }

    return (
        <div className="bg-backgroundError py-2">
            <div className="text-text-default flex items-center justify-center text-center text-sm font-bold">
                <Trans
                    defaultTrans="Warning! You are logged in as a customer with the email {{ email }} <button>Log out</button>"
                    i18nKey="adminLoggedInAsCustomerWarning"
                    values={{ email: userEmail }}
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
        </div>
    );
};
