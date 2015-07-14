# ClamscanWeb
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/84f55dde-0257-457d-8e78-ee84235baa16/mini.png)](https://insight.sensiolabs.com/projects/84f55dde-0257-457d-8e78-ee84235baa16)
## Installing
### 1. Clone repo
```bash
git clone --recursive https://github.com/breaker1/ClamscanWeb.git
```

### 2. Install dependencies with composer
If you do not have composer, please install it as described
[in the composer install guide.](https://getcomposer.org/download/)
```bash
composer install --no-dev
```

### 3. Update the config file
Edit the `app/config/main.json` file to reflect your environment.

Database settings can be anything that doctrine will accept. You should be able
to connect the application to any SQL database. For more information
[refer to the doctrine configuration information.](http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html)

Email server settings should be straight forward enough. For more
information about the email settings,
[refer to the Swiftmailer documentation.](http://swiftmailer.org/docs/sending.html)

### 4. Create an htaccess file
You will want to have an authentication scheme. The application itself simply
relies on the web server authentication.
```ApacheConf
AuthType        CAS
AuthName        "IU-CAS"
Require         valid-user
```

You also will want to redirect queries for non-existent files and directories to
the index.php script to try to handle.
```ApacheConf
Options         -MultiViews
RewriteEngine   On
RewriteCond     %{REQUEST_FILENAME} !-f
RewriteCond     %{REQUEST_FILENAME} !-d
RewriteRule     ^   index.php [QSA,L]
```
