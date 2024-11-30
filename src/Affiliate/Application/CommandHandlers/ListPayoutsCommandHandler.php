<?php

declare(strict_types=1);

namespace Affiliate\Application\CommandHandlers;

use Affiliate\Application\Commands\ListPayoutsCommand;
use Affiliate\Domain\Entities\PayoutEntity;
use Affiliate\Domain\Exceptions\PayoutNotFoundException;
use Affiliate\Domain\Repositories\PayoutRepositoryInterface;
use Shared\Domain\ValueObjects\CursorDirection;
use Traversable;

class ListPayoutsCommandHandler
{
    public function __construct(
        private PayoutRepositoryInterface $repo
    ) {}

    /**
     * @return Traversable<PayoutEntity>
     * @throws PayoutNotFoundException
     */
    public function handle(ListPayoutsCommand $cmd): Traversable
    {
        $cursor = $cmd->cursor
            ? $this->repo->ofId($cmd->cursor)
            : null;

        $payouts = $this->repo;

        if ($cmd->sortDirection) {
            $payouts = $payouts->sort($cmd->sortDirection, $cmd->sortParameter);
        }

        if ($cmd->status) {
            $payouts = $payouts->filterByStatus($cmd->status);
        }

        if ($cmd->user) {
            $payouts = $payouts->filterByUser($cmd->user);
        }

        if ($cmd->maxResults) {
            $payouts = $payouts->setMaxResults($cmd->maxResults);
        }

        if ($cursor) {
            if ($cmd->cursorDirection == CursorDirection::ENDING_BEFORE) {
                return $payouts = $payouts->endingBefore($cursor);
            }

            return $payouts->startingAfter($cursor);
        }

        return $payouts;
    }
}
