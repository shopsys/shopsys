'use client';

import dynamic from 'next/dynamic';
import { ReactNode, memo } from 'react';
import { Slide } from 'react-toastify';

// Dynamically import ToastContainer to improve HMR performance
const ToastContainer = dynamic(() => import('react-toastify').then((mod) => ({ default: mod.ToastContainer })), {
    ssr: false,
    loading: () => null,
});

type ToastifyProviderProps = {
    children: ReactNode;
};

const ToastifyProviderComponent: FC<ToastifyProviderProps> = ({ children }) => {
    return (
        <>
            {children}
            <ToastContainer autoClose={6000} position="top-center" theme="colored" transition={Slide} />
        </>
    );
};

export const ToastifyProvider = memo(ToastifyProviderComponent);
export default ToastifyProvider;
