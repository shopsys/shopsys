import { RequestsTable } from './RequestsTable';
import { useRequests } from './helpers/requests';
import symfonyImage from '/public/images/symfony.svg';
import { Image } from 'components/Basic/Image/Image';
import { useState } from 'react';

interface SymfonyDebugToolbarProps {
    tokenLinkHeader?: string;
    tokenHeader?: string;
}

export const SymfonyDebugToolbar: FC<SymfonyDebugToolbarProps> = ({
    tokenHeader = 'x-debug-token',
    tokenLinkHeader = 'x-debug-token-link',
}) => {
    const [isHovered, setIsHovered] = useState(false);
    const { responses, hasResponses, reset } = useRequests(tokenHeader, tokenLinkHeader);

    return (
        <div
            className="z-50 fixed right-0 bottom-0 flex items-center justify-center gap-2 bg-primaryDarker px-3 text-creamWhite"
            onMouseEnter={() => setIsHovered(true)}
            onMouseLeave={() => setIsHovered(false)}
        >
            <div className="flex h-9 items-center justify-center">
                <div>{responses.length > 0 && <span>{responses.length}</span>}</div>
                <RequestsTable hasResponses={hasResponses} isVisible={isHovered} reset={reset} responses={responses} />
            </div>
            <Image alt="Symfony Logo" height={24} src={symfonyImage} width={24} />
        </div>
    );
};
