import { TIDs } from 'cypress/tids';

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
        <div className="mt-1 lg:mt-6">
            <h1 className={headingClassName}>{heading}</h1>

            {!!content && (
                <>
                    <div
                        dangerouslySetInnerHTML={{ __html: content }}
                        tid={TIDs.order_confirmation_page_text_wrapper}
                    />
                    {children}
                </>
            )}
        </div>
    );
};
