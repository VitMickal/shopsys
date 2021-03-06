<?php

declare(strict_types=1);

namespace Shopsys\Releaser\ReleaseWorker\ReleaseCandidate;

use PharIo\Version\Version;
use Shopsys\Releaser\ReleaseWorker\AbstractShopsysReleaseWorker;
use Shopsys\Releaser\Stage;

final class SendBranchForReviewAndTestsReleaseWorker extends AbstractShopsysReleaseWorker
{
    /**
     * @param \PharIo\Version\Version $version
     * @return string
     */
    public function getDescription(Version $version): string
    {
        return '[Manually] Send the branch for review and tests';
    }

    /**
     * Higher first
     * @return int
     */
    public function getPriority(): int
    {
        return 730;
    }

    /**
     * @param \PharIo\Version\Version $version
     */
    public function work(Version $version): void
    {
        $this->confirm('Confirm the branch is sent to code-review');
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return Stage::RELEASE_CANDIDATE;
    }
}
