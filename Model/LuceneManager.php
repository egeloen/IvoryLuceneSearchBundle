<?php

/*
 * This file is part of the Ivory Lucene Search package.
 *
 * (c) Eric GELOEN <geloen.eric@gmail.com>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Ivory\LuceneSearchBundle\Model;

use Symfony\Component\Filesystem\Filesystem as SfFilesystem;
use ZendSearch\Lucene\Analysis\Analyzer\Analyzer;
use ZendSearch\Lucene\Lucene;
use ZendSearch\Lucene\Storage\Directory\Filesystem as ZfFilesystem;
use ZendSearch\Lucene\Search\QueryParser;

/**
 * Lucene manager.
 *
 * @author GeLo <geloen.eric@gmail.com>
 */
class LuceneManager
{
    /** @const string */
    const DEFAULT_ANALYZER = 'ZendSearch\Lucene\Analysis\Analyzer\Common\Text\CaseInsensitive';

    /** @const integer */
    const DEFAULT_MAX_BUFFERED_DOCS = 10;

    /** @const integer */
    const DEFAULT_MAX_MERGE_DOCS = PHP_INT_MAX;

    /** @const integer */
    const DEFAULT_MERGE_FACTOR = 10;

    /** @const integer */
    const DEFAULT_PERMISSIONS = 0777;

    /** @const boolean */
    const DEFAULT_AUTO_OPTIMIZED = false;

    /** @const string */
    const DEFAULT_QUERY_PARSER_ENCODING = '';

    /** @var array */
    protected $indexes = array();

    /** @var array */
    protected $configs = array();

    /**
     * Checks if the lucene manager has indexes.
     *
     * @return boolean TRUE if the lucene manager has indexes else FALSE.
     */
    public function hasIndexes()
    {
        return !empty($this->configs);
    }

    /**
     * Checks if the index exists for the given lucene identifier.
     *
     * @param string $identifier The lucene identifier.
     *
     * @return boolean TRUE if the index exists else FALSE.
     */
    public function hasIndex($identifier)
    {
        return isset($this->configs[$identifier]);
    }

    /**
     * Gets the index mapped by the given lucene identifier.
     *
     * @param string $identifier The lucene identifier.
     *
     * @return \ZendSearch\Lucene\Index The lucene index.
     */
    public function getIndex($identifier)
    {
        $config = $this->getConfig($identifier);
        $path = $config['path'];

        if (!$this->checkPath($path)) {
            $this->indexes[$identifier] = Lucene::create($path);
        } else {
            $this->indexes[$identifier] = Lucene::open($path);
        }

        Analyzer::setDefault(new $config['analyzer']());

        $this->indexes[$identifier]->setMaxBufferedDocs($config['max_buffered_docs']);
        $this->indexes[$identifier]->setMaxMergeDocs($config['max_merge_docs']);
        $this->indexes[$identifier]->setMergeFactor($config['merge_factor']);

        ZfFilesystem::setDefaultFilePermissions($config['permissions']);

        if ($config['auto_optimized']) {
            $this->indexes[$identifier]->optimize();
        }

        QueryParser::setDefaultEncoding($config['query_parser_encoding']);

        return $this->indexes[$identifier];
    }

    /**
     * Set the lucene indexes.
     *
     * Example:
     *
     * array(
     *     'identifier1' => array(
     *         'path'                  => '/path/to/lucene/index1',
     *         'analyzer'              => 'ZendSearch\Lucene\Analysis\Analyzer\Common\Text\CaseInsensitive',
     *         'max_buffered_docs'     => 10,
     *         'max_merge_docs'        => PHP_INT_MAX,
     *         'merge_factor'          => 10,
     *         'permissions'           => 0777,
     *         'auto_optimized'        => false,
     *         'query_parser_encoding' => ''
     *     ),
     *     'identifier2' => array(
     *         'path' => '/path/to/lucene/index2'
     *     )
     * )
     *
     * The path is required. If you don't pass the other options, the default describes in the example will be used.
     *
     * @param array $indexes The lucene indexes.
     *
     * @throws \InvalidArgumentException If a lucene index path is not provided.
     */
    public function setIndexes(array $indexes)
    {
        foreach ($indexes as $identifier => $index) {
            if (!isset($index['path'])) {
                throw new \InvalidArgumentException('Each lucene index must have a path value.');
            }

            $this->setIndex(
                $identifier,
                $index['path'],
                isset($index['analyzer']) ? $index['analyzer'] : self::DEFAULT_ANALYZER,
                isset($index['max_buffered_docs']) ? $index['max_buffered_docs'] : self::DEFAULT_MAX_BUFFERED_DOCS,
                isset($index['max_merge_docs']) ? $index['max_merge_docs'] : self::DEFAULT_MAX_MERGE_DOCS,
                isset($index['merge_factor']) ? $index['merge_factor'] : self::DEFAULT_MERGE_FACTOR,
                isset($index['permissions']) ? $index['permissions'] : self::DEFAULT_PERMISSIONS,
                isset($index['auto_optimized']) ? $index['auto_optimized'] : self::DEFAULT_AUTO_OPTIMIZED,
                isset($index['query_parser_encoding']) ? $index['query_parser_encoding'] : self::DEFAULT_QUERY_PARSER_ENCODING
            );
        }
    }

    /**
     * Sets a lucene index.
     *
     * @param string  $identifier          The lucene identifier.
     * @param string  $path                The lucene path.
     * @param string  $analyzer            The lucene analyzer class name.
     * @param integer $maxBufferedDocs     The lucene max buffered docs.
     * @param integer $maxMergeDocs        The lucene max merge docs.
     * @param integer $mergeFactor         The lucene merge factor.
     * @param integer $permissions         The lucene permissions.
     * @param boolean $autoOptimized       The lucene auto optimized.
     * @param string  $queryParserEncoding The lucene query parser encoding.
     */
    public function setIndex(
        $identifier,
        $path,
        $analyzer = self::DEFAULT_ANALYZER,
        $maxBufferedDocs = self::DEFAULT_MAX_BUFFERED_DOCS,
        $maxMergeDocs = self::DEFAULT_MAX_MERGE_DOCS,
        $mergeFactor = self::DEFAULT_MERGE_FACTOR,
        $permissions = self::DEFAULT_PERMISSIONS,
        $autoOptimized = self::DEFAULT_AUTO_OPTIMIZED,
        $queryParserEncoding = self::DEFAULT_QUERY_PARSER_ENCODING
    ) {
        $this->configs[$identifier] = array(
            'path'                  => $path,
            'analyzer'              => $analyzer,
            'max_buffered_docs'     => $maxBufferedDocs,
            'max_merge_docs'        => $maxMergeDocs,
            'merge_factor'          => $mergeFactor,
            'permissions'           => $permissions,
            'auto_optimized'        => $autoOptimized,
            'query_parser_encoding' => $queryParserEncoding
        );
    }

    /**
     * Removes a lucene index.
     *
     * @param string  $identifier      The lucene identifier.
     * @param boolean $removeDirectory TRUE if the index should be erased else FALSE.
     */
    public function removeIndex($identifier, $removeDirectory = false)
    {
        if ($removeDirectory) {
            $this->eraseIndex($identifier);
        }

        unset($this->configs[$identifier]);

        if (isset($this->indexes[$identifier])) {
            unset($this->indexes[$identifier]);
        }
    }

    /**
     * Erases a lucene index.
     *
     * @param string $identifier The lucene identifier.
     */
    public function eraseIndex($identifier)
    {
        $config = $this->getConfig($identifier);

        $filesystem = new SfFilesystem();
        $filesystem->remove($config['path']);
    }

    /**
     * Gets the config for the given lucene identifier.
     *
     * @param string $identifier The lucene identifier.
     *
     * @throws \InvalidArgumentException If the lucene index does not exist.
     *
     * @return array The config.
     */
    protected function getConfig($identifier)
    {
        if (!isset($this->configs[$identifier])) {
            throw new \InvalidArgumentException(sprintf('The lucene index "%s" does not exist.', $identifier));
        }

        return $this->configs[$identifier];
    }

    /**
     * Checks if a lucene index path exists.
     *
     * @param string $path The lucene index path.
     *
     * @return boolean TRUE if the lucene index path exists else FALSE.
     */
    protected function checkPath($path)
    {
        return file_exists($path) && is_readable($path) && ($resources = scandir($path)) && (count($resources) > 2);
    }
}
