<?php

namespace Nimble\ElasticBundle\Index;

use Nimble\ElasticBundle\Exception\IndexNotFoundException;

class IndexManager
{
    /**
     * @var Index[]
     */
    protected $indexes = [];

    /**
     * @param Index $index
     */
    public function registerIndex(Index $index)
    {
        if (array_key_exists($index->getId(), $this->indexes)) {
            throw new \InvalidArgumentException(
                sprintf('Index "%s" is already registered.', $index->getId())
            );
        }

        $this->indexes[$index->getId()] = $index;
    }

    /**
     * @param string $name
     * @return Index|null
     */
    public function getIndex($name)
    {
        if (!$this->hasIndex($name)) {
            throw new IndexNotFoundException($name);
        }

        return $this->indexes[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasIndex($name)
    {
        return array_key_exists($name, $this->indexes);
    }

    /**
     * @return array
     */
    public function getIndexIds()
    {
        return array_keys($this->indexes);
    }

    /**
     * @return Index[]
     */
    public function getIndexes()
    {
        return array_values($this->indexes);
    }
}
