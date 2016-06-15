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

    $messages = $data['messages'];
    $searchQuery = $data['searchQuery'];
    $searchQueryKey = $data['searchQueryKey'];

    /**
     * replace the first element (#) with custom html
     */
    $numberHeadCell = $data['listHeaders'][0];
    if ($numberHeadCell instanceof HeadCell) {
        $numberHeadCellContent = "<div class='btn-group'>
<a type='button' class='btn btn-primary' onclick=\"checkAll(this, 'input[name=\'selectedMessages[]\']');\"><i class='fa fa-check' aria-hidden='true'></i></a>
  <button type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
    <span class='caret'></span>
    <span class='sr-only'>Toggle Dropdown</span>
  </button>
  <ul class='dropdown-menu'>
    <li>
        <a href='#' data-toggle='modal' data-target='#safeBatchDeleteConfirmationModal' data-callback='getCheckboxesValues' data-callbackArgs=\"input[name='selectedMessages[]']\"><span class='text-danger'>Delete Safely</span></a>
    </li>
    <li>
        <a href='#' data-toggle='modal' data-target='#batchDeleteConfirmationModal' data-callback='getCheckboxesValues' data-callbackArgs=\"input[name='selectedMessages[]']\"><span class='text-danger'>Delete Forever</span></a>
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

    if (!empty($messages)) {
        $counter = $this->getCurrentComponentTemplate()->getPager()->getCounterStartNumber();
        foreach ($messages as $message) {
            /**
             * @var CodeJetter\components\contact\models\ContactMessage $message
             */
            $id = $message->getId();
            $name = (new StringUtility())->prepareForView($message->getName());
            $email = (new StringUtility())->prepareForView($message->getEmail());
            $message = (new StringUtility())->prepareForView($message->getMessage());

            $checkbox = $htmlUtility->generateCheckbox('selectedMessages[]', $id);

            $tmpCell1 = new Cell($checkbox . ' ' . $counter);
            $tmpCell2 = new Cell($name);
            $tmpCell3 = new Cell($email);
            $tmpCell4 = new Cell($message);

            $cell6Content = "<div class='btn-group'>
  <button type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
    <span class='caret'></span>
    <span class='sr-only'>Toggle Dropdown</span>
  </button>
  <ul class='dropdown-menu'>
    <li>
        <a href='#' data-toggle='modal' data-target='#safeDeleteConfirmationModal' data-id='{$id}' data-name='{$name}'><span class='text-danger'>Delete Safely</span></a>
    </li>
    <li>
        <a href='#' data-toggle='modal' data-target='#deleteConfirmationModal' data-id='{$id}' data-name='{$name}'><span class='text-danger'>Delete Forever</span></a>
    </li>
  </ul>
</div>";

            $tmpCell5 = new Cell($cell6Content, false);

            $tmpRow = new Row([$tmpCell1, $tmpCell2, $tmpCell3, $tmpCell4, $tmpCell5]);
            $tmpRow->addData('id', $id);
            $body->addRow($tmpRow);

            $counter++;
        }
    } else {
        $tmpCell = new Cell('No record.');
        $tmpCell->addColspan(5);
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
    $deleteConfirmationModalHtml = $htmlUtility->generateConfirmationModal('deleteConfirmationModal', 'deleteConfirmationModalLabel', $formHandler, 'deleteForm', '/admin/contact/delete-message');

    // batch delete confirmation modal
    $batchDeleteConfirmationModalHtml = $htmlUtility->generateConfirmationModal('batchDeleteConfirmationModal', 'batchDeleteConfirmationModalLabel', $formHandler, 'deleteForm', '/admin/contact/batch-delete-message');

    // safe delete group-member confirmation modal
    $safeDeleteModalHtml = $htmlUtility->generateConfirmationModal('safeDeleteConfirmationModal', 'safeDeleteConfirmationModalLabel', $formHandler, 'safeDeleteForm', '/admin/contact/safe-delete-message');

    // safe batch delete group-member confirmation modal
    $safeBatchDeleteModalHtml = $htmlUtility->generateConfirmationModal('safeBatchDeleteConfirmationModal', 'safeBatchDeleteConfirmationModalLabel', $formHandler, 'safeDeleteForm', '/admin/contact/safe-batch-delete-message');

    return "<div class='container-fluid'>
    <div class='row'>
        <div class='col-md-offset-1 col-md-10'>
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
<!--/ Modals -->";
