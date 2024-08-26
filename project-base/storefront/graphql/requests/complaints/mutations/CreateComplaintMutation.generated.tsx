import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CreateComplaintFragment } from '../fragments/CreateComplaintFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeCreateComplaintVariables = Types.Exact<{
  input: Types.TypeComplaintInput;
}>;


export type TypeCreateComplaint = { __typename?: 'Mutation', CreateComplaint: { __typename?: 'Complaint', uuid: string, number: string, deliveryFirstName: string, deliveryLastName: string, deliveryCompanyName: string | null, deliveryTelephone: string, deliveryStreet: string, deliveryCity: string, deliveryPostcode: string, createdAt: any, deliveryCountry: { __typename: 'Country', name: string, code: string }, items: Array<{ __typename?: 'ComplaintItem', quantity: number, description: string, orderItem: { __typename: 'OrderItem', uuid: string, name: string, vatRate: string, quantity: number, unit: string | null, type: Types.TypeOrderItemTypeEnum, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, order: { __typename?: 'Order', number: string, creationDate: any } } }> } };


      export interface PossibleTypesResultData {
        possibleTypes: {
          [key: string]: string[]
        }
      }
      const result: PossibleTypesResultData = {
  "possibleTypes": {
    "Advert": [
      "AdvertCode",
      "AdvertImage"
    ],
    "ArticleInterface": [
      "ArticleSite",
      "BlogArticle"
    ],
    "Breadcrumb": [
      "ArticleSite",
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Category",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "Store",
      "Variant"
    ],
    "CustomerUser": [
      "CompanyCustomerUser",
      "RegularCustomerUser"
    ],
    "Hreflang": [
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "SeoPage",
      "Variant"
    ],
    "NotBlogArticleInterface": [
      "ArticleLink",
      "ArticleSite"
    ],
    "ParameterFilterOptionInterface": [
      "ParameterCheckboxFilterOption",
      "ParameterColorFilterOption",
      "ParameterSliderFilterOption"
    ],
    "Product": [
      "MainVariant",
      "RegularProduct",
      "Variant"
    ],
    "ProductListable": [
      "Brand",
      "Category",
      "Flag"
    ],
    "Slug": [
      "ArticleSite",
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Category",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "Store",
      "Variant"
    ]
  }
};
      export default result;
    

export const CreateComplaintDocument = gql`
    mutation CreateComplaint($input: ComplaintInput!) {
  CreateComplaint(input: $input) {
    ...CreateComplaintFragment
  }
}
    ${CreateComplaintFragment}`;

export function useCreateComplaint() {
  return Urql.useMutation<TypeCreateComplaint, TypeCreateComplaintVariables>(CreateComplaintDocument);
};