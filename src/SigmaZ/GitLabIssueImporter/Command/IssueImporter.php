<?php declare(strict_types=1);
/*
 * This file is part of the SigmaZ\GitLabIssueImporter package.
 * (c) Steffen Zeidler <sigma_z@sigma-scripts.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SigmaZ\GitLabIssueImporter\Command;

use SigmaZ\GitLabIssueImporter\Importer\Curl;
use SigmaZ\GitLabIssueImporter\Importer\Guzzle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Parser;

class IssueImporter extends Command
{

    protected static $defaultName = 'import-issues';

    /** @var Parser */
    private $yamlParser;

    protected function configure(): void
    {
        $this->setDescription('Imports issues from a yaml file.');
        $this->setHelp('This command imports issues from a yaml file to GitLab');

        $this->addArgument('in', InputArgument::REQUIRED, 'input file in yaml');
        $this->addArgument('privateToken', InputArgument::REQUIRED, 'private GitLab token');

        $this->yamlParser = new Parser();
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $inputFile = $input->getArgument('in');
        $this->validateInputFile($inputFile);
        $privateToken = $input->getArgument('privateToken');
        $this->validatePrivateToken($privateToken);

        $issuesData = $this->parse($inputFile);
        $this->validateIssuesData($issuesData);
        $this->importIssues($issuesData, $privateToken);
    }

    private function validateInputFile(string $inputFile): void
    {
        $pathInfo = pathinfo($inputFile);

        if (!isset($pathInfo['extension'])) {
            throw new \RuntimeException('Expected input file to be a YAML file!');
        }
        if ($pathInfo['extension'] !== 'yml' && $pathInfo['extension'] !== 'yaml') {
            throw new \RuntimeException('Expected input file to be a YAML file! Got: ' . $pathInfo['extension']);
        }
        if (!is_file($inputFile)) {
            throw new \RuntimeException("Could not read file '$inputFile'!");
        }
    }

    private function validatePrivateToken(?string $privateToken): void
    {
        if (strlen($privateToken) < 20) {
            throw new \RuntimeException('Your private GitLab token should be at least 20 characters long!');
        }
    }

    private function parse(string $inputFile): array
    {
        $input = file_get_contents($inputFile);
        return $this->yamlParser->parse($input);
    }

    private function validateIssuesData(array $issuesData): void
    {
        if (!isset($issuesData['gitlab-url'])) {
            throw new \LogicException("Missing key 'gitlab-url' in YAML file!");
        }
        if (!isset($issuesData['project'])) {
            throw new \LogicException("Missing key 'project' in YAML file!");
        }
        if (!isset($issuesData['issues']) || !is_array($issuesData['issues'])) {
            throw new \LogicException("Expected key 'issues' is an array!");
        }
    }

    private function importIssues(array $issuesData, string $privateToken): void
    {
        $config = $this->loadConfig();

        $project = $config['project'];
        $additionalFields = [
            'milestone' => $issuesData['milestone'] ?? null
        ];

        foreach ($issuesData['issues'] as $issueData) {
            $issueProject = $issueData['project'] ?? $project;
            $gitLabUrl = $config['gitlab-url'] . urlencode($issueProject) . '/issues';
            $importer = new Guzzle($gitLabUrl, $privateToken);
//            $importer = new Curl($gitLabUrl, $privateToken);
            $importer->importIssue(array_merge($additionalFields, $issueData), $project);
        }
    }

    private function loadConfig()
    {
        return require __DIR__ . '/../../../../config.php';
    }

}
