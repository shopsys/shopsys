import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const TertiaryLoaders = dynamic(
    () => import('components/Pages/App/TertiaryLoaders').then((component) => component.TertiaryLoaders),
    { ssr: false },
);

export const DeferredTertiaryLoaders = () => {
    const shouldRender = useDeferredRender('tertiary_loaders');

    return shouldRender ? <TertiaryLoaders /> : null;
};
