import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const ToastContainerWrapper = dynamic(
    () => import('components/Pages/App/ToastContainerWrapper').then((component) => component.ToastContainerWrapper),
    { ssr: false },
);

export const DeferredToastContainer = () => {
    const shouldRender = useDeferredRender('tertiary_loaders');

    return shouldRender ? <ToastContainerWrapper /> : null;
};
