<?php
/*
 * This file is part of the SigmaZ\GitLabIssueImporter package.
 * (c) Steffen Zeidler <sigma_z@sigma-scripts.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SigmaZ\GitLabIssueImporter\Importer;


interface Importer
{

    public function importIssue(array $issueData, array $additionalFields = []): bool;

}
