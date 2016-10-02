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
    $currentPage = $this->getCurrentComponentTemplate()->getPager()->getCurrentPage();
    $data = $this->getCurrentComponentTemplate()->getData();
    $members = $data['members'];
    $searchQuery = $data['searchQuery'];
    $searchQueryKey = $data['searchQueryKey'];

    /**
     * replace the first element (#) with custom html
     */
    $numberHeadCell = $data['listHeaders'][0];
    if ($numberHeadCell instanceof HeadCell) {
        $numberHeadCellContent = "<div class='btn-group'>
<a type='button' class='btn btn-primary' onclick=\"checkAll(this, 'input[name=\'selectedMembers[]\']');\"><i class='fa fa-check' aria-hidden='true'></i></a>
  <button type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
    <span class='caret'></span>
    <span class='sr-only'>Toggle Dropdown</span>
  </button>
  <ul class='dropdown-menu'>
    <li>
        <a href='#' data-toggle='modal' data-target='#safeBatchDeleteConfirmationModal' data-callback='getCheckboxesValues' data-callbackArgs=\"input[name='selectedMembers[]']\"><span class='text-danger'>Delete Safely</span></a>
    </li>
    <li>
        <a href='#' data-toggle='modal' data-target='#batchDeleteConfirmationModal' data-callback='getCheckboxesValues' data-callbackArgs=\"input[name='selectedMembers[]']\"><span class='text-danger'>Delete Forever</span></a>
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

    if (!empty($members)) {
        $counter = $this->getCurrentComponentTemplate()->getPager()->getCounterStartNumber();
        foreach ($members as $member) {
            /**
             * @var CodeJetter\components\user\models\MemberUser $member
             */
            $stringUtility = new StringUtility();
            $memberUsername = $stringUtility->prepareForView($member->getUsername());
            $memberId = $member->getId();
            $memberName = $stringUtility->prepareForView($member->getName());
            $memberEmail = $stringUtility->prepareForView($member->getEmail());
            $memberPhone = $stringUtility->prepareForView($member->getPhone());
            $memberStatus = $member->getStatus();

            $cell6Content = "
<div class='btn-group'>
  <a type='button' class='btn btn-primary' href='/admin/member/{$memberId}'>Details</a>
  <button type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
    <span class='caret'></span>
    <span class='sr-only'>Toggle Dropdown</span>
  </button>
  <ul class='dropdown-menu'>
    <li>
        <a href='#' data-toggle='modal' data-target='#editModal' data-username='{$memberUsername}' data-id='{$memberId}' data-name='{$memberName}' data-email='{$memberEmail}' data-phone='{$memberPhone}' data-status='{$memberStatus}'>Quick Edit</a>
    </li>
    <li role='separator' class='divider'></li>
    <li>
        <a href='#' data-toggle='modal' data-target='#safeDeleteConfirmationModal' data-id='{$memberId}' data-name='{$memberName}' data-email='{$memberEmail}'><span class='text-danger'>Delete Safely</span></a>
    </li>
    <li>
        <a href='#' data-toggle='modal' data-target='#deleteConfirmationModal' data-id='{$memberId}' data-name='{$memberName}' data-email='{$memberEmail}'><span class='text-danger'>Delete Forever</span></a>
    </li>
  </ul>
</div>";

            $checkbox = $htmlUtility->generateCheckbox('selectedMembers[]', $memberId);

            $tmpRow = new Row([new Cell($checkbox . ' ' . $counter), new Cell($memberUsername), new Cell($memberName), new Cell($memberEmail), new Cell($memberPhone), new Cell($memberStatus), new Cell($cell6Content, false)]);
            $tmpRow->addData('id', $memberId);
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
    $deleteModalHtml = $htmlUtility->generateConfirmationModal('deleteConfirmationModal', 'deleteConfirmationModalLabel', 'Delete', $formHandler, 'deleteForm', '/admin/delete-member');

    // batch delete member confirmation modal
    $batchDeleteModalHtml = $htmlUtility->generateConfirmationModal('batchDeleteConfirmationModal', 'batchDeleteConfirmationModalLabel', 'Delete', $formHandler, 'batchDeleteForm', '/admin/batch-delete-member');

    // safe delete member confirmation modal
    $safeDeleteModalHtml = $htmlUtility->generateConfirmationModal('safeDeleteConfirmationModal', 'safeDeleteConfirmationModalLabel', 'Safe Delete', $formHandler, 'safeDeleteForm', '/admin/safe-delete-member');

    // batch delete member confirmation modal
    $safeBatchDeleteModalHtml = $htmlUtility->generateConfirmationModal('safeBatchDeleteConfirmationModal', 'safeBatchDeleteConfirmationModalLabel', 'Safe Delete', $formHandler, 'batchDeleteForm', '/admin/safe-batch-delete-member');

    // New modal
    $newModalHtml = "<div class='modal fade' id='addModal' tabindex='-1' role='dialog' aria-labelledby='addModalLabel'>
  <div class='modal-dialog' role='document'>
    <div class='modal-content'>
      <div class='modal-header'>
        <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
        <h4 class='modal-title' id='addModalLabel'>New Member</h4>
      </div>
      <form class='addForm' data-url='/admin/add-member' data-submitter='global' data-close-on-success='true' data-refresh='true'>
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
                    <label for='addFormEmail'>Email *</label>
                    <input type='text' class='form-control' name='email' id='addFormEmail' placeholder='Email'>
                  </div>
                  <div class='form-group'>
                    <label for='addFormName'>Name</label>
                    <input type='text' class='form-control' name='name' id='addFormName' placeholder='Name'>
                  </div>
                  <div class='form-group'>
                    <label for='addFormPhone'>Phone</label>
                    <input type='text' class='form-control' name='phone' id='addFormPhone' placeholder='Phone'>
                  </div>
                </div>
                <div class='col-md-6'>
                   <div class='form-group'>
                    <label for='updateFormStatus'>Status</label>
                    <select class='form-control' class='form-control' name='status'>
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

    // update member modal
    $updateHtml = "<div class='modal fade' id='editModal' tabindex='-1' role='dialog' aria-labelledby='editModalLabel'>
  <div class='modal-dialog' role='document'>
    <div class='modal-content'>
      <div class='modal-header'>
        <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
        <h4 class='modal-title' id='editModalLabel'>Edit Member</h4>
      </div>
      <form class='updateForm' data-url='/admin/update-member' data-submitter='global' data-refresh='true'>
          <div class='modal-body'>
            <div class='row'>
                <div class='col-md-12'>
                    <ul class='bg-info form-description'>
                      <li>{$data['requiredFields']}</li>
                      <li>{$data['usernameRequirements']}</li>
                    </ul>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-6'>
                  <div class='form-group'>
                    <label for='updateFormUsername'>Username *</label>
                    <input type='text' class='form-control' name='username' id='updateFormUsername' placeholder='Username'>
                  </div>
                  <div class='form-group'>
                    <label for='updateFormEmail'>Email *</label>
                    <input type='text' class='form-control' name='email' id='updateFormEmail' placeholder='Email'>
                  </div>
                 <div class='form-group'>
                    <label for='updateFormName'>Name</label>
                    <input type='text' class='form-control' name='name' id='updateFormName' placeholder='Name'>
                  </div>
                </div>
                <div class='col-md-6'>
                  <div class='form-group'>
                    <label for='updateFormPhone'>Phone</label>
                    <input type='text' class='form-control' name='phone' id='updateFormPhone' placeholder='Phone'>
                  </div>
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

    return "
<div class='container-fluid'>
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
{$deleteModalHtml}
{$batchDeleteModalHtml}
{$safeDeleteModalHtml}
{$safeBatchDeleteModalHtml}
{$newModalHtml}
{$updateHtml}
<!--/ Modals -->";