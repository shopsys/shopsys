import { Heading } from 'components/Basic/Heading/Heading';
import { Webline } from 'components/Layout/Webline/Webline';

type SimpleLayoutProps = {
    heading: string;
    standardWidth?: true;
};

export const SimpleLayout: FC<SimpleLayoutProps> = ({ heading, children, standardWidth }) => (
    <Webline>
        <div className="text-center">
            <Heading type="h1">{heading}</Heading>
        </div>

        {standardWidth ? (
            children
        ) : (
            <div className="mr-24 flex w-full justify-center">
                <div className="my-7 w-full rounded border-2 border-greyLighter px-2 pt-5 pb-4 lg:w-[690px] lg:px-14 lg:pt-10 lg:pb-8">
                    {children}
                </div>
            </div>
        )}
    </Webline>
);
