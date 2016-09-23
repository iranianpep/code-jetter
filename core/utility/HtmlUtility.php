<?php
/**
 * Created by PhpStorm.
 * User: ehsanabbasi
 * Date: 12/12/15
 * Time: 7:39 AM
 */

namespace CodeJetter\core\utility;

use CodeJetter\core\FormHandler;
use CodeJetter\core\io\Request;
use CodeJetter\core\Registry;
use TableGenerator\HeadCell;
use TableGenerator\Row;
use TableGenerator\Table;

/**
 * Class HtmlUtility
 * @package CodeJetter\core\utility
 */
class HtmlUtility
{
    /**
     * HtmlUtility constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param $options
     * @param null $name
     * @param null $selected
     * @param array $configs
     * Currently configs are: titleMapper, ucfirstTitle, multiple, class, id
     *
     * if titleMapper is not array and set to 'key', each item key in the array is considered as title
     *
     * Example:
     * $dropDown = generateDropDownList($enumValues, ['pending','completed'], [
     * 'titleMapper' => $titleMapper,
     * 'ucfirstTitle' => true,
     * 'multiple' => false,
     * 'class' => 'dropdown',
     * 'id' => 'statuses'
     * ]);
     * @return string
     */
    public function generateDropDownList($options, $name = null, $selected = null, array $configs = [])
    {
        if (empty($options)) {
            return '';
        }

        $multiple = (isset($configs['multiple']) && $configs['multiple'] === true) ? ' multiple' : '';
        $name = ($name !== null) ? " name='{$name}'" : '';
        $class = isset($configs['class']) ? " class='{$configs['class']}'" : '';
        $id = isset($configs['id']) ? " id='{$configs['id']}'" : '';
        $placeholder = isset($configs['placeholder']) ? " data-placeholder='{$configs['placeholder']}'" : '';

        if (!isset($configs['ucfirstTitle'])) {
            $configs['ucfirstTitle'] = false;
        }

        $stringUtility = new StringUtility();

        $html = "<select{$name}{$class}{$id}{$placeholder}{$multiple}>";
        foreach ($options as $key => $option) {
            // determine the value
            $value = (!empty($configs['keyAsValue'])) ? $key : $option;

            // determine checked option(s)
            if (is_array($selected) && in_array($value, $selected)) {
                $selectedOption = ' selected';
            } else {
                $selectedOption = $value == $selected ? ' selected' : '';
            }

            // determine the title
            if (!empty($configs['titleMapper']) && is_array($configs['titleMapper'])
                && array_key_exists($option, $configs['titleMapper'])) {
                $title = $configs['titleMapper'][$option];
            } elseif (isset($configs['titleMapper']) && $configs['titleMapper'] == 'key') {
                $title = $key;
            } else {
                $title = $option;
            }

            $title = isset($configs['ucfirstTitle']) && $configs['ucfirstTitle'] === true
                ? ucfirst($title) : $title;

            // If in the future, string should not be converted to its html entities, disable it in the configs
            $title = $stringUtility->prepareForView($title);

            $html .= "<option value='{$value}'{$selectedOption}>{$title}</option>";
        }
        $html .= '</select>';

        return $html;
    }

    /**
     * @param $options
     * @param null $name
     * @param null $checked
     * @param array $configs
     * Currently configs are: titleMapper, ucfirstTitle, class, disabled, inline
     * Example:
     * $options = ['f', 'm', 'n'];
    $generator = new HtmlUtility();
    $html = $generator->generateRadioButtons($options, 'gender', 'm',
    ['ucfirstTitle' => true, 'class' => 'radio-inline',
    'titleMapper' => ['f' => 'Female'],
    'disabled' => ['f']
    ]);
     * @return string
     */
    public function generateRadioButtons($options, $name = null, $checked = null, array $configs = [])
    {
        $html = '';

        $type = 'radio';
        $name = ($name !== null) ? " name='{$name}'" : '';
        $class = isset($configs['class']) ? " class = '{$configs['class']}'" : '';

        $stringUtility = new StringUtility();
        foreach ($options as $key => $option) {
            // determine the value
            $value = (!empty($configs['keyAsValue'])) ? $key : $option;

            $checkedOption = ($value == $checked) ? ' checked' : '';

            // determine the title
            if (!empty($configs['titleMapper']) && is_array($configs['titleMapper'])
                && array_key_exists($option, $configs['titleMapper'])) {
                $title = $configs['titleMapper'][$option];
            } elseif (isset($configs['titleMapper']) && $configs['titleMapper'] == 'key') {
                $title = $key;
            } else {
                $title = $option;
            }

            $title = isset($configs['ucfirstTitle']) && $configs['ucfirstTitle'] === true ?
                ucfirst($title) : $title;

            // determine disabled
            $disabled = isset($configs['disabled']) && in_array($option, $configs['disabled']) ?
                $disabled = ' disabled' : '';

            // If in the future, string should not be converted to its html entities, disable it in the configs
            $title = $stringUtility->prepareForView($title);

            if (isset($configs['inline'])) {
                $html .= "<label{$class}><input type='{$type}'{$name} value='{$value}'{$checkedOption}{$disabled}> {$title}</label>";
            } else {
                $html .= "<div{$class}><label><input type='{$type}'{$name} value='{$value}'{$checkedOption}{$disabled}> {$title}</label></div>";
            }
        }

        return $html;
    }

    /**
     * @param $options
     * @param null $name
     * @param null $checked
     * @param array $configs
     * Currently configs are: titleMapper, ucfirstTitle, class, disabled, inline
     * Example:
     * $options = ['f', 'm', 'n'];
    $generator = new HtmlUtility();
    $html = $generator->generateCheckboxes($options, 'gender', 'm',
     ['ucfirstTitle' => true, 'class' => 'checkbox-inline',
    'titleMapper' => ['f' => 'Female'],
    'disabled' => ['f']
    ]);
     * @return string
     */
    public function generateCheckboxes($options, $name = null, $checked = null, array $configs = [])
    {
        $html = '';

        $name = ($name !== null) ? " name='{$name}'" : '';
        $type = 'checkbox';

        $class = isset($configs['class']) ? " class = '{$configs['class']}'" : '';

        $stringUtility = new StringUtility();
        foreach ($options as $key => $option) {
            // determine the value
            $value = (!empty($configs['keyAsValue'])) ? $key : $option;

            // determine checked option(s)
            if (is_array($checked) && in_array($value, $checked)) {
                $checkedOption = ' checked';
            } else {
                $checkedOption = ($value == $checked) ? ' checked' : '';
            }

            // determine the title
            if (!empty($configs['titleMapper']) && is_array($configs['titleMapper'])
                && array_key_exists($option, $configs['titleMapper'])) {
                $title = $configs['titleMapper'][$option];
            } elseif (isset($configs['titleMapper']) && $configs['titleMapper'] == 'key') {
                $title = $key;
            } else {
                $title = $option;
            }

            $title = isset($configs['ucfirstTitle']) && $configs['ucfirstTitle'] === true ?
                ucfirst($title) : $title;

            // If in the future, string should not be converted to its html entities, disable it in the configs
            $title = $stringUtility->prepareForView($title);

            // determine disabled
            $disabled = isset($configs['disabled']) && in_array($option, $configs['disabled']) ?
                $disabled = ' disabled' : '';

            if (isset($configs['inline'])) {
                $html .= "<label{$class}><input type='{$type}'{$name} value='{$value}'{$checkedOption}{$disabled}> {$title}</label>";
            } else {
                $html .= "<div{$class}><label><input type='{$type}'{$name} value='{$value}'{$checkedOption}{$disabled}> {$title}</label></div>";
            }
        }

        return $html;
    }

    /**
     * @param null $name
     * @param null $value
     * @param null $class
     *
     * @return string
     */
    public function generateCheckbox($name = null, $value = null, $class = null)
    {
        $nameHtml = $name !== null ? " name='{$name}'" : '';
        $valueHtml = $value !== null ? " value='{$value}'" : '';
        $classHtml = $class !== null ? " class = {$class}" : '';

        return "<input type='checkbox'{$nameHtml}{$valueHtml}{$classHtml}>";
    }

    /**
     * Generate html for search field on top of the list
     *
     * @param $searchQuery
     * @param $searchQueryKey
     *
     * @return string
     */
    public function generateSearchField($searchQuery, $searchQueryKey)
    {
        return "<div class='row'>
    <div class='col-lg-12'>
        <div class='form-inline'>
            <div class='form-group'>
                <input type='text' class='form-control' name='query' id='query'
                placeholder='keyword' value='{$searchQuery}'>
                <input type='button' class='btn btn-primary' name='submitBtn' id='submitBtn'
                value='Search' onclick=\"searchQuery('{$searchQueryKey}', $('#query') . val());\">
                <input type='button' class='btn btn-primary' name='resetBtn' id='resetBtn'
                value='Reset' onclick=\"removeQueryString();\">
            </div>
        </div>
    </div>
</div>";
    }

    /**
     * Given an array of headers generate the Row objects
     *
     * @param array $headers
     *
     * @return Row
     * @throws \Exception
     */
    public function generateHeadRowByListHeaders(array $headers)
    {
        $headRow = new Row();

        /**
         * Set list sorting parameters
         */
        $listConfig = Registry::getConfigClass()->get('list');

        $request = new Request();
        $sortDir = $request->getQueryStringVariables($listConfig['orderDir']);
        $sortBy = $request->getQueryStringVariables($listConfig['orderBy']);

        if (!empty($headers)) {
            foreach ($headers as $header) {
                if (!$header instanceof HeadCell) {
                    continue;
                }

                $header->setListSortByKey($listConfig['orderBy']);
                $header->setListSortBy($sortBy);
                $header->setListSortDirKey($listConfig['orderDir']);
                $header->setListSortDir($sortDir);
                $headRow->addCell($header);
            }
        }

        return $headRow;
    }

    /**
     * @param             $divId
     * @param             $labelId
     * @param FormHandler $formHandler
     * @param             $formClass
     * @param             $formAction
     * @param string      $submitter
     * @param bool        $refresh
     *
     * @return string
     * @throws \Exception
     */
    public function generateConfirmationModal(
        $divId,
        $labelId,
        FormHandler $formHandler,
        $formClass,
        $formAction,
        $submitter = 'global',
        $refresh = true
    ) {
        return "<div class='modal fade' id='{$divId}' tabindex='-1' role='dialog' aria-labelledby='{$labelId}'>
  <div class='modal-dialog modal-sm' role='document'>
    <div class='modal-content'>
      <div class='modal-header'>
        <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
        <span aria-hidden='true'>&times;</span></button>
        <h4 class='modal-title' id='{$labelId}'>Delete Group(s)</h4>
      </div>
      <form class='{$formClass}' data-url='{$formAction}' data-submitter='{$submitter}' data-refresh='{$refresh}'>
      <input type='hidden' class='additional-data'>
          <div class='modal-body'>
            <p>Are you sure?</p>
          </div>
          <div class='modal-footer'>
            <button type='button' class='btn btn-default' data-dismiss='modal'>No</button>
            {$formHandler->generateAntiCSRFHtml()}
            <button type='submit' class='btn btn-warning'>Yes</button>
          </div>
      </form>
    </div>
  </div>
</div>";
    }
}