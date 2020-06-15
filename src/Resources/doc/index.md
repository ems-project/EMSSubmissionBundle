# The EMS SubmissionBundle

## Handlers
In the backend a content type is defined to configure your submit procedure. The handler has access to the complete form data, and to the response of the previously defined submit handler. When multiple submit handlers are attached to a form instance, they are called in order of definition, and the result of the previous submission handler is passed to the next one.
More info about this concept can be found in the EMSFormBundle's "[Handle Submitted data](https://github.com/ems-project/EMSFormBundle/blob/master/Resources/doc/handlers.md)" section.

A handler has 3 parts in the backend:
1. Definition of it's Type
1. An endpoint field
1. A message field

The endpoint and message fields are to be configured as defined by the Type of the handler using twig. The endpoint typically contains connection information, while the message contains information that is derived from the data submitted through the form.
Both fields have full access to the data send through the form using the `data` variable. When handlers are chained, the response of the previous handler is available through the `previous` variable.

Depending on the handler type, the endpoint and message fields contain a simple text entry, or a twig object with specific keys.

### Supported handlers

* [Email](handlers/email.md)
* [Pdf](handlers/pdf.md)
* [ServiceNow](handlers/serviceNow.md)

## Configuration
```yaml
#config/packages/ems_submission.yaml
ems_submission:
  default_timeout: '%env(int:EMSS_DEFAULT_TIMEOUT)%'
  connections: '%env(json:EMSS_CONNECTIONS)%'
```

### Default Timeout
Whenever a form is submitted using our handlers, we should limit the amount of time that is allowed for the request to succeed. The `default_timeout` requires a number that represents the allowed number of seconds before we timeout waiting for external feedback.

### Connections <a name="connection" />
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
