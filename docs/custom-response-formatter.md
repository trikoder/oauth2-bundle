# Creating a custom response formatter

To create a custom response formatter, your class must implement `Trikoder\Bundle\OAuth2Bundle\Response\Formatter`.

Once that's done, set the `response_formatter` in the configuration file to reference the new class

Example:
```yml
config/packages/trikoder_oauth2.yaml


    trikoder_oauth2:

        response_formatter: App\Response\CustomOAuthResponseFormatter

```
