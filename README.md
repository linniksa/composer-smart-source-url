
# Composer Smart Source Url

Helps composer choose the right url for private packages from gitlab, github and many other git hosting systems.

This plugin change ssh url to http dynamicaly url when you provide http credentials, for example:

```json
{
    "http-basic": {
        "github.com": {
            "username": "user",
            "password": "pass"
        }
    }
}
```

Thas was useful for testing purpose 
