<?php
/**
 * Created by PhpStorm.
 * User: ehsanabbasi
 * Date: 12/12/15
 * Time: 7:39 AM
 */

namespace CodeJetter\core\utility;

use CodeJetter\core\FormHandler;
use CodeJetter\libs\TableGenerator\HeadCell;
use CodeJetter\libs\TableGenerator\Row;

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

        $multiple = (isset($configs['multiple']) && $configs['multiple'] === true) ? 'multiple' : '';
        $name = ($name !== null) ? "name = '{$name}'" : '';
        $class = isset($configs['class']) ? "class = '{$configs['class']}'" : '';
        $id = isset($configs['id']) ? "id = '{$configs['id']}'" : '';

        if (!isset($configs['ucfirstTitle'])) {
            $configs['ucfirstTitle'] = false;
        }

        $stringUtility = new StringUtility();

        $html = "<select {$name} {$class} {$id} {$multiple}>";
        foreach ($options as $key => $option) {
            // determine checked option(s)
            if (is_array($selected) && in_array($option, $selected)) {
                $selectedOption = 'selected';
            } else {
                $selectedOption = $option == $selected ? 'selected' : '';
            }

            // determine the title
            if (!empty($configs['titleMapper']) && is_array($configs['titleMapper'])
                && array_key_exists($option, $configs['titleMapper'])) {
                $title = $configs['titleMapper'][$option];
            } elseif (isset($configs['titleMapper']) && $configs['titleMapper'] = 'key') {
                $title = $key;
            } else {
                $title = $option;
            }

            $title = $configs['ucfirstTitle'] === true ? ucfirst($title) : $title;

            // If in the future, string should not be converted to its html entities, disable it in the configs
            $title = $stringUtility->prepareForView($title);

            $html .= "<option value='{$option}' {$selectedOption}>{$title}</option>";
        }
        $html .= "</select>";

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

        $stringUtility = new StringUtility();
        foreach ($options as $option) {
            $optionName = isset($name) ? $name : '';
            $checkedOption = ($option == $checked) ? 'checked' : '';

            // determine the title
            $title = $option;
            if (array_key_exists($option, $configs['titleMapper'])) {
                $title = $configs['titleMapper'][$option];
            }

            $title = ($configs['ucfirstTitle'] === true) ? ucfirst($title) : $title;

            $class = isset($configs['class']) ? "class = '{$configs['class']}'" : '';

            // determine disabled
            $disabled = (in_array($option, $configs['disabled'])) ? $disabled = 'disabled' : '';

            $title = ($configs['ucfirstTitle'] === true) ? ucfirst($title) : $title;

            // If in the future, string should not be converted to its html entities, disable it in the configs
            $title = $stringUtility->prepareForView($title);

            $type = 'radio';

            if (isset($configs['inline'])) {
                $html .= "<label {$class}>
<input type='{$type}' {$optionName} value='{$option['value']}' {$checkedOption} {$disabled}> {$title}</label>";
            } else {
                $html .= "<div {$class}><label><input type='{$type}' {$optionName}
value='{$option}' {$checkedOption} {$disabled}> {$title}
</label></div>";
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

        $stringUtility = new StringUtility();
        foreach ($options as $option) {
            $optionName = isset($name) ? $name : '';

            // determine checked option(s)
            if (is_array($checked) && in_array($option, $checked)) {
                $checkedOption = 'checked';
            } else {
                $checkedOption = ($option == $checked) ? 'checked' : '';
            }

            // determine the title
            $title = $option;
            if (array_key_exists($option, $configs['titleMapper'])) {
                $title = $configs['titleMapper'][$option];
            }

            $title = ($configs['ucfirstTitle'] === true) ? ucfirst($title) : $title;

            $class = isset($configs['class']) ? "class = '{$configs['class']}'" : '';

            // determine disabled
            $disabled = (in_array($option, $configs['disabled'])) ? $disabled = 'disabled' : '';

            $title = ($configs['ucfirstTitle'] === true) ? ucfirst($title) : $title;

            // If in the future, string should not be converted to its html entities, disable it in the configs
            $title = $stringUtility->prepareForView($title);

            $type = 'checkbox';

            if (isset($configs['inline'])) {
                $html .= "<label {$class}>
<input type='{$type}' {$optionName} value='{$option['value']}' {$checkedOption} {$disabled}> {$title}</label>";
            } else {
                $html .= "<div {$class}><label>
<input type='{$type}' {$optionName} value='{$option}' {$checkedOption} {$disabled}> {$title}
</label></div>";
            }
        }

        return $html;
    }
    
    public function generateCheckbox($name = null, $value = null, $class = null)
    {
        $nameHtml = $name !== null ? "name='{$name}'" : '';
        $valueHtml = $value !== null ? "value='{$value}'" : '';
        $classHtml = $class !== null ? "class = {$class}" : '';

        return "<input type='checkbox' {$nameHtml} {$valueHtml} {$classHtml}>";
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

        if (!empty($headers)) {
            foreach ($headers as $header) {
                if (!$header instanceof HeadCell) {
                    continue;
                }

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
        $refresh = true) {
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