GitLab issue importer
===

Imports issues from a yaml file.


Input file (YAML)
---

This is an example yaml file for importing one issue
```yaml
gitlab-url: "http://gitlab.example.com/"
milestone: 'milestone #1'
project: test/project
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

```shell script
./bin/import-gitlab-issues.php example/issues.yml PriVatEToKeN12345678 
```


Open ToDos
---

Key  'related' is not supported, yet, because yet I don't know,
if issues can be related via the GitLab API. 

