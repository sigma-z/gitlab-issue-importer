<?php


namespace SigmaZ\GitLabIssueImporter\Importer;


use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Guzzle implements Importer
{
    /** @var string */
    private $gitLabUrl;

    /** @var string */
    private $privateToken;

    public function __construct(string $gitLabUrl, string $privateToken)
    {
        $this->gitLabUrl = $gitLabUrl;
        $this->privateToken = $privateToken;
    }

    public function importIssue(array $issueData, string $project): bool
    {
        $issueId = $this->postIssue($issueData);
        if (!empty($issueData['related'])) {
            $this->linkIssues($issueId, $issueData['related'], $project);
        }
        return true;
    }

    private function postIssue(array $issueData): int
    {
        $request = new Request(
            'POST',
            $this->gitLabUrl,
            ['PRIVATE-TOKEN' => $this->privateToken],
            $this->getPostParams($issueData)
        );

        $client = new Client(['verify' => false]);
        $result = $client->send($request);
        $responseData = json_decode((string)$result->getBody(), true);
        return $responseData['iid'];
    }

    public function linkIssues(int $issueId, array $relatedIssues, string $projectId): void
    {
        foreach ($relatedIssues as $relatedIssue) {
            $this->linkIssue($issueId, $relatedIssue, $projectId);
        }
    }

    public function linkIssue(int $issueId, string $relatedIssue, string $projectId): void
    {
        $request = new Request(
            'POST',
            $this->gitLabUrl . "/$issueId/links",
            ['PRIVATE-TOKEN' => $this->privateToken],
            $this->getPostParams([
                'target_project_id' => $projectId,
                'target_issue_iid' => ltrim($relatedIssue, '#')
            ])
        );

        $client = new Client(['verify' => false]);
        $result = $client->send($request);
        $responseData = json_decode((string)$result->getBody(), true);
        var_dump($responseData);
    }

    /**
     * @param array $issueData
     * @return string
     */
    private function getPostParams(array $issueData): string
    {
        foreach ($issueData as $key => &$value) {
            $value = is_array($value) ? implode(',', $value) : $value;
        }
        return http_build_query($issueData);
    }

}
