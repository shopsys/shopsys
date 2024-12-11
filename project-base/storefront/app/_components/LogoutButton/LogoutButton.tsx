'use client';

import { useLogout } from 'app/_hooks/useLogout';
import { Button } from 'components/Forms/Button/Button';

export const LogoutButton = () => {
    const handleLogout = useLogout();

    return <Button onClick={handleLogout}>Logout</Button>;
};
