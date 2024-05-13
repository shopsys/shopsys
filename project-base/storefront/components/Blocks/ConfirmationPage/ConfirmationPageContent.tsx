import sentCartImage from '/public/images/sent-cart.svg';
import { Image } from 'components/Basic/Image/Image';
import { SkeletonPageConfirmation } from 'components/Blocks/Skeleton/SkeletonPageConfirmation';
import { TIDs } from 'cypress/tids';
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
        <div className="mt-16 mb-10 flex flex-col items-center justify-center lg:mt-16 lg:mb-24 lg:flex-row lg:items-start">
            <div className="mb-0 w-40 lg:mr-32">
                <Image alt={heading} src={sentCartImage} />
            </div>
            <div>
                <div className="h1 mb-3">{heading}</div>
                {!!content && (
                    <>
                        <div
                            className="text-center lg:text-left"
                            dangerouslySetInnerHTML={{ __html: content }}
                            tid={TIDs.order_confirmation_page_text_wrapper}
                        />
                        {AdditionalContent}
                    </>
                )}
            </div>
        </div>
    );
};
