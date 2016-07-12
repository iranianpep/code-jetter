<?php

/** @var CodeJetter\core\View $this */

/** @var CodeJetter\core\FormHandler $formHandler */
$data = $this->getCurrentComponentTemplate()->getData();
$url = $data['formUrl'];

if ($data['tokenValid'] !== true) {
    return "Token is expired. Please submit your email <a href='/forgot-password'>here</a> again.";
}

return "<div class='container-fluid'>
    <div class='row vertical-offset-4'>
        <div class='col-md-offset-1 col-md-10'>
            Enter New Password
            <section id='intro' class='container-fluid'>
                <div class='row'>
                    <div class='col-md-6 col-md-offset-3 margin_bottom_div'>
                        <div class='message'></div>
                        <form name='resetPasswordForm' id='resetPasswordForm' data-url='{$url}'>
                            <div class='form-group'>
                                <label for='email'>New Password</label>
                                <input type='password' name='password' id='password' class='form-control' placeholder='Password' data-required='true'>
                            </div>
                            <div class='form-group'>
                                <label for='email'>Confirm Password</label>
                                <input type='password' name='passwordConfirmation' id='passwordConfirmation' class='form-control' placeholder='Confirm Password' data-required='true'>
                            </div>
                            <input type='hidden' name='email' value='{$data['email']}'>
                            <input type='hidden' name='resetPasswordToken' value='{$data['token']}'>
                            {$formHandler->generateAntiCSRFHtml('sha1')}
                            <button type='submit' name='resetPasswordButton' id='resetPasswordButton' class='btn btn-default'>Set New Password</button>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>";
