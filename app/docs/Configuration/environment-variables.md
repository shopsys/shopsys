# Environment variables

Project is configured via [environment variables](https://symfony.com/doc/current/configuration.html#configuration-based-on-environment-variables).
You can see all available environment variables in the `.env` file in the project root.

During the deployment is necessary to pass some necessary environment variables from Gitlab to cluster (more specifically to a deployed pod).
You can find all mappings in the `deploy/deploy-project.sh` file.
