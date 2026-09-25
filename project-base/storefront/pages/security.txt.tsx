import { GetServerSideProps } from 'next';
import { DEFAULT_LOCALE } from 'utils/domain/domainUtils';

// mandatory for Next although it's not used
const SecurityTxt: FC = (): null => {
    return null;
};

const EXPIRES_IN_DAYS = 180;

export const getServerSideProps: GetServerSideProps = async (context) => {
    // Allow only root /security.txt, return 404 for locale-prefixed URLs
    if (context.locale !== DEFAULT_LOCALE) {
        return {
            notFound: true,
        };
    }

    const contact = process.env.SECURITY_TXT_CONTACT || 'security@shopsys.com';
    const contactUri = contact.includes(':') ? contact : `mailto:${contact}`;

    // RFC 9116 requires the Expires field and recommends a value less than a year in the future,
    // generating it dynamically keeps the file always valid
    const expires = new Date(Date.now() + EXPIRES_IN_DAYS * 24 * 60 * 60 * 1000);

    const res = context.res;

    res.setHeader('Content-Type', 'text/plain');
    res.write(`Contact: ${contactUri}\nExpires: ${expires.toISOString()}\n`);
    res.end();

    return { props: {} };
};

export default SecurityTxt;
