# Supported handlers

## Configuration of a handler
In the backend a content type is defined to configure your submit procedure. The handler has access to the complete form data, and to the response of the previously defined submit handler. When multiple submit handlers are attached to a form instance, they are called in order of definition, and the result of the previous submission handler is passed to the next one.
More info about this concept can be found in the EMSFormBundle's "[Handle Submitted data](https://github.com/ems-project/EMSFormBundle/blob/master/Resources/doc/handlers.md)" section.

A handler has 3 parts in the backend:
1. Definition of it's Type
1. An endpoint field
1. A message field

The endpoint and message fields are to be configured as defined by the Type of the handler using twig. The endpoint typically contains connection information, while the message contains information that is derived from the data submitted through the form.
Both fields have full access to the data send through the form using the `data` variable. When handlers are chained, the response of the previous handler is available through the `previous` variable.

Depending on the handler type, the endpoint and message fields contain a simple text entry, or a twig object with specific keys.

## Overview
* [Email](#email)
* [ServiceNow](#servicenow)

## Handlers

### Email <a name="email"/>
Sends an email to the email address defined in the endpoint field. 
```twig 
//endpoint field
incoming-email@example.com
```

Email sender, subject, and body are defined in the message field using the "from", "subject", and "body" keys respectively. The output should be a json object.
```twig 
//message field
{
    "from": "noreply@example.com",
    "subject": "Form submission from website",
    "body": "foobar"
}
```

The message can access the filled in data of the form, for example submitted fields "email", "name", "firstname". Use the following approach if you want to include newlines in your email body.
```twig 
//message field
{% set body %}
    Email {{ data.email }}
    Name {{ data.name }}
    Firstname {{ data.firstname }}
{% endset %}
{
    "from": "{{ data.email }}", 
    "subject": "Email Form subject", 
    "body": "{{ body|json_encode }}"
}
```

To include one or multiple attachments to your email, declare them to a variable as shown below.
```twig 
//message field
{% set body %}
    Email {{ data.email }}
    Name {{ data.name }}
    Firstname {{ data.firstname }}
{% endset %}
{
  "from": "{{ data.email }}",
  "subject": "Email Form subject",
  "body": "{{ body|json_encode }}",
  "attachments": {
      "file_1": {
          "pathname": "{{ data.file_1.getPathname()|json_encode }}",
          "originalName": "{{ data.file_1.getClientOriginalName() }}",
          "mimeType": "{{ data.file_1.getClientMimeType() }}"
        }
    }
}
```

### ServiceNow <a name="servicenow"/>

Sends data to a Service Now REST endpoint. 

#### Endpoint

The endpoint field contains the host, table, user, and password to connect to the REST endpoint. Connection parameters are fetched using the [connection configuration](/Resources/doc/config.md#connection)
```twig 
//endpoint field
{
    "host": "https://example.service-now.com",
    "table": "table_name",
    "username": "{{'connection-name%.%user'|emss_connection}}",
    "password": "{{'connection-name%.%password'|emss_connection}}"
}
```

If you don't use default endpoints, you can specify them :
```twig 
//endpoint field
{
    "host": "https://example.service-now.com",
    "table": "table_name",
    "bodyEndpoint": "/api/now/v1/table",
    "attachmentEndpoint": "/api/now/v1/attachment/file",
    "username": "{{'connection-name%.%user'|emss_connection}}",
    "password": "{{'connection-name%.%password'|emss_connection}}"
}
```

#### Message

The message field contains the data to be send to the REST endpoint, for example:
```twig 
//message field
{
    "body": {
        "title": "Unknown",
        "name": "{{ data.name }}",
        "firstname": "{{ data.firstname }}",
        "email1": "{{ data.email }}"
    }
}
```

To include one or multiple attachments to your email, declare them as shown below.
```twig 
//message field
{
    "body": {
        "title": "Unknown",
        "name": "{{ data.name }}",
        "firstname": "{{ data.firstname }}",
        "email1": "{{ data.email }}"
    },
    "attachments": {
        "file_1": {
            "pathname": "{{ data.file_1.getPathname()|json_encode }}",
            "originalName": "{{ data.file_1.getClientOriginalName() }}",
            "mimeType": "{{ data.file_1.getClientMimeType() }}"
        }
    }
}
```