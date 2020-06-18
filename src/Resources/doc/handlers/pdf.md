# Pdf handler

Create a pdf that can be used in chained handlers.

## Endpoint

Contains the filename.

```twig 
example.pdf
```

## Message

The message contains pure HTML which will be converted to a PDF.
You can use inline css for styling the pdf document. 

```html 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
</head>
<body>
<h1>Content pdf</h1>
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean euismod aliquam nisl, 
ut varius purus vulputate quis. Nulla vehicula consequat ante a facilisis. 
Nunc tincidunt mauris at tincidunt feugiat. Praesent lacinia lacinia gravida. 
Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
Curabitur quis convallis eros. Curabitur scelerisque enim sapien, sed condimentum enim laoreet vel. 
Ut ut semper urna. In interdum eros vel eros interdum rutrum.</p>   
</body>
</html>
```

## Chaining to email

This example only works if the first submission (index 0) handler is a pdf handler.
Then we can access the response in the a second email handler.

```twig
{% autoescape %}
    {% set message = {
        'from': 'noreply@example.test',
        'subject': 'Test email with pdf',
        'attachments': [
            {
                'base64': request.responses.0.content|raw,
                'filename': request.responses.0.filename|raw,
                'mimeType': 'application/pdf'
            }
        ]
    } %}
{% endautoescape %}
{{ message|json_encode|raw }}
```
