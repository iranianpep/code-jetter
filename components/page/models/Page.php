<?php

namespace CodeJetter\components\page\models;

use CodeJetter\core\BaseModel;
use CodeJetter\core\Registry;

/**
 * Class Page.
 */
class Page extends BaseModel
{
    private $title;
    private $intro;
    private $category;
    private $metaTags;
    private $accessRole;

    /**
     * Page constructor.
     *
     * @param null $accessRole
     */
    public function __construct($accessRole = null)
    {
        parent::__construct();

        $this->setAccessRole($accessRole);
        $this->initRobotsMetaTag();
    }

    /**
     * @return array
     */
    public function getMetaTags()
    {
        return $this->metaTags;
    }

    /**
     * @param array $metaTags
     */
    public function setMetaTags(array $metaTags)
    {
        $this->metaTags = $metaTags;
    }

    /**
     * @param MetaTag $metaTag
     */
    public function addMetaTag(MetaTag $metaTag)
    {
        $metaTags = $this->getMetaTags();
        $metaTags[$metaTag->getName()] = $metaTag;
        $this->setMetaTags($metaTags);
    }

    /**
     * @param $metaTagName
     *
     * @return mixed
     */
    public function getMetaTagByName($metaTagName)
    {
        $metaTags = $this->getMetaTags();

        if (isset($metaTags[$metaTagName])) {
            return $metaTags[$metaTagName];
        }
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param string $intro
     */
    public function setIntro($intro)
    {
        $this->intro = $intro;
    }

    /**
     * @return string
     */
    public function getIntro()
    {
        return $this->intro;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return string
     */
    public function getAccessRole()
    {
        return $this->accessRole;
    }

    /**
     * @param string $accessRole
     */
    public function setAccessRole($accessRole)
    {
        $this->accessRole = empty($accessRole) ? 'public' : $accessRole;
    }

    /**
     * @throws \Exception
     */
    private function initRobotsMetaTag()
    {
        /**
         * by default based on the access role, specify the robots meta tag
         * this can be overwrite if a robots meta tag is added later.
         */
        $accessRole = $this->getAccessRole();

        // get accessRolesRobot
        $accessRolesRobot = Registry::getConfigClass()->get('accessRolesRobot');

        if (isset($accessRolesRobot[$accessRole])) {
            $metaTag = new MetaTag('robots', $accessRolesRobot[$accessRole]);
            $this->addMetaTag($metaTag);
        }
    }
}
