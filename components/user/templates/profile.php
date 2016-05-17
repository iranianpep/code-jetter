<?php

    use CodeJetter\components\user\models\User;
    use CodeJetter\core\utility\HtmlUtility;
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

    /**
     * Timezone
     */
    $timeZoneDropdown = (new HtmlUtility())->generateDropDownList($data['timeZoneList'], 'timeZone', $member->getTimeZone(), [
        'class' => 'form-control'
    ]);

    $stringUtility = new StringUtility();

    return "<div class='container-fluid'>
        <div class='row'>
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
                <div class='form-group'>
                    <label for='token' class='control-label'>Token:</label>
                    <label for='token' class='control-label'>{$member->getToken()}</label>
                </div>
                <div class='form-group'>
                    <label for='modifiedAt' class='control-label'>Token Generated At:</label>
                    <label class='control-label'>{$member->getTokenGeneratedAt()}</label>
                </div>
                <div class='form-group'>
                    <label for='createdAt' class='control-label'>Created At:</label>
                    <label for='id' class='control-label'>{$member->getCreatedAt()}</label>
                </div>
                <div class='form-group'>
                    <label for='modifiedAt' class='control-label'>Modified At:</label>
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
