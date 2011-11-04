<?php

namespace Ivory\LuceneSearchBundle\Model;

use Zend\Search\Lucene\Analysis\Analyzer\Analyzer;
use Zend\Search\Lucene\Storage\Directory\Filesystem;
use Ivory\LuceneSearchBundle\Model\Util;

/**
 * Lucene manager
 *
 * @author GeLo <geloen.eric@gmail.com>
 */
class LuceneManager
{
    /**
     * @var array Associative array mapping the lucene identifier to his index
     */
    protected $indexes = array();
    
    /**
     * @var array Associative array mapping the lucene identifier to his path
     */
    protected $paths = array();
    
    /**
     * @var array Associative array mapping the lucene identifier to his analyzer
     */
    protected $analyzers = array();
    
    /**
     * @var array Associative array mapping the lucene identifier to his max buffered document
     */
    protected $maxBufferedDocs = array();
    
    /**
     * @var array Associative array mapping the lucene identifier to his max merge document
     */
    protected $maxMergeDocs = array();
    
    /**
     * @var array Associative array mapping the lucene identifier to his merge factor
     */
    protected $mergeFactor = array();
    
    /**
     * @var array Associative array mapping the lucene identifier to his file permissions
     */
    protected $permissions = array();
    
    /**
     * @var array Associative array mapping the lucene identifier to his auto optimized flag
     */
    protected $autoOptimized = array();
    
    /**
     * Checks if the index exists for the given lucene identifier
     *
     * @param string $identifier
     * @return boolean TRUE if the index exists else FALSE
     */
    public function hasIndex($identifier)
    {
        return $this->hasPath($identifier)
            && $this->hasAnalyzer($identifier)
            && $this->hasMaxBufferedDocs($identifier)
            && $this->hasMaxMergeDocs($identifier)
            && $this->hasMergeFactor($identifier)
            && $this->hasPermissions($identifier)
            && $this->hasAutoOptimized($identifier);
    }
    
    /**
     * Gets the index mapped by the given lucene identifier
     * 
     * If the auto optimized flag is activated for the given lucene identifier, the index will be optimized
     *
     * @param string $identifier
     * @return Zend\Search\Lucene\Index
     */
    public function getIndex($identifier)
    {
        $path = $this->getPath($identifier);
        
        if(!file_exists($path))
            $this->indexes[$identifier] = Lucene::create($path);
        else
            $this->indexes[$identifier] = Lucene::open($path);
        
        $analyzer = $this->getAnalyzer($identifier);
        Analyzer::setDefault(new $analyzer);
        
        $this->indexes[$identifier]->setMaxBufferedDocs($this->getMaxBufferedDocs($identifier));
        $this->indexes[$identifier]->setMaxMergeDocs($this->getMaxMergeDocs($identifier));
        $this->indexes[$identifier]->setMergeFactor($this->getMergeFactor($identifier));

        Filesystem::setDefaultFilePermissions($this->getPermissions($identifier));
        
        if($this->getAutoOptimized($identifier))
            $this->indexes[$identifier]->optimize();

        return $this->indexes[$identifier];
    }
    
    /**
     * Set the indexes
     * 
     * Example:
     * array(
     *     'identifier1' => array(
     *         'path' => '/path/to/lucene/index1',
     *         'analyzer' => 'Zend\Search\Lucene\Analysis\Analyzer\Common\Text\CaseInsensitive',
     *         'max_buffered_docs' => 10,
     *         'max_merge_docs' => PHP_INT_MAX,
     *         'merge_factor' => 10,
     *         'permissions' => 0777,
     *         'auto_optimized' => false
     *     ),
     *     'identifier2' => array(
     *         'path' => '/path/to/lucene/index2'
     *     )
     * )
     * 
     * The path is required. If you don't pass the other options, the default describes in the example will be used.
     *
     * @param array $indexes 
     */
    public function setIndexes(array $indexes)
    {
        foreach(array_keys($this->indexes) as $identifier)
            $this->removeIndex($identifier);
        
        foreach($indexes as $identifier => $index)
        {
            if(!isset($index['path']))
                throw new \InvalidArgumentException('Each lucene index must have a path value.');
            else
            {
                if(!isset($index['analyzer']))
                    $index['analyzer'] = 'Zend\Search\Lucene\Analysis\Analyzer\Common\Text\CaseInsensitive';
                
                if(!isset($index['max_buffered_docs']))
                    $index['max_buffered_docs'] = 10;

                if(!isset($index['max_merge_docs']))
                    $index['max_merge_docs'] = PHP_INT_MAX;

                if(!isset($index['merge_factor']))
                    $index['merge_factor'] = 10;
                
                if(!isset($index['permissions']))
                    $index['permissions'] = 0777;

                if(!isset($index['auto_optimized']))
                    $index['auto_optimized'] = false;
                
                $this->addIndex($identifier, $index['path'], $index['analyzer'], $index['max_buffered_docs'], $index['max_merge_docs'], $index['merge_factor'], $index['permissions'], $index['auto_optimized']);
            }
        }
    }
    
    /**
     * Adds an index
     *
     * @param string $identifier
     * @param string $path
     * @param boolean $autoOptimized 
     */
    public function addIndex($identifier, $path, $analyzer = 'Zend\Search\Lucene\Analysis\Analyzer\Common\Text\CaseInsensitive', $maxBufferedDocs = 10, $maxMergeDocs = PHP_INT_MAX, $mergeFactor = 10, $permissions = 0777, $autoOptimized = false)
    {
        $this->addPath($identifier, $path);
        $this->addAnalyser($identifier, $analyzer);
        $this->addMaxBufferedDocs($identifier, $maxBufferedDocs);
        $this->addMaxMergeDocs($identifier, $maxMergeDocs);
        $this->addMergeFactor($identifier, $mergeFactor);
        $this->addPermissions($identifier, $permissions);
        $this->addAutoOptimized($identifier, $autoOptimized);
    }
    
    /**
     * Removes an index
     *
     * @param string $identifier 
     * @param boolean $removeDirectory
     */
    public function removeIndex($identifier, $removeDirectory = false)
    {
        if($removeDirectory)
            $this->eraseIndex($identifier);
        
        $this->removePath($identifier);
        $this->removeAnalyzer($identifier);
        $this->removeMaxBufferedDocs($identifier);
        $this->removeMaxMergeDocs($identifier);
        $this->removeMergeFactor($identifier);
        $this->removePermissions($identifier);
        $this->removeAutoOptimized($identifier);
        
        if(isset($this->indexes[$identifier]))
            unset($this->indexes[$identifier]);
    }
    
    /**
     * Erase an index
     *
     * @param string $identifier Index identifier
     */
    public function eraseIndex($identifier)
    {
        if($this->hasPath($identifier))
        {
            if(file_exists($this->getPath($identifier)))
                Util::removeDirectoryRecursilvy($this->getPath($identifier));
        }
        else
            throw new \InvalidArgumentException(sprintf('The lucene index path "%s" does not exist.', $identifier));
    }
    
    /**
     * Checks if the path exists for the given lucene identifier
     *
     * @param string $identifier
     * @return boolean TRUE if the path exists else FALSE
     */
    protected function hasPath($identifier)
    {
        self::checkIdentifier($identifier);
        
        return isset($this->paths[$identifier]);
    }
    
    /**
     * Gets the path mapped by the given lucene identifier
     *
     * @param string $identifier
     * @return string 
     */
    protected function getPath($identifier)
    {
        if($this->hasPath($identifier))
            return $this->paths[$identifier];
        else
            throw new \InvalidArgumentException(sprintf('The lucene index path "%s" does not exist.', $identifier));
    }
    
    /**
     * Adds a path
     *
     * @param string $identifier
     * @param string $path 
     */
    protected function addPath($identifier, $path)
    {
        self::checkIdentifier($identifier);
        self::checkPath($path);
        
        $this->paths[$identifier] = $path;
    }
    
    /**
     * Removes a path
     *
     * @param string $identifier
     */
    protected function removePath($identifier)
    {
        if($this->hasPath($identifier))
            unset($this->paths[$identifier]);
        else
            throw new \InvalidArgumentException(sprintf('The lucene index path "%s" does not exist.', $identifier));
    }
    
    /**
     * Checks if the analyzer exists for the given lucene identifier
     *
     * @param string $identifier
     * @return boolean TRUE if the analyzer exists else FALSE
     */
    protected function hasAnalyzer($identifier)
    {
        self::checkIdentifier($identifier);
        
        return isset($this->analyzers[$identifier]);
    }
    
    /**
     * Gets the analyzer mapped by the given lucene identifier
     *
     * @param string $identifier
     * @return string 
     */
    protected function getAnalyzer($identifier)
    {
        if($this->hasAnalyzer($identifier))
            return $this->analyzers[$identifier];
        else
            throw new \InvalidArgumentException(sprintf('The lucene index analyzer "%s" does not exist.', $identifier));
    }
    
    /**
     * Adds an analyzer
     *
     * @param string $identifier
     * @param string $analyzer 
     */
    protected function addAnalyser($identifier, $analyzer)
    {
        self::checkIdentifier($identifier);
        self::checkAnalyzer($analyzer);
        
        $this->analyzers[$identifier] = $analyzer;
    }
    
    /**
     * Removes an analyzer
     *
     * @param type $identifier 
     */
    protected function removeAnalyzer($identifier)
    {
        if($this->hasAnalyzer($identifier))
            unset($this->analyzers[$identifier]);
        else
            throw new \InvalidArgumentException(sprintf('The lucene index analyzer "%s" does not exist.', $identifier));
    }
    
    /**
     * Checks if the max buffered documents exists for the given lucene identifier
     *
     * @param string $identifier
     * @return boolean TRUE if the max buffered documents exists else FALSE
     */
    protected function hasMaxBufferedDocs($identifier)
    {
        self::checkIdentifier($identifier);
        
        return isset($this->maxBufferedDocs[$identifier]);
    }
    
    /**
     * Gets the max buffered documents mapped by the given lucene identifier
     *
     * @param string $identifier
     * @return integer 
     */
    protected function getMaxBufferedDocs($identifier)
    {
        if($this->hasMaxBufferedDocs($identifier))
            return $this->maxBufferedDocs[$identifier];
        else
            throw new \InvalidArgumentException(sprintf('The lucene index max buffered docs "%s" does not exist.', $identifier));
    }
    
    /**
     * Adds a max buffered documents
     *
     * @param string $identifier
     * @param integer $maxBufferedDocs
     */
    protected function addMaxBufferedDocs($identifier, $maxBufferedDocs)
    {
        self::checkIdentifier($identifier);
        self::checkMaxBufferedDocs($maxBufferedDocs);
        
        $this->maxBufferedDocs[$identifier] = $maxBufferedDocs;
    }
    
    /**
     * Removes a max buffered documents
     *
     * @param string $identifier 
     */
    protected function removeMaxBufferedDocs($identifier)
    {
        if($this->hasMaxBufferedDocs($identifier))
            unset($this->maxBufferedDocs[$identifier]);
        else
            throw new \InvalidArgumentException(sprintf('The lucene index max buffered docs "%s" does not exist.', $identifier));
    }
    
    /**
     * Checks if the max merge documents exists for the given lucene identifier
     *
     * @param string $identifier
     * @return boolean TRUE if the max merge documents exists else FALSE
     */
    protected function hasMaxMergeDocs($identifier)
    {
        self::checkIdentifier($identifier);
        
        return isset($this->maxMergeDocs[$identifier]);
    }
    
    /**
     * Gets the max merge documents mapped by the given lucene identifier
     *
     * @param string $identifier
     * @return integer 
     */
    protected function getMaxMergeDocs($identifier)
    {
        if($this->hasMaxMergeDocs($identifier))
            return $this->maxMergeDocs[$identifier];
        else
            throw new \InvalidArgumentException(sprintf('The lucene index max merge docs "%s" does not exist.', $identifier));
    }
    
    /**
     * Adds a max merge documents
     *
     * @param string $identifier
     * @param integer $maxMergeDocs
     */
    protected function addMaxMergeDocs($identifier, $maxMergeDocs)
    {
        self::checkIdentifier($identifier);
        self::checkMaxMergeDocs($maxMergeDocs);
        
        $this->maxMergeDocs[$identifier] = $maxMergeDocs;
    }
    
    /**
     * Removes a max merge documents
     *
     * @param string $identifier 
     */
    protected function removeMaxMergeDocs($identifier)
    {
        if($this->hasMaxMergeDocs($identifier))
            unset($this->maxMergeDocs[$identifier]);
        else
            throw new \InvalidArgumentException(sprintf('The lucene index max merge docs "%s" does not exist.', $identifier));
    }
    
    /**
     * Checks if the merge factor exists for the given lucene identifier
     *
     * @param string $identifier
     * @return boolean TRUE if the merge factor exists else FALSE
     */
    protected function hasMergeFactor($identifier)
    {
        self::checkIdentifier($identifier);
        
        return isset($this->mergeFactor[$identifier]);
    }
    
    /**
     * Gets the merge factor mapped by the given lucene identifier
     *
     * @param string $identifier
     * @return integer 
     */
    protected function getMergeFactor($identifier)
    {
        if($this->hasMergeFactor($identifier))
            return $this->mergeFactor[$identifier];
        else
            throw new \InvalidArgumentException(sprintf('The lucene index merge factor "%s" does not exist.', $identifier));
    }
    
    /**
     * Adds a merge factor
     *
     * @param string $identifier
     * @param integer $mergeFactor
     */
    protected function addMergeFactor($identifier, $mergeFactor)
    {
        self::checkIdentifier($identifier);
        self::checkMergeFactor($mergeFactor);
        
        $this->mergeFactor[$identifier] = $mergeFactor;
    }
    
    /**
     * Removes a merge factor
     *
     * @param string $identifier 
     */
    protected function removeMergeFactor($identifier)
    {
        if($this->hasMergeFactor($identifier))
            unset($this->mergeFactor[$identifier]);
        else
            throw new \InvalidArgumentException(sprintf('The lucene index merge factor "%s" does not exist.', $identifier));
    }
    
     /**
     * Checks if permissions exist for the given lucene identifier
     *
     * @param string $identifier
     * @return boolean TRUE if permissions exist else FALSE
     */
    protected function hasPermissions($identifier)
    {
        self::checkIdentifier($identifier);
        
        return isset($this->permissions[$identifier]);
    }
    
    /**
     * Gets permissions mapped by the given lucene identifier
     *
     * @param string $identifier
     * @return integer 
     */
    protected function getPermissions($identifier)
    {
        if($this->hasPermissions($identifier))
            return $this->permissions[$identifier];
        else
            throw new \InvalidArgumentException(sprintf('The lucene index permissions "%s" does not exist.', $identifier));
    }
    
    /**
     * Adds permissions
     *
     * @param string $identifier
     * @param integer $permissions
     */
    protected function addPermissions($identifier, $permissions)
    {
        self::checkIdentifier($identifier);
        self::checkPermissions($permissions);
        
        $this->permissions[$identifier] = $permissions;
    }
    
    /**
     * Removes permissions
     *
     * @param string $identifier 
     */
    protected function removePermissions($identifier)
    {
        if($this->hasPermissions($identifier))
            unset($this->permissions[$identifier]);
        else
            throw new \InvalidArgumentException(sprintf('The lucene index permissions "%s" does not exist.', $identifier));
    }
    
    /**
     * Checks if the auto optimized flag exists for the given lucene identifier
     *
     * @param string $identifier
     * @return boolean TRUE if the auto optimized flag exists else FALSE
     */
    protected function hasAutoOptimized($identifier)
    {
        self::checkIdentifier($identifier);
        
        return isset($this->autoOptimized[$identifier]);
    }
    
    /**
     * Gets the auto optimized flag mapped by the given lucene identifier
     *
     * @param string $identifier
     * @return boolean 
     */
    protected function getAutoOptimized($identifier)
    {
        if($this->hasAutoOptimized($identifier))
            return $this->autoOptimized[$identifier];
        else
            throw new \InvalidArgumentException(sprintf('The lucene index auto optimized flag "%s" does not exist.', $identifier));
    }
    
    /**
     * Adds an auto optimized flag
     *
     * @param string $identifier
     * @param boolean $autoOptimized
     */
    protected function addAutoOptimized($identifier, $autoOptimized)
    {
        self::checkIdentifier($identifier);
        self::checkAutoOpimized($autoOptimized);
        
        $this->autoOptimized[$identifier] = $autoOptimized;
    }
    
    /**
     * Removes an auto optimized flag
     *
     * @param string $identifier 
     */
    protected function removeAutoOptimized($identifier)
    {
        if($this->hasAutoOptimized($identifier))
            unset($this->autoOptimized[$identifier]);
        else
            throw new \InvalidArgumentException(sprintf('The lucene index auto optimized flag "%s" does not exist.', $identifier));
    }
    
    /**
     * Checks validity of a lucene identifier
     *
     * @param string $identifier 
     */
    protected static function checkIdentifier($identifier)
    {
        if(!is_string($identifier))
            throw new \InvalidArgumentException('The lucene index identifier must be a string value.');
    }
    
    /**
     * Checks validity of a lucene path
     *
     * @param string $path 
     */
    protected static function checkPath($path)
    {
        if(!is_string($path))
            throw new \InvalidArgumentException('The lucene index path must be a string value.');
    }
    
    /**
     * Checks validity of a lucene analyzer
     *
     * @param string $analyzer 
     */
    protected static function checkAnalyzer($analyzer)
    {
        if(!is_string($analyzer))
            throw new \InvalidArgumentException('The lucene index analyzer must be a string value.');
    }
    
    /**
     * Checks validity of a lucene max buffered documents
     *
     * @param integer $maxBufferedDocs 
     */
    protected static function checkMaxBufferedDocs($maxBufferedDocs)
    {
        if(!is_int($maxBufferedDocs))
            throw new \InvalidArgumentException('The lucene index max buffered documents must be an integer value.');
    }
    
    /**
     * Checks validity of a lucene max merge documents
     *
     * @param integer $maxMergeDocs 
     */
    protected static function checkMaxMergeDocs($maxMergeDocs)
    {
        if(!is_int($maxMergeDocs))
            throw new \InvalidArgumentException('The lucene index max merge documents must be an integer value.');
    }
    
    /**
     * Checks validity of a lucene merge factor
     *
     * @param integer $mergeFactor 
     */
    protected static function checkMergeFactor($mergeFactor)
    {
        if(!is_int($mergeFactor))
            throw new \InvalidArgumentException('The lucene index merge factor must be an integer value.');
    }
    
    /**
     * Checks validity of a lucene permissions
     *
     * @param integer $permissions 
     */
    protected static function checkPermissions($permissions)
    {
        if(!is_int($permissions))
            throw new \InvalidArgumentException('The lucene index permissions must be an integer value.');
    }
    
    /**
     * Checks validity of a lucene auto optimized flag
     *
     * @param boolean $autoOptimized 
     */
    protected static function checkAutoOpimized($autoOptimized)
    {
        if(!is_bool($autoOptimized))
            throw new \InvalidArgumentException('The lucene index auto optimized flag must be a boolean value.');
    }
}
