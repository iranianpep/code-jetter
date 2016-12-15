<?php

namespace CodeJetter\core\io;

use CodeJetter\core\utility\StringUtility;

/**
 * Class Output.
 */
class Output
{
    private $origin;
    private $success;
    private $message;
    private $messages;
    private $redirectTo;
    private $data;
    private $htmlClass;

    /**
     * Output constructor.
     *
     * @param null $origin
     */
    public function __construct($origin = null)
    {
        if ($origin !== null) {
            $this->setOrigin($origin);
        }
    }

    /**
     * @param bool $prepareForView
     *
     * @return string
     */
    public function getMessage($prepareForView = true)
    {
        if ($prepareForView === true) {
            return (new StringUtility())->prepareForView($this->message);
        } else {
            return $this->message;
        }
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return array
     */
    public function getMessages($prepareForView = true)
    {
        if ($prepareForView === true) {
            $stringUtility = new StringUtility();
            $messages = $this->messages;

            if (!empty($messages)) {
                foreach ($messages as $key => $message) {
                    $messages[$key] = $stringUtility->prepareForView($message);
                }
            }

            return $messages;
        } else {
            return $this->messages;
        }
    }

    /**
     * @param array $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    /**
     * @param $message
     */
    public function addMessage($message)
    {
        $messages = $this->getMessages();
        $messages[] = $message;
        $this->setMessages($messages);
    }

    /**
     * @return bool
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * @param bool $success
     *
     * @throws \Exception
     */
    public function setSuccess($success)
    {
        if (!is_bool($success)) {
            throw new \Exception('Success must be boolean');
        }

        $this->success = $success;
    }

    /**
     * convert object to array.
     *
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * @return string
     */
    public function toJSON()
    {
        return json_encode($this->toArray());
    }

    /**
     * @return string
     */
    public function getRedirectTo()
    {
        return $this->redirectTo;
    }

    /**
     * @param string $redirectTo
     */
    public function setRedirectTo($redirectTo)
    {
        $this->redirectTo = $redirectTo;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getHtmlClass()
    {
        return $this->htmlClass;
    }

    /**
     * @param string $htmlClass
     */
    public function setHtmlClass($htmlClass)
    {
        $this->htmlClass = $htmlClass;
    }

    /**
     * @return mixed
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param $origin
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;
    }
}
