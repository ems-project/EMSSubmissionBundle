EMSSubmissionBundle
=============

Submissions are handled based on configuration of an endpoint (email, Service Now, ...) and a data template structure the incomming data in the desired format.

A Rest API will be provided to allow submissions to specific preconfigured targerts.
Or the EMSFormBundle can be used to generate and validate the form containing the data. After validation th data will be send to this module, 
which uses configuration from ElasticMS to select a target and data template for the submission of the webform data.

Coding standards
----------------
PHP Code Sniffer is available via composer, the standard used is defined in phpcs.xml.diff:
````bash
composer phpcs
````

If your code is not compliant, you could try fixing it automatically:
````bash
composer phpcbf
````

PHPStan is run at level 7, you can check for errors locally using:
`````bash
composer phpstan
`````

Documentation
-------------

[Configuration](../master/Resources/doc/configuration.md)