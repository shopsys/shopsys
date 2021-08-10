import { SlugType } from '../slug/Slug';
import { v4 as uuid } from 'uuid';

export const productDetailBody = `
    uuid
    name
    namePrefix
    nameSuffix
    description
    catalogNumber
`;

export interface ProductDetailType extends SlugType {
    uuid: typeof uuid;
    name: string;
    namePrefix: string;
    nameSuffix: string;
    description: string;
    catalogNumber: string;
}
