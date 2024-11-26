# Social networks

Social network use PHP backend library [Hybridauth](https://hybridauth.github.io/)

## How to set it up?

You can set up which social network logins are enabled on which domain in `social_network_config.yaml` file.

For every social network, you need `app_id` and `app_secret`. Below you have information on where and how to get them. The credentials then need to be set to the corresponding environment variables.

Every social network require URL for backward redirect to the eshop so you have to add URL `{eshop_domain}/social-network/login/{type}` where `{eshop_domain}` is your domain and `{type}` is the social network name, e.g.: `google`, `facebook`, `seznam`, etc.
If you want to use social network login on multiple domains, you have to allow all of them in the corresponding social network settings.
For local development, login via social networks usually requires https.

### Facebook

- go to [Facebook developers site](https://developers.facebook.com/) and log into Facebook account
- create [application](https://developers.facebook.com/apps)
    - in "Use Cases", choose "Authenticate and request data from users with Facebook Login"
    - after creating the application, click on "Customize adding a Facebook Login button" on the Dashboard
    - in the "Permissions" tab, add an "email" permission
    - in the "Settings" tab, set the proper redirect URI (`{eshop_domain}/social-network/login/facebook`) in "Valid OAuth Redirect URIs" input
- go to `App settings` → `Basic`, and you will find `App ID` and `App secret` here

!!! warning

    Only the owner of the Facebook application can use the social login that is configured this way (the application is not published at this point). If you want to test the login with the other accounts, you have to add them explicitly in the "App Roles" setting.
    These users then need to confirm their role in their developers account.

### Google

- log into Google account and create [OAuth 2.0 Client IDs](https://console.cloud.google.com/apis/credentials)
    - Create a project.
    - Click on "+ create credentials" and select "OAuth client ID".
    - Click on "configure consent screen" and fill in the required fields (choose an "internal" user type).
    - After you configure the consent screen, once again click on "+ create credentials" and select "OAuth client ID".
    - As an application type, choose "web application" and fill in the required fields.
    - You need to add an authorized redirect URI here, which is `{eshop_domain}/social-network/login/google`.
- in detail, you will find `Client ID` and `Client secret`

### Seznam

- log to your Seznam account and go to page https://vyvojari.seznam.cz/oauth/admin
- create service and first data are `app_id` and `app_secret`
- you need to add redirect URI, which is `{eshop_domain}/social-network/login/seznam`

## How to add next social network

Only what you have to do is add configuration to file `social_network_config.yaml` and add a new value to `LoginTypeEnumDecorator.types.yaml`.
The provider key (i.e. the name of the social network) in the `social_network_config.yaml` file must be the same as the key in `LoginTypeEnum`.
It should be enough to get inspired by Facebook or Google configs.
