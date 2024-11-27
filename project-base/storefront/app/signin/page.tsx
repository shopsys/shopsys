import { LoginForm } from 'app/_components/LoginForm/LoginForm';
import { getIsUserLoggedInQuery } from 'app/_queries/getIsUserLoggedInQuery';
import { getSettingsQuery } from 'app/_queries/getSettingsQuery';
import { redirect } from 'next/navigation';

export default async function SignInPage() {
    const isUserLoggedIn = await getIsUserLoggedInQuery();

    if (isUserLoggedIn) {
        return redirect('/app');
    }
    const { data: settingsData } = await getSettingsQuery();

    return <LoginForm formHeading="Sign in" socialNetworks={settingsData?.settings?.socialNetworkLoginConfig} />;
}
