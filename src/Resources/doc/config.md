# Configuration
## Dependencies
The system is using the client request from the EMS\ClientHelper namespace. Make sure to define it in your `services.yaml` configuration:
```yaml
EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequest: '@emsch.client_request.website'
```

## Config file (config/packages/ems_submission.yaml)
```yaml
ems_submission:
  default_timeout: '%env(int:EMSS_DEFAULT_TIMEOUT)%'
  connections: '%env(json:EMSS_CONNECTIONS)%'
```

## Default Timeout
Whenever a form is submitted using our handlers, we should limit the amount of time that is allowed for the request to succeed. The `default_timeout` requires a number that represents the allowed number of seconds before we timeout waiting for external feedback.

## Connections <a name="connection" />
To integrate with external services like ServiceNow we need credentials. Those are passed using the configuration of the bundle to prevent disclosure of the password in the ElasticMS backend and Elasticsearch cluster.
The 'connections' parameter allows to add one or more connection configurations as follows:
```yaml 
ems_submission:
  connections: '[{"connection": "service-now-instance-a", "user": "instance-a-username", "password": "instance-a-password"}, {"connection": "service-now-instance-b", "user": "instance-b-username", "password": "instance-b-password"}]'
```

Each configuration has a "connection", "user", and "password" entry.
* "connection" is used to identify the user/password combination from within a submission template
* "user" is the username needed to connect to the service (the name of this key is free of choice)
* "password" is the password needed to connect to the service (the name of this key is free of choice)
An infinite amount of keys can be added to this configuration, only the "connection" key is obligatory.

### Fetch credentials for your service.
An example endpoint configuration to integrate with ServiceNow has access to the user/pass of the "service-now-instance-a" using the [emss_connection](/src/Resources/doc/twig.md) filter:
```twig
{
    "host": "https://example.service-now.com/api/now/table/my_table_name",
    "username": "{{'service-now-instance-a%.%user'|emss_connection}}",
    "password": "{{'service-now-instance-a%.%password'|emss_connection}}"
}
```
