'use client';

import { ReactNode } from 'react';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

type ToastifyProviderProps = {
    children: ReactNode;
};
export const ToastifyProvider: FC<ToastifyProviderProps> = ({ children }) => {
    return (
        <>
            {children}
            <ToastContainer autoClose={6000} position="top-center" theme="colored" />
        </>
    );
};

export default ToastifyProvider;
