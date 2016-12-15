<?php
/**
 * Created by PhpStorm.
 * User: ehsanabbasi
 * Date: 22/12/15
 * Time: 8:46 AM.
 */

namespace CodeJetter\core\io;

use CodeJetter\core\Registry;

/**
 * Class Response.
 */
class Response
{
    private $headers = [];
    private $content = '';
    private $statusCode = 200;
    private $statusText;
    private $charset = 'UTF-8';

    /**
     * Response constructor.
     *
     * @param string $content
     * @param int    $status
     * @param array  $headers
     */
    public function __construct($content = '', $status = 200, array $headers = [])
    {
        $this->setContent($content);
        $this->setStatusCode($status);
        $this->setHeaders($headers);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = (int) $statusCode;
    }

    /**
     * @return string
     */
    public function getStatusText()
    {
        return $this->statusText;
    }

    /**
     * @param string $statusText
     */
    public function setStatusText($statusText)
    {
        $this->statusText = $statusText;
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
     * @param null $content
     * @param bool $exit
     */
    public function echoContent($content = null, $exit = true)
    {
        if ((new Request())->isAJAX() === true) {
            $url = Registry::getConfigClass()->get('URL');
            header('Access-Control-Allow-Origin: '.$url);
            header('Access-Control-Allow-Methods: *');
            header('Content-Type: application/json');
        }

        if ($content === null) {
            $content = $this->getContent();
        }

        if ((new Request())->isAJAX() === false) {
            $content .= microtime(true) - (new Request())->getStartTime().' Seconds';
        }

        echo $content;

        if ($exit === true) {
            exit;
        }
    }
}
