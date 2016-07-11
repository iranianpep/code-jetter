<?php
    /** @var CodeJetter\core\FormHandler $formHandler */
    $data = $this->getCurrentComponentTemplate()->getData();

    return "<div class='container-fluid'>
        <div class='row vertical-offset-4'>
            <div class='col-md-6 col-md-offset-3'>
                <form role='form' data-url='/contact/new' data-reset-on-success='true'>
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
                                <label for='name' class='control-label'>Name</label>
                                <input type='text' class='form-control' name='name' id='name' placeholder='Name' value='' autocomplete='false'>
                            </div>
                        </div>
                        <div class='col-md-6'>
                            <div class='form-group'>
                                <label for='email' class='control-label'>Email *</label>
                                <input type='text' class='form-control' name='email' id='email' placeholder='Email' value=''>
                            </div>
                        </div>
                    </div>
                    <div class='row'>
                        <div class='col-md-12'>
                            <div class='form-group'>
                                <label for='message' class='control-label'>Message *</label>
                                <textarea type='text' class='form-control' name='message' id='message' placeholder='Enter you message ...'></textarea>
                            </div>
                        </div>
                    </div>
                    <div class='form-group'>
                        {$formHandler->generateAntiCSRFHtml()}
                        <button type='submit' class='btn btn-success'>Send</button>
                    </div>
                </form>
            </div>
        </div>
    </div>";
