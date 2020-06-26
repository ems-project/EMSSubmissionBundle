# Http handler

Send a http request from submission data.
The **message** will contain the request body.

## Endpoint

The endpoint needs to be a valid JSON and the only required property is URL.

The following example will do a POST request to http://example.test/api/form?q=test
```json 
{
  "method": "POST",
  "url": "http://example.test/api/form",
  "query": {
    "q": "test"
  },
  "headers": {
    "Content-Type": "application/json"
  },
  "timeout": 30,
}
```

Authentication

The endpoint JSON allow two authentication properties **auth_basic** and **auth_bearer**

```json 
{
  "auth_basic": "username:password",
  "auth_bearer": "a token enabling HTTP Bearer authorization"
}
```

The authentication parameters can also be fetched using the [connection configuration](/src/Resources/doc/index.md#connection)

```json 
{
  "auth_basic": "{{'apiTest%.%user'|emss_connection}}:{{'apiTest%.%password'|emss_connection}}",
  "auth_bearer": "{{'apiTest%.%token'|emss_connection}}"
}
```