# ClamscanWeb
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/84f55dde-0257-457d-8e78-ee84235baa16/mini.png)](https://insight.sensiolabs.com/projects/84f55dde-0257-457d-8e78-ee84235baa16)

## Create an htaccess file
You will want to have an authentication scheme. The application itself simply relies on the web server authentication.
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