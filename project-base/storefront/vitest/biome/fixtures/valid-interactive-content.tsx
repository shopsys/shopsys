import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Link } from 'components/Basic/Link/Link';

export const ValidInteractiveContentFixture = () => (
    <div>
        <button type="button">
            <span>Open popup</span>
        </button>
        <ExtendedNextLink href="/category">Go to category</ExtendedNextLink>
        <Link href="/contact">Contact</Link>
    </div>
);
