### Authentication
First thing you should do for local development, is to generate private keys for Frontend. 
```plain
php phing frontend-generate-new-keys
```

For logging the user in/out we can use Auth hook. 
```plain
/hooks/auth/UseAuth.js
```

For authorization, we use Bearer tokens, which are sent with every URQL request to the API.

**AccessToken** 

JWT - JSON Web Token assigned to user with time validation.

**RefreshToken** 

When accessToken is not valid anymore urql client calls API refreshTokens mutation, with refreshToken as a parameter. 

In case the result is successful, we get a new accessToken and a new refreshToken, and the URQL client automatically re-executes all API requests with the new access token.
While the URQL client is verifying the tokens, all other calls are paused.

If error occurs auth.logout() function is called.

For using auth functions you have to use:

```plain
import { AuthContext } from 'hooks/auth/UseAuth';
const auth = useContext(AuthContext);
```

#### Prepared functions:
User login
```plain
auth.login(email: string, password: string);
```
This function calls the API with the provided email and password. If everything is OK, we get two tokens: an accessToken and a refreshToken. They are then stored in the localstorage and auth.isUserLoggedIn is set to true.

If user is logged then AccessToken is automatically added to header as Bearer token for every API Call and the results are filtered by the specified user.

User logout
```plain
auth.logout();
```
When you want to log the user out, call the auth.logout() function. Then auth.isUserLoggedIn is set to false and
tokens are erased from localStorage.

