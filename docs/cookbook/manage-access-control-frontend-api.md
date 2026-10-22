# Managing Access Control in Frontend API

This guide covers Frontend API-specific access control implementation using GraphQL directives and Symfony voters. The Frontend API integrates with the platform's unified role-based access control system.

!!! info "Global RBAC System"

    This page focuses on Frontend API-specific implementation details. For a comprehensive overview of the role-based access control system used across the platform, see [Role-Based Access Control](../introduction/role-based-access-control.md).

## 1. Default Roles

By default, we have the following roles defined in `Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole`:

- `ROLE_API_ALL`: This role is assigned to a user with all privileges.
- `ROLE_API_MANAGE_CUSTOMERS`: This role allows a user to manage other users within the company.
- `ROLE_API_MANAGE_COMPANY_DATA`: This role allows a user to manage company data (e.g., billing address).
- `ROLE_API_CUSTOMER_SELF_MANAGE`: This role allows a user to manage only their own data.
- `ROLE_API_CUSTOMER_SEES_PRICES`: This role allows a user to see prices.
- `ROLE_API_CART_AND_ORDER_CREATION`: This role allows a user to manipulate with a cart and create an order.
- `ROLE_API_COMPANY_ORDERS_VIEW`: This role allows a user to see information about all the company's orders (i.e., access the order list and detail pages of all orders created under the user's company).
- `ROLE_API_COMPLAINT_CREATION`: This role allows a user to create a complaint.
- `ROLE_API_COMPANY_COMPLAINTS_VIEW`: This role allows a user to see information about all the company's complaints (i.e., access the complaint list and detail pages of all complaints created under the user's company).

## 2. Granting Permissions for Query/Mutation

Permissions for a query or mutation can be granted by defining access and using the `@=isGranted` directive in the query/mutation types files. For example:

```yaml
AddNewCustomerUser:
    type: 'CustomerUser!'
    description: 'Add new customer user to customer'
    args:
        input:
            type: AddNewCustomerUserDataInput!
            validation: cascade
    access: "@=isGranted('ROLE_API_MANAGE_CUSTOMERS')"
    resolve: "@=mutation('addNewCustomerUserMutation', args, validator)"
```

## 3. Overriding Permissions

Permissions can be overridden in the `project-base` to disable access for everyone or allow access for different roles. For example:

```yaml
AddNewCustomerUser:
    type: 'CustomerUser!'
    description: 'Add new customer user to customer'
    args:
        input:
            type: AddNewCustomerUserDataInput!
            validation: cascade
    access: false
    resolve: "@=mutation('addNewCustomerUserMutation', args, validator)"
```

## 4. Symfony Voter

Symfony's voter system is a powerful tool for managing access control.
There is already a defined voter `can_manage_customer_user_voter` that can be used to check if a user has the necessary permissions to manage a customer user.
The voter checks the user's roles and returns either `ACCESS_GRANTED`, `ACCESS_DENIED`, or `ACCESS_ABSTAIN`.

### 4.1 Voter Implementation

The `can_manage_customer_user_voter` voter is implemented in `Shopsys\FrontendApiBundle\Voter\CustomerUserVoter`.
This voter extends `Shopsys\FrontendApiBundle\Voter\AbstractB2bVoter`, which by default only allows access for B2B domains.
For more information on setting up domain type, refer to the [Start Building Your Application](../introduction/start-building-your-application.md#set-up-domain-type) article.

### 4.2 Overriding the Voter

If you want to modify voter logic you can simply override `Shopsys\FrontendApiBundle\Voter\CustomerUserVoter` to modify the access control logic or disable it entirely.

```yaml
services:
    App\FrontendApi\AbstractB2bVoter:
        tags: ['security.voter']

    Shopsys\FrontendApiBundle\Voter\CustomerUserVoter:
        class: App\App\FrontendApi\AbstractB2bVoter
```

### 4.3 Creating a New Voter

You can also create your own voter if the existing voters do not meet your needs.
To do this, you would extend the `Symfony\Component\Security\Core\Authorization\Voter\Voter` class and implement the `supports` and `voteOnAttribute` methods.
If you want to check for B2B domain access, you can extend the `Shopsys\FrontendApiBundle\Voter\AbstractB2bVoter` class.

### 4.4 Example Usage

Here is an example of how to use the `can_manage_customer_user_voter` voter in a GraphQL mutation:

```yaml
EditCustomerUserPersonalData:
    type: 'CustomerUser!'
    description: 'edit customer user to customer'
    args:
        input:
            type: EditCustomerUserPersonalDataInput!
            validation: cascade
    resolve: "@=mutation('editCustomerUserPersonalDataMutation', args, validator)"
    access: "@=isGranted('can_manage_customer_user_voter', args)"
```

In this example, the `EditCustomerUserPersonalData` mutation uses the `can_manage_customer_user_voter` voter to check if the current user has the necessary permissions to edit a customer user's personal data.
The `ROLE_API_MANAGE_CUSTOMERS` role can edit all customer users of the same company (changing their role group and removing them is limited, see [Assigning Role Groups](#5-assigning-role-groups)), but the `ROLE_API_CUSTOMER_SELF_MANAGE` role can only manage its own data.

## 5. Assigning Role Groups

A customer user can assign only a role group whose roles are a subset of their own roles.
Roles on both sides are expanded through the role hierarchy, and the current customer user's roles are always read from their role group in the database, never from the request or from the access token.
`ROLE_API_ALL` is compared as a role of its own, so the role group with `ROLE_API_ALL` can be assigned only by a customer user who has `ROLE_API_ALL` as well.
`ROLE_API_MANAGE_CUSTOMERS` includes `ROLE_API_CUSTOMER_SELF_MANAGE` in the role hierarchy, so a customer user who manages the users of the company can always manage their own data too.

A customer user with `ROLE_API_MANAGE_CUSTOMERS` can edit personal data of every customer user of the company.
The role group of another customer user can be changed, and the customer user can be removed, only when the current customer user could assign the role group the customer user currently has.
A role group is not "weaker" or "stronger" as a whole, so two role groups can be incomparable and then neither of their users can change the role group of the other.

The rules are implemented in `Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver::canCustomerUserAssignRoleGroup()` and `canCustomerUserChangeRoleGroupOfCustomerUser()` and they are applied in these places:

- the `Shopsys\FrontendApiBundle\Component\Constraints\CustomerUserRoleGroupAssignable` constraint validates `roleGroupUuid` of the `AddNewCustomerUser` mutation
- the `Shopsys\FrontendApiBundle\Component\Constraints\CustomerUserRoleGroupChangeAllowed` constraint validates the `EditCustomerUserPersonalData` mutation when its `roleGroupUuid` differs from the current role group of the edited customer user
- `Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\CustomerUserMutation::checkCustomerUserCanBeDeleted()` denies removing a customer user whose role group the current customer user could not change
- the `customerUserRoleGroups` query returns only the role groups the current customer user can assign
- the `canRoleGroupBeChangedByCurrentCustomerUser` field of `BaseCustomerUser` GraphQL interface tells the storefront whether the current customer user can change the role group of the customer user and remove them
