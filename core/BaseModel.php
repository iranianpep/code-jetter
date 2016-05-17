<?php
/**
 * Created by PhpStorm.
 * User: ehsanabbasi
 * Date: 30/04/15
 * Time: 10:02 PM
 */

namespace CodeJetter\core;

use CodeJetter\core\utility\StringUtility;

/**
 * Class BaseModel
 * @package CodeJetter\core
 */
abstract class BaseModel
{
    protected $id;
    protected $createdAt;
    protected $modifiedAt;
    protected $live;
    protected $archivedAt;
    protected $mapperName;

    /**
     * BaseModel constructor.
     */
    public function __construct()
    {
        $this->setMapperName($this->getMapperName());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @throws \Exception
     */
    public function setId($id)
    {
        if (!is_numeric($id) || !($id > 0)) {
            throw new \Exception('Id is not valid');
        }

        $this->id = (int) $id;
    }

    /**
     * @return mixed
     */
    public function getLive()
    {
        return $this->live;
    }

    /**
     * @param $live
     *
     * @throws \Exception
     */
    public function setLive($live)
    {
        if ($live === null || $live == '1') {
            $this->live = $live;
        } else {
            throw new \Exception("Live can be only 1 or null. '{$live}' is passed instead");
        }


    }

    /**
     * @return int|NULL
     */
    public function getArchivedAt()
    {
        return $this->archivedAt;
    }

    /**
     * @param int|NULL $archivedAt
     */
    public function setArchivedAt($archivedAt)
    {
        $this->archivedAt = $archivedAt;
    }

    /**
     * @return int
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param int $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return int
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * @param int $modifiedAt
     */
    public function setModifiedAt($modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;
    }

    /**
     * @param bool $fullPath
     * @param bool $getFresh
     *
     * @return mixed
     */
    public function getMapperName($fullPath = true, $getFresh = false)
    {
        if (empty($this->mapperName) || $getFresh === true) {
            $classNameParts = explode('\\', get_class($this));
            $classNamePartsEnd = end($classNameParts);

            // append Mapper to model name
            $mapperName = $classNamePartsEnd . 'Mapper';

            if ($fullPath === true) {
                // full path
                // remove starting from models, replace the rest with mappers and mapper name
                $pattern = "#models\\\\{$classNamePartsEnd}$#";
                $this->setMapperName(preg_replace($pattern, '', get_class($this)) . 'mappers\\' . $mapperName);
            } else {
                $this->setMapperName($mapperName);
            }
        }

        return $this->mapperName;
    }

    /**
     * @param string $mapperName
     */
    public function setMapperName($mapperName)
    {
        $this->mapperName = $mapperName;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * Extract class name from the namespace
     * @return string
     * @throws \Exception
     */
    public function getClassNameFromNamespace()
    {
        return (new StringUtility())->getClassNameFromNamespace(get_class($this));
    }

    /**
     * @return string
     */
    public function toJSON()
    {
        return json_encode($this->toArray());
    }
}
