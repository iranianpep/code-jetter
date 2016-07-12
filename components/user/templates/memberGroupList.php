<?php

    use CodeJetter\core\utility\HtmlUtility;
    use CodeJetter\core\utility\StringUtility;
    use CodeJetter\libs\TableGenerator\Cell;
    use CodeJetter\libs\TableGenerator\HeadCell;
    use CodeJetter\libs\TableGenerator\Row;
    use CodeJetter\libs\TableGenerator\Head;
    use CodeJetter\libs\TableGenerator\Body;
    use CodeJetter\libs\TableGenerator\Table;

    /** @var CodeJetter\core\FormHandler $formHandler */
    /** @var CodeJetter\core\View $this */
    $currentPage = $this->getCurrentComponentTemplate()->getPager()->getCurrentPage();

    $data = $this->getCurrentComponentTemplate()->getData();
    $groups = $data['groups'];
    $searchQuery = $data['searchQuery'];
    $searchQueryKey = $data['searchQueryKey'];

    /**
     * replace the first element (#) with custom html
     */
    $numberHeadCell = $data['listHeaders'][0];
    if ($numberHeadCell instanceof HeadCell) {
        $numberHeadCellContent = "<div class='btn-group'>
<a type='button' class='btn btn-primary' onclick=\"checkAll(this, 'input[name=\'selectedGroups[]\']');\"><i class='fa fa-check' aria-hidden='true'></i></a>
  <button type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
    <span class='caret'></span>
    <span class='sr-only'>Toggle Dropdown</span>
  </button>
  <ul class='dropdown-menu'>
    <li>
        <a href='#' data-toggle='modal' data-target='#safeBatchDeleteConfirmationModal' data-callback='getCheckboxesValues' data-callbackArgs=\"input[name='selectedGroups[]']\"><span class='text-danger'>Delete Safely</span></a>
    </li>
    <li>
        <a href='#' data-toggle='modal' data-target='#batchDeleteConfirmationModal' data-callback='getCheckboxesValues' data-callbackArgs=\"input[name='selectedGroups[]']\"><span class='text-danger'>Delete Forever</span></a>
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

    if (!empty($groups)) {
        $counter = $this->getCurrentComponentTemplate()->getPager()->getCounterStartNumber();
        foreach ($groups as $group) {
            /**
             * @var CodeJetter\components\user\models\MemberUser $group
             */
            $groupId = $group->getId();
            $groupName = (new StringUtility())->prepareForView($group->getName());
            $groupStatus = $group->getStatus();

            $checkbox = $htmlUtility->generateCheckbox('selectedGroups[]', $groupId);

            $tmpCellNo = new Cell($checkbox . ' ' . $counter);
            $tmpCell2 = new Cell($groupName);
            $tmpCell5 = new Cell($groupStatus);

            $cell6Content = "<div class='btn-group'>
  <a type='button' class='btn btn-primary' href='#' data-toggle='modal' data-target='#editModal'data-id='{$groupId}' data-name='{$groupName}' data-status='{$groupStatus}'>Edit</a>
  <button type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
    <span class='caret'></span>
    <span class='sr-only'>Toggle Dropdown</span>
  </button>
  <ul class='dropdown-menu'>
    <li>
        <a href='#' data-toggle='modal' data-target='#safeDeleteConfirmationModal' data-id='{$groupId}' data-name='{$groupName}'><span class='text-danger'>Delete Safely</span></a>
    </li>
    <li>
        <a href='#' data-toggle='modal' data-target='#deleteConfirmationModal' data-id='{$groupId}' data-name='{$groupName}'><span class='text-danger'>Delete Forever</span></a>
    </li>
  </ul>
</div>";

            $tmpCell6 = new Cell($cell6Content, false);

            $tmpRow = new Row([$tmpCellNo, $tmpCell2, $tmpCell5, $tmpCell6]);
            $tmpRow->addData('id', $groupId);
            $body->addRow($tmpRow);

            $counter++;
        }
    } else {
        $tmpCell = new Cell('No record.');
        $tmpCell->addColspan(4);
        $body->addRow(new Row([$tmpCell]));
    }

    $table = new Table();
    $table->class = 'table table-hover';
    $table->addHead($head);
    $table->addBody($body);
    $tableHtml = $table->getHtml();

    $searchFieldHtml = (new HtmlUtility())->generateSearchField($searchQuery, $searchQueryKey);

    $pagerHtml = $this->getCurrentComponentTemplate()->getPager()->getHtml();

    // delete confirmation modal
    $deleteConfirmationModalHtml = $htmlUtility->generateConfirmationModal('deleteConfirmationModal', 'deleteConfirmationModalLabel', $formHandler, 'deleteForm', '/admin/delete-group-member');

    // batch delete confirmation modal
    $batchDeleteConfirmationModalHtml = $htmlUtility->generateConfirmationModal('batchDeleteConfirmationModal', 'batchDeleteConfirmationModalLabel', $formHandler, 'deleteForm', '/admin/batch-delete-group-member');

    // safe delete group-member confirmation modal
    $safeDeleteModalHtml = $htmlUtility->generateConfirmationModal('safeDeleteConfirmationModal', 'safeDeleteConfirmationModalLabel', $formHandler, 'safeDeleteForm', '/admin/safe-delete-group-member');

    // safe batch delete group-member confirmation modal
    $safeBatchDeleteModalHtml = $htmlUtility->generateConfirmationModal('safeBatchDeleteConfirmationModal', 'safeBatchDeleteConfirmationModalLabel', $formHandler, 'safeDeleteForm', '/admin/safe-batch-delete-group-member');

    // New modal
    $newModalHtml = "<div class='modal fade' id='addModal' tabindex='-1' role='dialog' aria-labelledby='addModalLabel'>
  <div class='modal-dialog' role='document'>
    <div class='modal-content'>
      <div class='modal-header'>
        <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
        <h4 class='modal-title' id='addModalLabel'>New Group</h4>
      </div>
      <form class='addForm' data-url='/admin/add-group-member' data-submitter='global' data-close-on-success='true' data-refresh='true'>
          <div class='modal-body'>
              <div class='row'>
                <div class='col-md-12'>
                    <ul class='bg-info form-description'>
                        <li>{$data['requiredFields']}</li>
                        <li>{$data['uniqueFields']}</li>
                    </ul>
                </div>
              </div>
              <div class='row'>
                <div class='col-md-6'>
                  <div class='form-group'>
                    <label for='addFormName'>Name *</label>
                    <input type='text' class='form-control' name='name' id='addFormName' placeholder='Name'>
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

    // update member modal
    $updateModalHtml = "<div class='modal fade' id='editModal' tabindex='-1' role='dialog' aria-labelledby='editModalLabel'>
  <div class='modal-dialog' role='document'>
    <div class='modal-content'>
      <div class='modal-header'>
        <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
        <h4 class='modal-title' id='editModalLabel'>Edit Member</h4>
      </div>
      <form class='updateForm' data-url='/admin/update-group-member' data-submitter='global' data-refresh='true'>
          <div class='modal-body'>
              <div class='row'>
                <div class='col-md-12'>
                    <ul class='bg-info form-description'>
                        <li>{$data['requiredFields']}</li>
                        <li>{$data['uniqueFields']}</li>
                    </ul>
                </div>
              </div>
              <div class='row'>
                <div class='col-md-6'>
                  <div class='form-group'>
                    <label for='updateFormName'>Name *</label>
                    <input type='text' class='form-control' name='name' id='updateFormName' placeholder='Name'>
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
                </div>
              </div>
          </div>
          <div class='modal-footer'>
            <button type='button' class='btn btn-default' data-dismiss='modal'>Cancel</button>
            {$formHandler->generateAntiCSRFHtml()}
            <button type='submit' class='btn btn-success'>Update</button>
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
                    <button type='button' class='btn btn-primary' data-toggle='modal' data-target='#addModal'>New</button>
                </div>
            </div>
            {$searchFieldHtml}
            <div class='row'>
                <div class='col-lg-12'>
                    {$tableHtml}
                </div>
            </div>
            <div class='row'>
                <div class='col-lg-12'>
                    {$pagerHtml}
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modals -->
{$deleteConfirmationModalHtml}
{$batchDeleteConfirmationModalHtml}
{$safeDeleteModalHtml}
{$safeBatchDeleteModalHtml}
{$newModalHtml}
{$updateModalHtml}
<!--/ Modals -->";
