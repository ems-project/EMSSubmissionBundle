# Email handler

Sends an email to the email address defined in the endpoint field. 

## Endpoint

```twig 
incoming-email@example.com
```

## Message
Email sender, subject, and body are defined in the message field using the "from", "subject", and "body" keys respectively. The output should be a json object.
```twig 
{
    "from": "noreply@example.com",
    "subject": "Form submission from website",
    "body": "foobar"
}
```

The message can access the filled in data of the form, for example submitted fields "email", "name", "firstname". Use the following approach if you want to include newlines in your email body.
```twig 
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