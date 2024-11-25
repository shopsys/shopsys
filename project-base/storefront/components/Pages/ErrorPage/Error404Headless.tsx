import { ErrorPage, ErrorPageButtonLink, ErrorPageTextHeading, ErrorPageTextMain } from './ErrorPageElements';
import image404 from '/public/images/404_m.png';
import { Image } from 'components/Basic/Image/Image';

type Error404HeadlessProps = {
    imageAlt: string;
    headingText: string;
    mainText: string;
    backButtonText?: string;
    backButtonHref?: string;
};
export const Error404Headless: FC<Error404HeadlessProps> = ({
    imageAlt,
    headingText,
    mainText,
    backButtonText,
    backButtonHref,
}) => {
    return (
        <ErrorPage>
            <div className="mb-8 max-w-sm">
                <Image priority alt={imageAlt} src={image404} />
            </div>
            <div>
                <ErrorPageTextHeading>{headingText}</ErrorPageTextHeading>
                <ErrorPageTextMain>{mainText}</ErrorPageTextMain>

                {backButtonHref && <ErrorPageButtonLink href={backButtonHref}>{backButtonText}</ErrorPageButtonLink>}
            </div>
        </ErrorPage>
    );
};
