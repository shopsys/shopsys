import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const Loaders = dynamic(() => import('components/Pages/App/Loaders').then((component) => component.Loaders), {
    ssr: false,
});

export const DeferredLoaders = () => {
    const shouldRender = useDeferredRender('loaders');

    return shouldRender ? <Loaders /> : null;
};
