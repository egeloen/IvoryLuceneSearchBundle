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
use ZendSearch\Lucene\Index;
use ZendSearch\Lucene\Lucene;
use ZendSearch\Lucene\Search\QueryParser;
use ZendSearch\Lucene\Storage\Directory\Filesystem as ZfFilesystem;

/**
 * @author GeLo <geloen.eric@gmail.com>
 */
class LuceneManager
{
    const DEFAULT_ANALYZER = 'ZendSearch\Lucene\Analysis\Analyzer\Common\Text\CaseInsensitive';
    const DEFAULT_MAX_BUFFERED_DOCS = 10;
    const DEFAULT_MAX_MERGE_DOCS = PHP_INT_MAX;
    const DEFAULT_MERGE_FACTOR = 10;
    const DEFAULT_PERMISSIONS = 0777;
    const DEFAULT_AUTO_OPTIMIZED = false;
    const DEFAULT_QUERY_PARSER_ENCODING = '';
    private $indexes = array();
    private $configs = array();

    /**
     * @return bool
     */
    public function hasIndexes()
    {
        return !empty($this->configs);
    }

    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function hasIndex($identifier)
    {
        return isset($this->configs[$identifier]);
    }

    /**
     * @param string $identifier
     *
     * @return Index
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
     * @param array $indexes
     *
     * @throws \InvalidArgumentException
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
     * @param string $identifier
     * @param string $path
     * @param string $analyzer
     * @param int    $maxBufferedDocs
     * @param int    $maxMergeDocs
     * @param int    $mergeFactor
     * @param int    $permissions
     * @param bool   $autoOptimized
     * @param string $queryParserEncoding
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
            'query_parser_encoding' => $queryParserEncoding,
        );
    }

    /**
     * @param string $identifier
     * @param bool   $removeDirectory
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
     * @param string $identifier
     */
    public function eraseIndex($identifier)
    {
        $config = $this->getConfig($identifier);

        $filesystem = new SfFilesystem();
        $filesystem->remove($config['path']);
    }

    /**
     * @param string $identifier
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    private function getConfig($identifier)
    {
        if (!isset($this->configs[$identifier])) {
            throw new \InvalidArgumentException(sprintf('The lucene index "%s" does not exist.', $identifier));
        }

        return $this->configs[$identifier];
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    private function checkPath($path)
    {
        return file_exists($path) && is_readable($path) && ($resources = scandir($path)) && (count($resources) > 2);
    }
}
