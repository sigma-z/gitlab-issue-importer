#!/usr/bin/env php
<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use SigmaZ\GitLabIssueImporter\Command\IssueImporter;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new IssueImporter());
$application->run();
