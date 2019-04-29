Endpoint
===

The system is using the client request from the EMS\ClientHelper namespace. Make sure to define it in your `services.yaml` configuration:
```yaml
EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequest: '@emsch.client_request.website'
```