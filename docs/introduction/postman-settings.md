# Postman Settings

Postman is a strong tool for working with different APIs.
This article describes how to easily import `schema.graphql` to your Postman to help you with queries and mutations.

## How to retrieve schema.graphql

There are two ways of retrieving `schema.graphql`.

### Generate schema.graphql in your locally running project

Run this command inside `php-fpm` container:

```
php phing frontend-api-generate-graphql-schema
```

`graphql.schema` file will be generated in root folder of the project.

### Download schema.graphql from CI server on Gitlab

Open this project in `Gitlab` and go to `CI / CD` -> `Pipelines` section.
Find the last build of the desired branch and open it.
Then click on `Review` stage.
In right menu in `Job artifacts` select `Browse` and then find and download `schema.graphql` file.

## Importing schema.graphql into Postman

Open `Postman` application.
Select your desired workspace (default is `My Workspace`).
In left menu click on `APIs` and then click on `+` button.
Enter desired API name like `Shopsys Platform Frontend API`.
The version number is up to you as we are not versioning the Frontend API.
Select `GraphQL` option from `Schema type` and `GraphQL SDL` option from `Schema format`.
Your API will be created.

On API page click on `Define` tab and copy and paste `schema.graphql` content here.
Click on `Save` button and then on `Generate collection` button.
Enter desired collection name like `Shopsys Platform Frontend API`.
Select `Test the API` from `What do you want to do with this collection?` selection and click on `Generate collection` button.
In left menu click on `Collections` and you should see your new collection there.
Click on the new collection and under `Variables` tab enter new variable `url` and as current value set URL address of GraphQL endpoint like `http://127.0.0.1:8000/graphql/`.

You are now able to easily run `queries` and `mutations` from this collection.

For more info about how to create requests in Postman see [Postman docs](https://learning.postman.com/docs/sending-requests/requests/).
