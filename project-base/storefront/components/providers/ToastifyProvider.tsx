'use client';

import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

export const ToastifyProvider: FC = () => {
    return <ToastContainer autoClose={6000} position="top-center" theme="colored" />;
};

export default ToastifyProvider;
