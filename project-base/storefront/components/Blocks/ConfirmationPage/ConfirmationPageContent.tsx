import { SkeletonPageConfirmation } from 'components/Blocks/Skeleton/SkeletonPageConfirmation';
import { ReactElement } from 'react';

type ConfirmationPageContentProps = {
    heading: string;
    content?: string;
    AdditionalContent?: ReactElement;
    isFetching: boolean;
};

export const ConfirmationPageContent: FC<ConfirmationPageContentProps> = ({
    heading,
    content,
    AdditionalContent,
    isFetching,
}) => {
    if (isFetching) {
        return <SkeletonPageConfirmation />;
    }

    return (
        <div className="mt-16 mb-10 flex flex-col items-center justify-center lg:mt-16 lg:mb-24 lg:flex-row">
            <div className="mb-0 w-40 lg:mr-32">
                <img alt={heading} src="/public/frontend/images/sent-cart.svg" />
            </div>
            <div>
                <div className="h1 mb-3">{heading}</div>
                {!!content && (
                    <>
                        <div className="text-center lg:text-left" dangerouslySetInnerHTML={{ __html: content }} />
                        {AdditionalContent}
                    </>
                )}
            </div>
        </div>
    );
};
