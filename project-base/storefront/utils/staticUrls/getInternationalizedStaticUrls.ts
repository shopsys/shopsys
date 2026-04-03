import { SameLengthOutput } from 'types/SameLengthOutput';
import { getInternationalizedStaticUrl, Url } from './getInternationalizedStaticUrl';

export const getInternationalizedStaticUrls = <InputUrls extends Url[]>(urls: [...InputUrls], domainUrl: string) => {
    return urls.map((url) => getInternationalizedStaticUrl(url, domainUrl)) as SameLengthOutput<InputUrls>;
};
