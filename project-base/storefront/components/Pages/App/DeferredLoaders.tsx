import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const Loaders = dynamic(() => import('components/Pages/App/Loaders').then((component) => ({
    default: component.Loaders
})));

export const DeferredLoaders = () => {
    const shouldRender = useDeferredRender('loaders');

    return shouldRender ? <Loaders /> : null;
};
