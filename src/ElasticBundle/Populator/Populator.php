<?php

namespace Nimble\ElasticBundle\Populator;

use Nimble\ElasticBundle\Document;
use Nimble\ElasticBundle\Transformer\TransformerManager;
use Nimble\ElasticBundle\Type\Type;
use Symfony\Component\Console\Helper\ProgressBar;

class Populator
{
    /**
     * @var Type
     */
    private $type;

    /**
     * @var PopulationFetcherInterface
     */
    private $fetcher;

    /**
     * @var TransformerManager
     */
    private $transformer;

    /**
     * @param Type $type
     * @param PopulationFetcherInterface $fetcher
     * @param TransformerManager $transformer
     */
    public function __construct(Type $type, PopulationFetcherInterface $fetcher, TransformerManager $transformer)
    {
        $this->type = $type;
        $this->fetcher = $fetcher;
        $this->transformer = $transformer;
    }

    /**
     * @param array $entities
     * @return Document[]
     */
    protected function transformEntitiesToDocuments(array $entities)
    {
        $documents = [];

        foreach ($entities as $entity) {
            $documents = array_merge($documents, $this->transformer->transformToDocuments(
                $entity,
                $this->type->getIndex()->getId(),
                $this->type->getName()
            ));
        }

        return $documents;
    }

    /**
     * TODO: make a progress bar interface an an adapter for symfony's ProgressBar
     *
     * @param int $batchSize
     * @param ProgressBar $progress
     * @return int Number of documents created/updated.
     */
    public function populate($batchSize, ProgressBar $progress = null)
    {

        $documentCount = 0;
        $offset = 0;
        $count = $this->fetcher->getEntityCount();

        if ($count === 0) {
            return 0;
        }

        if (null !== $progress) {
            $progress->start($count);
        }

        while ($offset < $count) {
            $entities = $this->fetcher->fetchEntities($offset, $batchSize);
            $batch = $this->transformEntitiesToDocuments($entities);
            $this->type->putDocuments($batch);

            if (null !== $progress) {
                $progress->advance(count($entities));
            }

            $documentCount += count($batch);
            $offset += $batchSize;

            unset($entities);
            unset($batch);
        }

        if (null !== $progress) {
            $progress->finish();
        }

        return $documentCount;
    }
}
