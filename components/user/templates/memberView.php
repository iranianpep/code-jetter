<?php

    use \CodeJetter\core\utility\HtmlUtility;
    use \CodeJetter\components\user\models\User;
    use CodeJetter\core\utility\StringUtility;

    /** @var CodeJetter\core\FormHandler $formHandler */
/** @var CodeJetter\core\View $this */
$this->getFooter()->addScriptFile($this->getConfig()->get('URL') . '/scripts/chosen.jquery.min.js');
$this->getHeader()->addStyleFile($this->getConfig()->get('URL') . '/styles/chosen.min.css');
$data = $this->getCurrentComponentTemplate()->getData();
$member = $data['member'];
$updateFormUrl = $data['updateFormUrl'];

if (!$member instanceof User) {
    return 'User Not found';
}

$stringUtility = new StringUtility();

if ($this->getCreatedByClass(false) === 'AdminUserController') {
    $parentHtml = "<div class='form-group'>
        <label for='parentId' class='control-label'>Parent Id:</label>
        <input type='text' class='form-control' name='parentId' id='parentId' placeholder='Parent Id' value='{$stringUtility->prepareForView($member->getParentId())}'>
    </div>";
} else {
    $parentHtml = '';
}

// for the time being only MemberUser has got group
if (isset($data['groups']) && method_exists($member, 'getGroupIds')) {
    $groupsHtml = (new HtmlUtility())->generateDropDownList(
        $data['groups'],
        'groups[]',
        $member->getGroupIds(),
        [
            'class' => 'form-control chosen-select',
            'id' => 'groups',
            'titleMapper' => 'key',
            'multiple' => true,
        ]
    );
}

if (!empty($groupsHtml)) {
    $groupsHtml = "<div class='form-group'>
    <label for='groups' class='control-label'>Groups</label>
        {$groupsHtml}
    </div>";
} else {
    $groupsHtml = '';
}

if (isset($data['statuses'])) {
    $selectedStatus = $member->getStatus();
    $statusesHtml = (new HtmlUtility())->generateDropDownList(
        $data['statuses'],
        'status',
        $selectedStatus,
        [
            'ucfirstTitle' => true,
            'class' => 'form-control',
            'id' => 'status'
        ]
    );

    $statusesHtml = "<div class='form-group'>
    <label for='status' class='control-label'>Status</label>
        {$statusesHtml}
    </div>";
} else {
    $statusesHtml = '';
}

    /**
     * Timezone
     */
    $timeZoneDropdown = (new HtmlUtility())->generateDropDownList($data['timeZoneList'], 'timeZone', $member->getTimeZone(), [
        'class' => 'form-control'
    ]);

return "<div class='container-fluid'>
        <div class='row vertical-offset-4'>
<!-- details -->
<div class='col-md-6 col-md-offset-3'>
    <form role='form' data-url='{$updateFormUrl}' data-submitter='global' data-refresh='true'>
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
            <!-- details 1st column -->
            <div class='col-md-6'>
                <div class='form-group'>
                    <label for='id' class='control-label'>Id: </label>
                    <input type='text' class='form-control' name='id' id='id' placeholder='Id' value='{$stringUtility->prepareForView($member->getId())}' readonly>
                </div>
                <div class='form-group'>
                    <label for='username' class='control-label'>Username *</label>
                    <input type='text' class='form-control' name='username' id='username' placeholder='Username' value='{$stringUtility->prepareForView($member->getUsername())}'>
                </div>
                <div class='form-group'>
                    <label for='email' class='control-label'>Email *</label>
                    <input type='text' class='form-control' name='email' id='email' placeholder='Email' value='{$stringUtility->prepareForView($member->getEmail())}'>
                </div>
                <div class='form-group'>
                    <label for='name' class='control-label'>Name</label>
                    <input type='text' class='form-control' name='name' id='name' placeholder='Name' value='{$stringUtility->prepareForView($member->getName())}' autocomplete='false'>
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
            <!--/ details 1st column -->
            <!-- details 2nd column -->
            <div class='col-md-6'>
                <div class='form-group'>
                    <label for='phone' class='control-label'>Phone</label>
                    <input type='text' class='form-control' name='phone' id='phone' placeholder='Phone' value='{$stringUtility->prepareForView($member->getPhone())}'>
                </div>
                <div class='form-group'>
                    <label for='timezone' class='control-label'>Timezone</label>
                    {$timeZoneDropdown}
                </div>
                {$groupsHtml}
                {$statusesHtml}
                {$parentHtml}
                <div class='form-group'>
                    <label class='control-label'>Token:</label>
                    <label class='control-label'>{$member->getToken()}</label>
                </div>
                <div class='form-group'>
                    <label class='control-label'>Token Generated At:</label>
                    <label class='control-label'>{$member->getTokenGeneratedAt()}</label>
                </div>
                <div class='form-group'>
                    <label class='control-label'>Created At:</label>
                    <label for='id' class='control-label'>{$member->getCreatedAt()}</label>
                </div>
                <div class='form-group'>
                    <label class='control-label'>Modified At:</label>
                    <label for='id' class='control-label'>{$member->getModifiedAt()}</label>
                </div>
            </div>
            <!--/ details 2nd column -->
        </div>
        <div class='form-group'>
            {$formHandler->generateAntiCSRFHtml()}
            <button type='submit' class='btn btn-success'>Update</button>
        </div>
    </form>
</div>
<!--/ details -->
</div>
</div>";
