import { TIDs } from 'cypress/tids';
import { twMergeCustom } from 'utils/twMerge';

type ConfirmationPageContentProps = {
    heading: string;
    headingClassName?: string;
    content?: string;
};

export const ConfirmationPageContent: FC<ConfirmationPageContentProps> = ({
    heading,
    headingClassName,
    content,
    children,
}) => {
    return (
        <>
            <h1 className={twMergeCustom('mt-1 mb-4 lg:mt-6', headingClassName)}>{heading}</h1>

            {!!content && (
                <>
                    <div
                        dangerouslySetInnerHTML={{ __html: content }}
                        data-tid={TIDs.order_confirmation_page_text_wrapper}
                    />
                    {children}
                </>
            )}
        </>
    );
};
