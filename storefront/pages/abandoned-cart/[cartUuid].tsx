import { useRouter } from 'next/router';
import { FC, useEffect } from 'react';
import { nextReduxWrapper } from 'redux/main';
import { userActions } from 'redux/slices/user';

const OrderDetailByHash: FC = () => {
    const router = useRouter();
    useEffect(() => {
        router.replace('/');
    }, [router]);

    return null;
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    if (typeof context.params?.cartUuid === 'string') {
        store.dispatch(userActions.setCartUuid(context.params.cartUuid));
    }

    return {
        redirect: {
            destination: '/',
            statusCode: 302,
        },
    };
});

export default OrderDetailByHash;
