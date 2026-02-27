import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const SecondaryLoaders = dynamic(
    () => import('components/Pages/App/SecondaryLoaders').then((component) => component.SecondaryLoaders),
    { ssr: false },
);

export const DeferredSecondaryLoaders = () => {
    const shouldRender = useDeferredRender('secondary_loaders');

    return shouldRender ? <SecondaryLoaders /> : null;
};
