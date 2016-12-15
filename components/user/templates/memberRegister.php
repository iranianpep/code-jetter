<?php

    /** @var CodeJetter\core\View $this */

    /** @var CodeJetter\core\FormHandler $formHandler */
    $data = $this->getCurrentComponentTemplate()->getData();

    return "<div class='container-fluid'>
    <div class='row vertical-offset-4'>
        <div class='col-md-6 col-md-offset-3'>
            <form name='register' id='register' data-url='/register'>
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
                            <label for='username'>Username *</label>
                            <input type='text' name='username' id='username' class='form-control' placeholder='Username' data-required='true'>
                        </div>
                        <div class='form-group'>
                            <label for='email'>Email *</label>
                            <input type='text' name='email' id='email' class='form-control' placeholder='Email' data-required='true'>
                        </div>
                    </div>
                    <div class='col-md-6'>
                        <div class='form-group'>
                            <label for='password'>Password *</label>
                            <input type='password' name='password' id='password' class='form-control' placeholder='Password' data-required='true'>
                        </div>
                        <div class='form-group'>
                            <label for='passwordConfirmation'>Password Confirmation *</label>
                            <input type='password' name='passwordConfirmation' id='passwordConfirmation' class='form-control' placeholder='Password Confirmation' data-required='true'>
                        </div>
                    </div>
                </div>
                <div class='form-group'>
                    {$formHandler->generateAntiCSRFHtml('sha1')}
                    <button type='submit' name='registerButton' id='registerButton' class='btn btn-default'>Register</button>
                </div>
            </form>
        </div>
    </div>
</div>";
