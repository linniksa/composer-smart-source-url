
# Composer Smart Source Url

This plugin helps composer choose the right url for private packages (e.g. for gitlab runner) from gitlab (github btw also supported), in conditions when you need to specify ssh url for repository in `composer.json` (e.g. for deployment).

When you specify auth credentials in `auth.json`, the plugin changes the URL scheme from ssh to http (s) on the fly (without touching any files).

# Example

Add plugin to you project:

    composer.phar require-dev linniksa/composer-smart-source-url
 
Add this lines to your `.gitlab-ci.yml`:

```yml
- >
      echo > auth.json -e '{
        "http-basic":{
          "[YOU DOMAIN]": {
            "username":"gitlab-ci-token",
            "password": "'${CI_JOB_TOKEN}'"
          }
        }
      }'
```
Don't forget to change `[YOU DOMAIN]`.

That it, now your private repositories can be easily cleaned by gitlab runner
