# Google Address Coordinates

Shopsys Platform can load latitude and longitude for store addresses via Google Maps Platform Geocoding API.
The integration is implemented by [`GoogleAddressCoordinatesFacade`]({{github.link}}/packages/framework/src/Component/AddressCoordinates/GoogleAddressCoordinatesFacade.php).

The facade calls the Google Geocoding API v4 endpoint and uses the `GOOGLE_MAP_API_KEY` environment variable as the API key.
When the key is empty, coordinate loading is disabled and the facade returns no coordinates.

## Configuration

Set the `GOOGLE_MAP_API_KEY` environment variable in your application environment:

```env
GOOGLE_MAP_API_KEY=your-google-maps-platform-api-key
```

The key is used on the backend only, so do not expose it in storefront public environment variables.
If you also use Google Maps JavaScript API in storefront, use a separate key for the browser-side integration.

## Getting a Google API key

Follow the official [Google Geocoding API setup documentation](https://developers.google.com/maps/documentation/geocoding/get-api-key):

- create or select a Google Cloud project
- make sure the project has an active billing account
- enable the Geocoding API for the project
- create an API key in Google Cloud Console credentials
- restrict the key before using it in production

For this backend integration, follow [Google Maps Platform API security best practices](https://developers.google.com/maps/api-security-best-practices) and restrict the key as follows:

- use an API restriction allowing only Geocoding API
- use an application restriction based on the public IP address or CIDR range of the server that sends the backend requests
- use a separate key per application or environment where possible
- delete keys that are no longer used

!!! warning

    Google states that unrestricted API keys can be abused and that you are financially responsible for charges caused by such abuse.
    Do not use an unrestricted API key in production.

## Cost and quota protection

Google Maps Platform Geocoding API is a paid service connected to a Google Cloud billing account.
The e-shop operator is responsible for monitoring API usage and costs charged to the configured payment method.

Before enabling this integration in production:

- review the current [Geocoding API usage and billing](https://developers.google.com/maps/documentation/geocoding/usage-and-billing) information
- estimate expected request volume using the [Google Maps Platform pricing information](https://mapsplatform.google.com/pricing/)
- configure [Google Cloud Billing budgets and budget alerts](https://docs.cloud.google.com/billing/docs/how-to/budgets) for the project or billing account
- configure Google Maps Platform quota alerts for the Geocoding API according to the [cost management documentation](https://developers.google.com/maps/billing-and-pricing/manage-costs)
- consider lowering the Geocoding API quota to a value that matches expected traffic and acceptable cost
- monitor the usage after deployment and adjust quotas carefully

!!! warning

    Quotas can prevent unexpectedly high usage, but setting them too low may cause coordinate loading to stop working for real users.
    Budget alerts help with monitoring, but alerts alone do not necessarily stop spending automatically.
    The production operator should choose limits that balance cost protection and service availability.

## Usage in Shopsys Platform

The API key is injected into `GoogleAddressCoordinatesFacade` from `GOOGLE_MAP_API_KEY`.
The facade sends address parts to Google and requests only the `results.location` field.

The integration is used for:

- loading coordinates for store addresses in administration
- resolving store search text to coordinates in Frontend API when the default store search coordinates provider is enabled

The store search text coordinates provider uses the `store_search_coordinates_cache` cache pool.
If you use the default provider, keep the corresponding cache and Redis configuration from `project-base`.

## Operational notes

- An empty `GOOGLE_MAP_API_KEY` disables calls to Google and coordinate loading silently returns no result.
- Use different API keys for development, staging, and production when possible.
- Rotate the key if it is leaked and update the application environment immediately.
- Check Google Cloud Monitoring and Google Maps Platform reporting regularly after enabling the integration.
