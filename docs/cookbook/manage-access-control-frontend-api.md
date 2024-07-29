# Managing Access Control in Frontend API

This guide will walk you through the process of managing access control in the Frontend API. We will cover the default roles, how to grant permissions for queries/mutations, how to override these permissions, and how to use Symfony's voter system.

## 1. Default Roles

By default, we have three roles defined in `Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole`:

-   `ROLE_API_LOGGED_CUSTOMER`: This role is assigned to a logged customer.
-   `ROLE_API_ALL`: This role is assigned to a user with all privileges.
-   `ROLE_API_CUSTOMER_SELF_MANAGE`: This role allows a user to manage only their own data.

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
    access: "@=isGranted('ROLE_API_ALL')"
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

Symfony's voter system is a powerful tool for managing access control. There is already a defined voter `can_manage_customer_user_voter` that can be used to check if a user has the necessary permissions to manage a customer user. The voter checks the user's roles and returns either `ACCESS_GRANTED`, `ACCESS_DENIED`, or `ACCESS_ABSTAIN`.

### 4.1 Voter Implementation

The `can_manage_customer_user_voter` voter is implemented in `Shopsys\FrontendApiBundle\Voter\CustomerUserVoter`. This voter extends `Shopsys\FrontendApiBundle\Voter\AbstractB2bVoter`, which by default only allows access for B2B domains. For more information on setting up domain type, refer to the [Shopsys documentation](https://docs.shopsys.com/en/14.0/introduction/start-building-your-application/#set-up-domain-type).

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

You can also create your own voter if the existing voters do not meet your needs. To do this, you would extend the `Symfony\Component\Security\Core\Authorization\Voter\Voter` class and implement the `supports` and `voteOnAttribute` methods.
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

In this example, the `EditCustomerUserPersonalData` mutation uses the `can_manage_customer_user_voter` voter to check if the current user has the necessary permissions to edit a customer user's personal data. The `ROLE_API_ALL` role can manage all customer users, but the `ROLE_API_CUSTOMER_SELF_MANAGE` role can only manage its own data.
