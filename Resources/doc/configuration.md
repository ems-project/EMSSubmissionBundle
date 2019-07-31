Endpoint
===

The system is using the client request from the EMS\ClientHelper namespace. Make sure to define it in your `services.yaml` configuration:
```yaml
EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequest: '@emsch.client_request.website'
```

ServiceNow
===

To integrate with ServiceNow we need credentials, those are passed using the configuration of the bundle to prevent disclosure of the password in ElasticMS.
The 'connections' parameter in the package `ems_submission.yaml` allows to add one or more connection configurations:
```yaml 
ems_submission:
  connections: '[{"connection": "service-now-instance-a", "user": "instance-a-username", "password": "instance-a-password"}, {"connection": "service-now-instance-b", "user": "instance-b-username", "password": "instance-b-password"}]'
```

The Submission content type is able to fetch these credentials by using the following placeholders:
```twig
%service-now-instance-a.user% {# will be replaced with the username of the connection "service-now-instance-a" #}
%service-now-instance-a.password% {# will be replaced with the password of the connection "service-now-instance-a" #}

%service-now-instance-b.user% {# will be replaced with the username of the connection "service-now-instance-b" #}
%service-now-instance-b.password% {# will be replaced with the password of the connection "service-now-instance-b" #}
```