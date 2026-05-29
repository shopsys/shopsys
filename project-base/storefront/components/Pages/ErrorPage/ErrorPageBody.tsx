export type ErrorPageBodyProps = {
    heading: string;
    statusCode: string | number;
    text: string;
};

export const ErrorPageBody: FC<ErrorPageBodyProps> = ({ children, heading, statusCode, text }) => (
    <section className="flex flex-col gap-4">
        <span className="mx-auto select-none font-bold font-secondary text-7xl text-text-primary tracking-normal lg:text-9xl">
            {statusCode}
        </span>

        <h1 className="text-center">{heading}</h1>

        <p className="mx-auto max-w-[520px] text-balance text-center">{text}</p>

        {children}
    </section>
);
