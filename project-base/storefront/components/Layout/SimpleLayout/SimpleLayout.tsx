import { Webline } from 'components/Layout/Webline/Webline';

type SimpleLayoutProps = {
    heading: string;
    standardWidth?: true;
};

export const SimpleLayout: FC<SimpleLayoutProps> = ({ heading, children, standardWidth }) => (
    <Webline>
        <h1 className="mb-3 text-center">{heading}</h1>

        {standardWidth ? (
            children
        ) : (
            <div className="flex w-full justify-center">
                <div className="my-7 w-full rounded border-2 border-greyLighter px-2 pt-5 pb-4 lg:w-[690px] lg:px-14 lg:pt-10 lg:pb-8">
                    {children}
                </div>
            </div>
        )}
    </Webline>
);
