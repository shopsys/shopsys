import { Url, getInternationalizedStaticUrl } from './getInternationalizedStaticUrl';
import { SameLengthOutput } from 'types/SameLengthOutput';

export const getInternationalizedStaticUrls = <InputUrls extends Url[]>(urls: [...InputUrls], domainUrl: string) => {
    return urls.map((url) => getInternationalizedStaticUrl(url, domainUrl)) as SameLengthOutput<InputUrls>;
};
