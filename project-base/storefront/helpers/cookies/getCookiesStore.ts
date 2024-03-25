import { getCookies } from 'cookies-next';
import { GetServerSidePropsContext } from 'next';

type CookiesType = {
    cookiesStore: string;
};

export const getCookiesStore = (context?: GetServerSidePropsContext) => {
    const { cookiesStore } = getCookies(context) as CookiesType;

    return cookiesStore ? decodeURIComponent(cookiesStore) : null;
};
