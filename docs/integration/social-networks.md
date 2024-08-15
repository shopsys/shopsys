# Social networks

Social network use PHP backend library [Hybridauth](https://hybridauth.github.io/)

## How to set it up?

You can set up which social network logins are enabled on which domain in `social_network_config.yaml` file.

For every social network, you need `app_id` and `app_secret`. Below you have information on where and how to get them. The credentials then need to be set to the corresponding environment variables.

### Facebook

-   go to site https://developers.facebook.com/ and log into Facebook account
-   create [application](https://developers.facebook.com/apps)
-   in that application, in `App settings` → `Basic` you will find `app_id` and `app_secret`

### Google

-   log into Google account and create [OAuth 2.0 Client IDs](https://console.cloud.google.com/apis/credentials)
-   in detail, you will find `Client ID` and `Client secret`
-   in Client ID settings, you need to add an authorized redirect URI, which is `{eshop_domain}/social-network/login/google`

### Seznam

-   log to your Seznam account and go to page https://vyvojari.seznam.cz/oauth/admin
-   create service and first data are `app_id` and `app_secret`

Every social network require URL for backward redirect to you app.
In Shopsys platform, you have to add url `{eshop_domain}/social-network/login/{type}` where `{eshop_domain}` is your domain and `{type}` is social network name, e.g.: `google`, `facebook`, `seznam`, etc.
For local development, social networks usually require https.

P.S.: If you are owner of the Facebook application, and you log in with this owner account, email will not come after login, more info: https://github.com/hybridauth/hybridauth/issues/1372#issuecomment-1542447803

## How to add next social network

Only what you have to do is add configuration to file `social_network_config.yaml` and add a new value to `LoginTypeEnumDecorator.types.yaml`.
The provider key (i.e. the name of the social network) in the `social_network_config.yaml` file must be the same as the key in `LoginTypeEnum`.
It should be enough to get inspired by Facebook or Google configs.
