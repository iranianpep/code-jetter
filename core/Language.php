<?php

namespace CodeJetter\core;

/**
 * Class Language.
 */
class Language
{
    private $fileDir;
    private $fileContentArray;
    private $currentLanguage;

    /**
     * Language constructor.
     */
    public function __construct()
    {
        $config = Registry::getConfigClass();
        $language = $config->get('defaultLanguage');
        $directorySeparator = $config->get('DS');

        $this->fileDir = $config->get('URI').'core'.$directorySeparator.'language'.$directorySeparator;

        $this->setCurrentLanguage($language);
    }

    /**
     * @return string
     */
    public function getFileFullPath()
    {
        return $this->fileDir.$this->getCurrentLanguage().'.json';
    }

    /**
     * @throws \Exception
     */
    private function loadFile()
    {
        // get file full path
        $fileFullPath = $this->getFileFullPath();

        if (!file_exists($fileFullPath)) {
            throw new \Exception("Language file: '{$fileFullPath}' does not exist");
        }

        // If there are many languages this array might get big. If this is the case unset other languages
        $this->fileContentArray[$this->getCurrentLanguage()] = json_decode(file_get_contents($fileFullPath), true);
    }

    /**
     * @param       $key
     * @param array $replacements
     *
     * @return mixed|string
     */
    public function get($key, $replacements = [])
    {
        if (!$this->has($key)) {
            return '';
        }

        $all = $this->getAll();
        $found = $all[$this->getCurrentLanguage()][$key];

        if (empty($replacements)) {
            return $found;
        }

        foreach ($replacements as $key => $value) {
            $found = str_replace('{'.$key.'}', $value, $found);
        }

        return $found;
    }

    public function has($key)
    {
        $all = $this->getAll();

        return array_key_exists($key, $all[$this->getCurrentLanguage()]);
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    public function getAll()
    {
        // make sure that the language file is loaded once
        if (!isset($this->fileContentArray[$this->getCurrentLanguage()])) {
            $this->loadFile();
        }

        return $this->fileContentArray;
    }

    /**
     * @return string
     */
    public function getCurrentLanguage()
    {
        return $this->currentLanguage;
    }

    /**
     * @param string $currentLanguage
     */
    public function setCurrentLanguage($currentLanguage)
    {
        $this->currentLanguage = $currentLanguage;
    }
}
