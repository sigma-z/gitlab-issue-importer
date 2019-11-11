GitLab issue importer
===

Imports issues from a yaml file.


Config file
---

This is an example php file (./config.php)

```php
<?php
return [
    'gitlab-url' => 'http://localhost/api/v4/projects/',
    'projectId' => 'test/project'
];
```


Input file (YAML)
---

This is an example yaml file for importing one issue
```yaml
milestone: 'milestone #1'
issues:
  - title: 'Issue #1'
    description: 'Description of issue #1'
    labels:
      - Bug
      - Support
    weight: 1
    related:
      - "#2"
```


Usage
---

Import issues

```shell script
./bin/import-gitlab-issues.php import-issues example/issues.yml PriVatEToKeN12345678
```

Link issues

```shell script
./bin/import-gitlab-issues.php link-issues 123:321 PriVatEToKeN12345678
```
