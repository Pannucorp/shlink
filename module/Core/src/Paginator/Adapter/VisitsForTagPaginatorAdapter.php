<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Core\Paginator\Adapter;

use Happyr\DoctrineSpecification\Specification\Specification;
use Shlinkio\Shlink\Core\Model\VisitsParams;
use Shlinkio\Shlink\Core\Repository\VisitRepositoryInterface;
use Shlinkio\Shlink\Core\Visit\Persistence\VisitsCountFiltering;
use Shlinkio\Shlink\Rest\Entity\ApiKey;

class VisitsForTagPaginatorAdapter extends AbstractCacheableCountPaginatorAdapter
{
    private VisitRepositoryInterface $visitRepository;
    private string $tag;
    private VisitsParams $params;
    private ?ApiKey $apiKey;

    public function __construct(
        VisitRepositoryInterface $visitRepository,
        string $tag,
        VisitsParams $params,
        ?ApiKey $apiKey
    ) {
        $this->visitRepository = $visitRepository;
        $this->params = $params;
        $this->tag = $tag;
        $this->apiKey = $apiKey;
    }

    public function getSlice($offset, $length): array // phpcs:ignore
    {
        return $this->visitRepository->findVisitsByTag(
            $this->tag,
            $this->params->getDateRange(),
            $length,
            $offset,
            $this->resolveSpec(),
        );
    }

    protected function doCount(): int
    {
        return $this->visitRepository->countVisitsByTag(
            $this->tag,
            new VisitsCountFiltering(
                $this->params->getDateRange(),
                $this->params->excludeBots(),
                $this->resolveSpec(),
            ),
        );
    }

    private function resolveSpec(): ?Specification
    {
        return $this->apiKey !== null ? $this->apiKey->spec(true) : null;
    }
}
