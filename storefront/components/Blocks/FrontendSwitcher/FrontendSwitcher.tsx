import { FrontendSwitcherIconStyled, FrontendSwitcherWrapperStyled } from './FrontendSwitcher.style';
import { FC } from 'react';
import { useRouter } from 'next/router';

const FrontendSwitcher: FC = () => {
    const router = useRouter();

    const openTwigStorefrontEvent = () => {
        document.cookie = 'twigStorefront=true;';
        router.reload();
    };

    return (
        <FrontendSwitcherWrapperStyled>
            <a onClick={openTwigStorefrontEvent}>
                <FrontendSwitcherIconStyled title="Switch to Twig frontend" icon="Replace" />
            </a>
        </FrontendSwitcherWrapperStyled>
    );
};

export default FrontendSwitcher;
