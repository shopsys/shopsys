export const MessageWrapper: FC = ({ children }) => (
    <div className="mt-16 mb-10 flex flex-col items-center justify-center lg:mt-16 lg:mb-24 lg:flex-row">
        {children}
    </div>
);

export const ImageWrapper: FC = ({ children }) => <div className="mb-0 w-40 lg:mr-32">{children}</div>;

export const PaymentWrapper: FC = ({ children }) => (
    <div className="mt-16 mb-10 flex flex-col items-center justify-center lg:mt-16 lg:mb-24 lg:flex-row">
        {children}
    </div>
);

type MessageProps = { message?: string };

export const Message: FC<MessageProps> = ({ children, message }) => (
    <div className="text-center lg:text-left" dangerouslySetInnerHTML={{ __html: message ?? '' }}>
        {children}
    </div>
);
