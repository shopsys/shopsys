import { FC } from 'react';
import { FrontendSwitcherWrapper } from './FrontendSwitcher.style';
import ShopsysIcon from 'components/basic/ShopsysIcon';
import { useRouter } from 'next/router';

const FrontendSwitcher: FC = () => {
    const router = useRouter();

    const openTwigFrontendEvent = () => {
        document.cookie = 'twigFrontend=true;';
        router.reload();
    };

    return (
        <FrontendSwitcherWrapper>
            <a onClick={openTwigFrontendEvent}>
                <ShopsysIcon iconTitle="Switch to Twig frontend" icon="replace" />
            </a>
        </FrontendSwitcherWrapper>
    );
};

export default FrontendSwitcher;
