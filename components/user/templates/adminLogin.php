<?php

    /** @var CodeJetter\core\FormHandler $formHandler */
    $data = $this->getCurrentComponentTemplate()->getData();

    return "<div class='container-fluid'>
    <div class='row vertical-offset-4'>
        <div class='col-md-6 col-md-offset-3 margin_bottom_div'>
            <form name='login' id='login' data-url='/admin/login'>
                <div class='row'>
                    <div class='col-md-12'>
                        <ul class='bg-info form-description'>
                          <li>{$data['requiredFields']}</li>
                        </ul>
                    </div>
                </div>
                <div class='row'>
                    <div class='col-md-6'>
                        <div class='form-group'>
                            <label for='username'>Username *</label>
                            <input type='text' name='username' id='username' class='form-control' placeholder='Username' data-required='true'>
                        </div>
                    </div>
                    <div class='col-md-6'>
                        <div class='form-group'>
                            <label for='password'>Password *</label>
                            <input type='password' name='password' id='password' class='form-control' placeholder='Password' data-required='true'>
                        </div>
                    </div>
                </div>
                <div class='form-group'>
                    {$formHandler->generateAntiCSRFHtml()}
                    <button type='submit' name='loginButton' id='loginButton' class='btn btn-default'>Login</button>
                </div>
            </form>
            <a href='/admin/forgot-password'>Forgot password?</a>
        </div>
    </div>
</div>";
