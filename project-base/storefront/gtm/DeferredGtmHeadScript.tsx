import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const GtmHeadScript = dynamic(() => import('gtm/GtmHeadScript').then((component) => ({
    default: component.GtmHeadScript
})), {
    ssr: false,
});

export const DeferredGtmHeadScript: FC = () => {
    const shouldRender = useDeferredRender('gtm_head_script');

    return shouldRender ? <GtmHeadScript /> : null;
};
