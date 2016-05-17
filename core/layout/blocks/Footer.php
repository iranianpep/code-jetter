<?php
/**
 * Created by PhpStorm.
 * User: ehsanabbasi
 * Date: 24/04/15
 * Time: 7:26 PM
 */

namespace CodeJetter\core\layout\blocks;

/**
 * Class Footer
 * @package CodeJetter\core\layout\blocks
 */
class Footer extends BaseBlock
{
    private $copyright;

    /**
     * @return string
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * @param string $copyright
     */
    public function setCopyright($copyright)
    {
        $this->copyright = $copyright;
    }
}
