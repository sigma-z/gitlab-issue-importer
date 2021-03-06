<?php


namespace SigmaZ\GitLabIssueImporter\Command;


use SigmaZ\GitLabIssueImporter\Importer\Guzzle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class LinkIssue extends Command
{

    protected static $defaultName = 'link-issues';

    protected function configure(): void
    {
        $this->setDescription('Links issues.');
        $this->setHelp('This command links issues in GitLab');

        $this->addArgument('issues', InputArgument::REQUIRED, "'issues - format: '#1:#2' or '1:2");
        $this->addArgument('privateToken', InputArgument::REQUIRED, 'private GitLab token');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $issues = $input->getArgument('issues');
        $this->validateIssuesFormat($issues);
        $privateToken = $input->getArgument('privateToken');
        $this->validatePrivateToken($privateToken);

        $this->linkIssues($issues, $privateToken);
    }

    private function validatePrivateToken(?string $privateToken): void
    {
        if (strlen($privateToken) < 20) {
            throw new \RuntimeException('Your private GitLab token should be at least 20 characters long!');
        }
    }

    private function validateIssuesFormat(string $issues)
    {
        if (preg_match('/^#?\d+:#?\d+$/', $issues) <= 0) {
            throw new \RuntimeException('To link issues you must use format #123:#321 or 123:321');
        }
    }

    private function linkIssues(string $issues, string $privateToken)
    {
        $config = $this->loadConfig();
        $projectId = $config['projectId'];
        $gitLabUrl = $config['gitlab-url'] . urlencode($projectId) . '/issues';
        $importer = new Guzzle($gitLabUrl, $privateToken);
        list($issueA, $issueB) = explode(':', $issues);
        $issueA = ltrim($issueA, '#');

        $importer->linkIssue($issueA, $issueB, $projectId);
    }

    private function loadConfig()
    {
        return require __DIR__ . '/../../../../config.php';
    }

}
