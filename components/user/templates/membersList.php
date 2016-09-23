<?php

    use CodeJetter\core\utility\HtmlUtility;
    use CodeJetter\core\utility\StringUtility;
    use TableGenerator\Cell;
    use TableGenerator\HeadCell;
    use TableGenerator\Row;
    use TableGenerator\Head;
    use TableGenerator\Body;
    use TableGenerator\Table;

/** @var CodeJetter\core\FormHandler $formHandler */
/** @var CodeJetter\core\View $this */
$data = $this->getCurrentComponentTemplate()->getData();
$children = $data['children']['result'];
$total = $data['children']['total'];
$searchQuery = $data['searchQuery'];
$searchQueryKey = $data['searchQueryKey'];

/**
 * replace the first element (#) with custom html
 */
$numberHeadCell = $data['listHeaders'][0];
if ($numberHeadCell instanceof HeadCell) {
    $numberHeadCellContent = "<div class='btn-group'>
<a type='button' class='btn btn-primary' onclick=\"checkAll(this, 'input[name=\'selectedChildren[]\']');\"><i class='fa fa-check' aria-hidden='true'></i></a>
<button type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
<span class='caret'></span>
<span class='sr-only'>Toggle Dropdown</span>
</button>
<ul class='dropdown-menu'>
<li>
    <a href='#' data-toggle='modal' data-target='#batchDeleteConfirmationModal' data-callback='getCheckboxesValues' data-callbackArgs=\"input[name='selectedChildren[]']\"><span class='text-danger'>Delete</span></a>
</li>
</ul>
</div>";

    $numberHeadCell->setContent($numberHeadCellContent);
    $data['listHeaders'][0] = $numberHeadCell;
}

$htmlUtility = new HtmlUtility();
$headRow = $htmlUtility->generateHeadRowByListHeaders($data['listHeaders']);
$head = new Head($headRow);

$body = new Body();

if (!empty($children)) {
    $counter = $this->getCurrentComponentTemplate()->getPager()->getCounterStartNumber();
    foreach ($children as $child) {
        /**
         * @var CodeJetter\components\user\models\MemberUser $child
         */
        $childUsername = (new StringUtility())->prepareForView($child->getUsername());
        $childId = $child->getId();
        $childName = $child->getName();
        $childEmail = $child->getEmail();
        $childPhone = $child->getPhone();
        $childStatus = $child->getStatus();

        $cell6Content = "
<div class='btn-group'>
<a type='button' class='btn btn-primary' href='/account/member/{$childId}'>Details</a>
<button type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
<span class='caret'></span>
<span class='sr-only'>Toggle Dropdown</span>
</button>
<ul class='dropdown-menu'>
<li>
    <a href='#' data-toggle='modal' data-target='#deleteConfirmationModal' data-id='{$childId}' data-name='{$childName}' data-email='{$childEmail}'><span class='text-danger'>Delete</span></a>
</li>
</ul>
</div>";

        $checkbox = $htmlUtility->generateCheckbox('selectedChildren[]', $childId);

        $tmpRow = new Row([new Cell($checkbox . ' ' . $counter), new Cell($childUsername), new Cell($childName), new Cell($childEmail), new Cell($childPhone), new Cell($childStatus), new Cell($cell6Content, false)]);
        $tmpRow->addData('id', $childId);
        $body->addRow($tmpRow);

        $counter++;
    }
} else {
    $tmpCell = new Cell('No record.');
    $tmpCell->addColspan(8);
    $body->addRow(new Row([$tmpCell]));
}

$table = new Table();
$table->class = 'table table-hover';
$table->addHead($head);
$table->addBody($body);
$tableHtml = $table->getHtml();

$searchFieldHtml = (new HtmlUtility())->generateSearchField($searchQuery, $searchQueryKey);

$pagerHtml = $this->getCurrentComponentTemplate()->getPager()->getHtml();

// delete member confirmation modal
$deleteModalHtml = $htmlUtility->generateConfirmationModal('deleteConfirmationModal', 'deleteConfirmationModalLabel', $formHandler, 'deleteForm', '/account/delete-child');

// batch delete member confirmation modal
$batchDeleteModalHtml = $htmlUtility->generateConfirmationModal('batchDeleteConfirmationModal', 'batchDeleteConfirmationModalLabel', $formHandler, 'batchDeleteForm', '/account/batch-delete-child');

// new member modal
$newModalHtml = "<div class='modal fade' id='addMemberModal' tabindex='-1' role='dialog' aria-labelledby='addMemberModalLabel'>
<div class='modal-dialog' role='document'>
<div class='modal-content'>
  <div class='modal-header'>
    <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
    <h4 class='modal-title' id='addMemberModalLabel'>New Member</h4>
  </div>
  <form class='addForm' data-url='/account/add-child' data-submitter='global' data-refresh='true'>
      <div class='modal-body'>
            <div class='row'>
                <div class='col-md-12'>
                    <ul class='bg-info form-description'>
                      <li>{$data['requiredFields']}</li>
                      <li>{$data['passwordRequirements']}</li>
                      <li>{$data['usernameRequirements']}</li>
                    </ul>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-6'>
                    <div class='form-group'>
                        <label for='addFormUsername'>Username *</label>
                        <input type='text' class='form-control' name='username' id='addFormUsername' placeholder='Username'>
                      </div>
                      <div class='form-group'>
                        <label for='exampleInputEmail1'>Email *</label>
                        <input type='text' class='form-control' name='email' id='addFormEmail' placeholder='Email'>
                      </div>
                      <div class='form-group'>
                        <label for='exampleInputEmail1'>Name</label>
                        <input type='text' class='form-control' name='name' id='addFormName' placeholder='Name'>
                      </div>
                      <div class='form-group'>
                        <label for='exampleInputEmail1'>Phone</label>
                        <input type='text' class='form-control' name='phone' id='addFormPhone' placeholder='Phone'>
                      </div>
                </div>
                <div class='col-md-6'>
                      <div class='form-group'>
                        <label for='updateFormStatus'>Status</label>
                        <select class='form-control' name='status'>
                            <option value='active'>Active</option>
                            <option value='inactive'>Inactive</option>
                        </select>
                      </div>
                      <div class='form-group'>
                            <label for='password' class='control-label'>Password</label>
                            <input type='password' class='form-control' name='password' id='password'>
                        </div>
                      <div class='form-group'>
                            <label for='passwordConfirmation' class='control-label'>Confirm Password</label>
                            <input type='password' class='form-control' name='passwordConfirmation' id='passwordConfirmation'>
                      </div>
                </div>
            </div>
      </div>
      <div class='modal-footer'>
        <button type='button' class='btn btn-default' data-dismiss='modal'>Cancel</button>
        {$formHandler->generateAntiCSRFHtml()}
        <button type='submit' class='btn btn-success'>Save</button>
      </div>
  </form>
</div>
</div>
</div>";

return "<div class='container-fluid'>
<div class='row vertical-offset-4'>
    <div class='col-md-offset-1 col-md-10'>
        <div class='row'>
            <div class='col-lg-2 col-lg-offset-10'>
                <button type='button' class='btn btn-primary' data-toggle='modal' data-target='#addMemberModal'>New Member</button>
            </div>
        </div>
        {$searchFieldHtml}
        <div class='row'>
            <div class='col-lg-12'>
                {$tableHtml}
            </div>
        </div>
        {$pagerHtml}
    </div>
</div>
</div>
<!-- Modals -->
{$deleteModalHtml}
{$newModalHtml}
{$batchDeleteModalHtml}
<!--/ Modals -->";
