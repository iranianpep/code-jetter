<?php
/**
 * Created by PhpStorm.
 * User: ehsanabbasi
 * Date: 24/04/15
 * Time: 7:26 PM
 */

namespace CodeJetter\core\layout\blocks;

use CodeJetter\components\page\models\MetaTag;

/**
 * Class Header
 * @package CodeJetter\core\layout\blocks
 */
class Header extends BaseBlock
{
    /**
     * @return string
     */
    public function getAdditionalMetaTagsHtml()
    {
        $metaTags = $this->getPage()->getMetaTags();

        $metaTagsHtml = '';
        if (!empty($metaTags)) {
            foreach ($metaTags as $metaTag) {
                if (!$metaTag instanceof MetaTag) {
                    continue;
                }

                $httpEquiv = $metaTag->getHttpEquiv();
                $charset = $metaTag->getCharset();

                $httpEquiv = empty($httpEquiv) ? '' : "http-equiv='{$metaTag->getHttpEquiv()}'";
                $charset = empty($charset) ? '' : "charset='{$metaTag->getCharset()}'";

                $metaTagsHtml .= "<meta {$httpEquiv} name='{$metaTag->getName()}'
content='{$metaTag->getContent()}' {$charset}>";
            }
        }

        return $metaTagsHtml;
    }
}
