import { TypeLoginTypeEnum } from 'graphql/types';
import type { LastLoginType } from 'store/slices/createUserSlice';
import { usePersistStore } from 'store/usePersistStore';
import { SocialNetworkLoginLink } from './SocialNetworkLoginLink';

type SocialNetworkLoginProps = {
    socialNetworks: TypeLoginTypeEnum[];
    shouldOverwriteCustomerUserCart: boolean | undefined;
    lastLoginType: LastLoginType | null;
};

export const SocialNetworkLogin: FC<SocialNetworkLoginProps> = ({
    socialNetworks,
    shouldOverwriteCustomerUserCart,
    lastLoginType,
}) => {
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const productListUuids: string[] = Object.values(usePersistStore((store) => store.productListUuids));

    return (
        <div className="flex w-full flex-col gap-3">
            {socialNetworks.map((socialNetwork) => {
                const url = {
                    pathname: `/social-network/login/${socialNetwork}`,
                    query: {
                        ...(cartUuid && { cartUuid }),
                        ...(shouldOverwriteCustomerUserCart !== undefined && { shouldOverwriteCustomerUserCart }),
                        ...(productListUuids.length > 0 && { productListUuids: productListUuids.join(',') }),
                    },
                };

                return (
                    <SocialNetworkLoginLink
                        key={socialNetwork}
                        href={url}
                        isLastUsed={lastLoginType === socialNetwork}
                        socialNetwork={socialNetwork}
                    />
                );
            })}
        </div>
    );
};
