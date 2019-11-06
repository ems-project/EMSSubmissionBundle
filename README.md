# EMSSubmissionBundle
Implementation of a Submission handling system for [EMSFormBundle](https://github.com/ems-project/EMSFormBundle)

## Coding standards
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

TODO: PHP Mess Detector can generate a report in ./phpmd.html, rule violations are ignored by Travis for now.
````bash
composer phpmd
composer phpmd-win
````

Use phpmd-win when working on Windows!

## Documentation

* [Configuration](../master/Resources/doc/config.md)
* [Handlers](../master/Resources/doc/handlers.md)
* [Twig Documentation](../master/Resources/doc/twig.md)


Submissions are handled based on configuration of an endpoint (email, Service Now, ...) and a data template structure the incomming data in the desired format.

A Rest API will be provided to allow submissions to specific preconfigured targerts.
Or the EMSFormBundle can be used to generate and validate the form containing the data. After validation th data will be send to this module, 
which uses configuration from ElasticMS to select a target and data template for the submission of the webform data.
