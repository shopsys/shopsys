import { Webline } from 'components/Layout/Webline/Webline';
import { ErrorPageBody, ErrorPageBodyProps } from 'components/Pages/ErrorPage/ErrorPageBody';

export const ErrorPage: FC<ErrorPageBodyProps> = ({ children, heading, statusCode, text }) => (
    <Webline className="py-10 lg:py-20">
        <ErrorPageBody heading={heading} statusCode={statusCode} text={text}>
            {children}
        </ErrorPageBody>
    </Webline>
);
