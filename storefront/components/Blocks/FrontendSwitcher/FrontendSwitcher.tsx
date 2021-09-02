import { FC } from 'react';
import { FrontendSwitcherWrapper } from './FrontendSwitcher.style';
import Icon from 'components/Basic/Icon';
import { useRouter } from 'next/router';

const FrontendSwitcher: FC = () => {
    const router = useRouter();

    const openTwigStorefrontEvent = () => {
        document.cookie = 'twigStorefront=true;';
        router.reload();
    };

    return (
        <FrontendSwitcherWrapper>
            <a onClick={openTwigStorefrontEvent}>
                <Icon iconTitle="Switch to Twig frontend" icon="replace" />
            </a>
        </FrontendSwitcherWrapper>
    );
};

export default FrontendSwitcher;
