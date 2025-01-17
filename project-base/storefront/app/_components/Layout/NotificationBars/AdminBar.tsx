import { AdminBarContent } from './AdminBarContent';
import { getCurrentCustomerData } from 'app/_queries/getCurrentCustomerData';
import { jwtDecode } from 'jwt-decode';
import { getTokensRSC } from 'utils/auth/getTokensFromRSC';

export const AdminBar = async () => {
    const [{ accessToken }, user] = await Promise.all([getTokensRSC(), getCurrentCustomerData()]);

    if (!accessToken) {
        return null;
    }

    const decodedAccessToken = jwtDecode(accessToken) as { administratorUuid?: string };
    const userEmail = decodedAccessToken.administratorUuid ? user!.email : undefined;

    return <AdminBarContent userEmail={userEmail} />;
};
