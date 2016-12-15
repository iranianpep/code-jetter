<?php

namespace CodeJetter\components\page\models;

use CodeJetter\core\BaseModel;

/**
 * Class MetaTag.
 */
class MetaTag extends BaseModel
{
    private $charset;
    private $name;
    private $content;
    private $httpEquiv;

    /**
     * MetaTag constructor.
     *
     * @param $name
     * @param $content
     */
    public function __construct($name, $content)
    {
        parent::__construct();
        $this->setName($name);
        $this->setContent($content);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getHttpEquiv()
    {
        return $this->httpEquiv;
    }

    /**
     * @param string $httpEquiv
     */
    public function setHttpEquiv($httpEquiv)
    {
        $this->httpEquiv = $httpEquiv;
    }
}
