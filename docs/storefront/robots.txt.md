# Dynamic Robots.txt

The robots.txt file is rendered dynamically on the server-side for each domain, so it's possible to serve sitemap links for the currently visited domain.

Any individual desired changes in the robots.txt file can be achieved in the `pages/robots.txt.tsx` file and function `getRobotsTxtContent` respectively.

Robots.txt file is available without any necessary additional configurations at the standard location (<https://domain/robots.txt>)
