<?php declare(strict_types=1);
/*
 * This file is part of the SigmaZ\GitLabIssueImporter package.
 * (c) Steffen Zeidler <sigma_z@sigma-scripts.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SigmaZ\GitLabIssueImporter\Importer;


class Curl implements Importer
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
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->gitLabUrl);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, ['PRIVATE-TOKEN: ' . $this->privateToken]);
        curl_setopt($curlHandle, CURLOPT_POST, true);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS	, $this->convertDataToPostFields($issueData));
        $response = curl_exec($curlHandle);
        if (curl_errno($curlHandle) !== 0) {
            trigger_error(curl_error($curlHandle));
        }
        curl_close($curlHandle);

        $data = json_decode($response, true);
        return $data['iid'];
    }

    private function convertDataToPostFields(array $issueData): string
    {
        $postFields = '';
        foreach ($issueData as $key => $value) {
            $valueAsString = is_array($value) ? implode(',', $value) : (string)$value;
            $postFields .= urlencode($key) . '=' . urlencode($valueAsString) . '&';
        }
        return $postFields;
    }

    public function linkIssues(int $issueId, array $relatedIssues, string $project): void
    {
        foreach ($relatedIssues as $relatedIssue) {
            $this->linkIssue($issueId, $relatedIssue, $project);
        }
    }

    public function linkIssue(int $issueId, string $relatedIssue, string $project): void
    {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->gitLabUrl . "/$issueId/links");
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, ['PRIVATE-TOKEN: ' . $this->privateToken]);
        curl_setopt($curlHandle, CURLOPT_POST, true);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS	, $this->convertDataToPostFields([
            'target_project_id' => urlencode($project),
            'target_issue_iid' => ltrim('#', $relatedIssue)
        ]));
        $response = curl_exec($curlHandle);
        if (curl_errno($curlHandle) !== 0) {
            trigger_error(curl_error($curlHandle));
        }
        curl_close($curlHandle);
    }

}
