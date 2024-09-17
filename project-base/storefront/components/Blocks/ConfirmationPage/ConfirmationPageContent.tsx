import sentCartImage from '/public/images/sent-cart.svg';
import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import { ReactElement } from 'react';

type ConfirmationPageContentProps = {
    heading: string;
    content?: string;
    AdditionalContent?: ReactElement;
};

export const ConfirmationPageContent: FC<ConfirmationPageContentProps> = ({ heading, content, AdditionalContent }) => {
    return (
        <div className="mb-10 mt-16 flex flex-col items-center justify-center lg:mb-24 lg:mt-16 lg:flex-row lg:items-start">
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
