<?php

    /** @var CodeJetter\core\View $this */

    $data = $this->getCurrentComponentTemplate()->getData();
    $url = $data['formUrl'];

    /** @var CodeJetter\core\FormHandler $formHandler */

    return "<div class='container-fluid'>
    <div class='row'>
        <div class='col-md-4 col-md-offset-4'>
            <form name='forgotPasswordForm' id='forgotPasswordForm' data-url='{$url}' data-reset-on-success='true'>
                <div class='row'>
                    <div class='col-md-12'>
                        <ul class='bg-info form-description'>
                          <li>{$data['requiredFields']}</li>
                        </ul>
                    </div>
                </div>
                <div class='row'>
                    <div class='col-md-12'>
                        <div class='form-group'>
                            <label for='email'>Email *</label>
                            <input type='text' name='email' id='email' class='form-control' placeholder='Email' data-required='true'>
                        </div>
                    </div>
                </div>
                <div class='form-group'>
                    {$formHandler->generateAntiCSRFHtml('sha1')}
                    <button type='submit' name='forgotPasswordButton' id='forgotPasswordButton' class='btn btn-default'>Send Reset Link</button>
                </div>
            </form>
        </div>
    </div>
</div>";
