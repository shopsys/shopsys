import { Slide, ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

export const ToastContainerWrapper = () => {
    return <ToastContainer autoClose={6000} position="top-center" theme="colored" transition={Slide} />;
};
