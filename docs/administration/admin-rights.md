# Admin rights

-   Administrator rights are implemented using Symfony roles in `config/packages/security.yaml` (for detailed information, see the [Symfony security documentation](https://symfony.com/doc/5.4/security.html)). The most important settings are:

    -   `role_hierarchy`
        -   defines the roles' inheritance
        -   e.g. entry `ROLE_ORDER_FULL: [ROLE_ORDER_VIEW]` means that by granting the `ROLE_ORDER_FULL` role, the `ROLE_ORDER_VIEW` is granted automatically as well.
    -   `access_control`

        -   defines which role has access to which path pattern
        -   the access is evaluated from the top to the bottom, so it is important to define the most nested paths the first, see the example below:

        ```yaml
        # CORRECT: full rights are required to access the page for creating a new article:
        - { path: ^/%admin_url%/article/new, roles: ROLE_ARTICLE_FULL }
        - { path: ^/%admin_url%/article/, roles: ROLE_ARTICLE_VIEW }

        # INCORRECT: the second line is not reachable, so the admin with "view" rights would be able to access the article creation page:
        - { path: ^/%admin_url%/article/, roles: ROLE_ARTICLE_VIEW }
        - { path: ^/%admin_url%/article/new, roles: ROLE_ARTICLE_FULL }
        ```

-   All the available roles are defined along with their human-readable labels in `src/Model/Security/Roles.php`
-   If a particular page or section is restricted for the given admin, it is removed from the menu
    -   see `src/Controller/Admin/SideMenuConfigurationSubscriber.php`
    -   see `src/Model/Security/MenuItemsGrantedRolesSetting.php`
    -   we use the default [access decision strategy (i.e. `affirmative`)](https://symfony.com/doc/5.4/security/voters.html#changing-the-access-decision-strategy), i.e., an admin will be granted access if he has at least one of the required roles, see the example below:
    ```php
    // returns true if the admin has at least one of the roles ROLE_FEED_VIEW, ROLE_HEUREKA_VIEW, or ROLE_SCRIPT_VIEW
    $this->security->isGranted([
        Roles::ROLE_FEED_VIEW,
        Roles::ROLE_HEUREKA_VIEW,
        Roles::ROLE_SCRIPT_VIEW,
    ]);
    ```
