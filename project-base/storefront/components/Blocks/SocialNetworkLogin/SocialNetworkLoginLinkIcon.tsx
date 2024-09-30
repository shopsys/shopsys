import { FacebookIcon } from 'components/Basic/Icon/FacebookIcon';
import { GoogleIcon } from 'components/Basic/Icon/GoogleIcon';
import { SeznamIcon } from 'components/Basic/Icon/SeznamIcon';
import { TypeLoginTypeEnum } from 'graphql/types';

export const SocialNetworkIcon: FC<{ socialNetwork: TypeLoginTypeEnum }> = ({ socialNetwork }) => {
    switch (socialNetwork) {
        case TypeLoginTypeEnum.Facebook:
            return <FacebookIcon className="size-6 text-textInverted" />;
        case TypeLoginTypeEnum.Seznam:
            return <SeznamIcon className="size-6 text-textInverted" />;
        case TypeLoginTypeEnum.Google:
            return <GoogleIcon className="size-6" />;
        default:
            return null;
    }
};
