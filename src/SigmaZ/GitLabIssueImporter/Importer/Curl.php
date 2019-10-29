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

    public function importIssue(array $issueData, array $additionalFields = []): bool
    {
        #$issueData = array_merge($additionalFields, $issueData);
        return $this->postIssue($issueData);
    }

    private function postIssue(array $issueData): bool
    {
        $success = true;
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->gitLabUrl);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, ['PRIVATE-TOKEN: ' . $this->privateToken]);
        curl_setopt($curlHandle, CURLOPT_POST, true);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS	, $this->convertDataToPostFields($issueData));
        curl_exec($curlHandle);
        if (curl_errno($curlHandle) !== 0) {
            trigger_error(curl_error($curlHandle));
            $success = false;
        }
        curl_close($curlHandle);

        return $success;
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

}
