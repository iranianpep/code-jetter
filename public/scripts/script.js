$(document).ready(function(){
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "preventDuplicates": true,
        "timeOut": 8000
    };

    // set the submitted input or button - for cases when there is more than one submit in a form
    $("form input[type=submit], form button[type=submit]").on('click', function() {
        $("input[type=submit], button[type=submit]", $(this).parents("form")).removeAttr("submitted");
        $(this).attr("submitted", "true");
    });

    $('form').on('submit', function(e){

        // todo replace all the other this with form - be careful about this inside the ajax call
        var form = $(this);

        // if data-submitter has been specified something AND is not global, do not proceed
        if (this.getAttribute('data-submitter') !== null && this.getAttribute('data-submitter') !== 'global') {
            return true;
        }

        e.preventDefault();

        // find the element fired the submit
        var triggeredBy = $("input[type=submit][submitted=true], button[type=submit][submitted=true]");

        if ((triggeredBy).prop('nodeName') === 'BUTTON') {
            $(triggeredBy).attr('disabled', true);
            var buttonTitle = $(triggeredBy).html();
            $(triggeredBy).html('Loading ...');
        }

        var closeModalOnSuccess = this.getAttribute('data-close-on-success');
        var refresh = this.getAttribute('data-refresh');
        var resetForm = this.getAttribute('data-reset-on-success');

        var data = getFormData(this);

        $.ajax({
            type: 'POST',
            data: data,
            dataType: 'json',
            url: this.getAttribute('data-url'),
            headers: {
                Accept: "application/json; charset:utf-8"
            },
            success: function(response) {
                // if redirectTo is specified
                if (response.redirectTo !== undefined && response.redirectTo !== null) {
                    window.location.href = response.redirectTo;
                }

                if (response.success === true) {
                    if (response.message !== undefined) {
                        // convert array to string using join
                        var message = '';
                        if (typeof response.message == 'undefined' || !response.message || response.message.length === 0) {
                            message = response.messages.join('<br>');
                        } else {
                            message = response.message;
                        }

                        sendNotification(message, 'Success', 'success');
                    }

                    // get on complete callback
                    if (closeModalOnSuccess !== null) {
                        hideFormModal(form);
                    }

                    if (refresh === 'true' || refresh === '1') {
                        setTimeout(function(){
                            location.reload();
                        }, 550);
                    } else {
                        if (resetForm === 'true') {
                            // probably wont work if there more than one form in the page
                            $(form)[0].reset();

                            // reset chosen as well
                            $(form).find('.chosen-select').trigger('chosen:updated');
                        }
                    }

                } else {
                    // convert array to string using join
                    var message = '';
                    if (typeof response.message == 'undefined' || !response.message || response.message.length === 0) {
                        message = response.messages.join('<br>');
                    } else {
                        message = response.message;
                    }

                    sendNotification(message, 'Error', 'error');
                }
            },
            error: function(response) {

            },
            complete: function(response) {
                if ((triggeredBy).prop('nodeName') === 'BUTTON') {
                    $(triggeredBy).attr('disabled', false);
                    $(triggeredBy).html(buttonTitle);
                }
            }
        });
    });

    $('.modal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var data = button.data();

        // remove unwanted properties
        //delete(data.target);
        //delete(data.toggle);

        var modal = $(this);
        // find form inside the modal
        var form = $(modal).find('form');

        //if (form.length > 0) {
        //    // add data to form inside the modal
        //    appendDataObjectToForm(data, form);
        //}

        // TODO this needs to be controlled from form itself -> by passing the callback function
        if (data.target === '#notifyModal') {
            modal.find('.modal-title').text('Notify ' + data.name);
        } else if (data.target === '#deleteConfirmationModal') {
            modal.find('.modal-title').text('Delete ' + data.name);
            appendDataObjectToForm(data, form);
        } else if (data.target === '#safeDeleteConfirmationModal') {
            modal.find('.modal-title').text('Safe Delete ' + data.name);
            appendDataObjectToForm(data, form);
        } else if (data.target === '#editModal') {
            modal.find('.modal-title').text('Edit ' + data.name);
            appendDataObjectToForm(data, form, ['id']);
            populateFormWithDataObject(data, form);
        } else {
            appendDataObjectToForm(data, form);
        }

    });

    // TODO this needs to have another approach - maybe load it in the page uses this
    if ($(".chosen-select").length > 0) {
        $(".chosen-select").chosen();
    }

    // display sub-menu of responsive menu
    $(".zetta-menu").on('click', function(e){
        $(e.target).parent('li').toggleClass('zm-opened');
    });

    // initialize tooltip - the rest is handled by bootstrap
    $('[data-toggle="tooltip"]').tooltip();
});

function redirectToPage(basePath, page, limit, queryString)
{
    if (typeof basePath == 'undefined' || !basePath || basePath.length === 0) {
        basePath = window.location.pathname;
    }

    window.location.href = basePath + '/page/' + page + '/limit/' + limit + queryString;
}

function appendDataObjectToForm(data, form, whitelist)
{
    if (typeof data != "undefined" && typeof form != "undefined") {

        var foundDiv = $(form).find('.appended-data');
        if (foundDiv.length == 0) {
            // appended-data div does NOT exist, add it
            $(form).append("<div class='appended-data'></div>");
        } else {
            // appended-data div exists, reset its content
            $('div.appended-data').html('');
        }

        // for each variable / property in data object
        for (var name in data) {
            // if data is callbackargs skip it - it is used when name is: callback
            if (name == 'callbackargs') {
                continue;
            }

            // if whitelist is defined, check property is in it
            if (typeof whitelist != "undefined" && whitelist.indexOf(name) == -1) {
                // property is not in whitelist, do not append it to the form
                continue;
            }

            var found = $(form).find("input[type='hidden'][name='"+ name +"']");

            if (name == 'callback') {
                var dataValue = executeWindowFunctionByName(data.callback, data.callbackargs);
            } else {
                var dataValue = data[name];
            }

            if (found.length > 0) {
                // input already exists, update it
                $(found).val(dataValue);
            } else {
                var input = $("<input>").attr("type", "hidden").attr("name", name).val(dataValue);
                $(form).find('.appended-data').append($(input));
            }
        }
    } else {
        alert('Data or form is undefined. Contact admin.')
    }
}

function populateFormWithDataObject(data, form)
{
    if (typeof data != "undefined" && typeof form != "undefined") {
        // for each variable / property in data object
        for (var name in data) {
            var found = $(form).find("[name='"+ name +"']");

            if (found.length > 0) {
                // input already exists, update it
                $(found).val(data[name]);
            }
        }
    } else {
        alert('Data or form is undefined. Contact admin.')
    }
}

function getFormData(form)
{
    var data = $(form).serializeArray();

    // Submitted input does not include in serializeArray() - Also only pick the submitted input
    var submitted = $(form).find("input[type=submit][submitted=true], button[type=submit][submitted=true]");
    data.push({name: $(submitted).attr('name'), value: $(submitted).attr('value')});

    return data;
}

function getFormModal(form)
{
    return $(form).parents('.modal');
}

function hideFormModal(form)
{
    var modal = getFormModal(form);
    $(modal).modal('hide');
}

function sendNotification(message, title, type)
{
    var toastrRef = '';

    switch (type) {
        case 'success':
            toastrRef = toastr.success(message, title);
            break;
        case 'error':
            toastrRef = toastr.error(message, title);
            break;
        case 'warning':
            toastrRef = toastr.warning(message, title);
            break;
        case 'info':
            toastrRef = toastr.info(message, title);
            break;
    }

    return toastrRef;
}

function removeNotification(sessionNotificationRef)
{
    if (sessionNotificationRef !== undefined) {
        sessionNotificationRef.clear();
    } else {
        toastr.clear();
    }
}

function searchQuery(key, value)
{
    var uri = window.location.search;
    var queryString = updateQueryStringParameter(uri, key, value);
    window.location.href = window.location.pathname + queryString;
}

function sort(orderByKey, orderByValue, orderDirKey, orderDirValue)
{
    var uri = window.location.search;
    var redirectTo = updateQueryStringParameter(uri, orderByKey, orderByValue);
    var sortDirQuery = updateQueryStringParameter(redirectTo, orderDirKey, orderDirValue);

    window.location.href = window.location.pathname + sortDirQuery;
}

function getQueryStringParameter(key)
{
    var query = window.location.search.substring(1);
    var vars = query.split("&");

    for (var i=0;i<vars.length;i++) {
        var pair = vars[i].split("=");
        if(pair[0] == key){return pair[1];}
    }

    return(false);
}

function updateQueryStringParameter(uri, key, value)
{
    var re = new RegExp("([?|&])" + key + "=.*?(&|#|$)", "i");

    if (uri.match(re)) {
        return uri.replace(re, '$1' + key + "=" + value + '$2');
    } else {
        var hash =  '';

        if (uri.indexOf('#') !== -1) {
            hash = uri.replace(/.*#/, '#');
            uri = uri.replace(/#.*/, '');
        }

        var separator = uri.indexOf('?') !== -1 ? "&" : "?";

        return uri + separator + key + "=" + value + hash;
    }
}

function removeQueryString()
{
    window.location.href = window.location.pathname;
}

function checkSessionTimeout(model)
{
    var sessionTimeout = globalConfig.sessionTimeout;
    var userIdleTime = getUserIdleTime(model);

    if (userIdleTime > sessionTimeout) {
        console.log('session expired');
        // session expired
        window.location.reload(false);
    } else if (userIdleTime > sessionTimeout - globalConfig.notifySessionTimeout) {
        // session is about to expire
        sendNotification('Your session will be expired in ' + globalConfig.notifySessionTimeout + ' seconds. Please refresh the page to renew the session', 'Warning', 'warning');
    } else {
        // all good
    }
}

/**
 * Return user idle time in second
 *
 * @returns {*}
 */
function getUserIdleTime(model)
{
    if (typeof localStorage.loggedIn == 'undefined') {
        return false;
    }

    var loggedIn = JSON.parse(localStorage.loggedIn);
    var domLoading = loggedIn[model];

    //var domLoading = localStorage.getItem('domLoading');

    if (typeof domLoading != 'undefined') {
        return (Date.now() - domLoading) / 1000;
    } else {
        return false;
    }
}

/**
 * Display responsive menu
 */
function showHideMenu()
{
    $(".navbar-menu").toggleClass('responsive-hidden');
}

function checkAllByCheckbox(checkbox, selector)
{
    $(selector).prop('checked', $(checkbox).prop('checked'));
}

function checkAll(triggeredBy, selector)
{
    $(triggeredBy).attr('data-checked', ($(triggeredBy).attr('data-checked') == 'checked' ? 'unchecked' : 'checked'))

    if ($(triggeredBy).attr('data-checked') == 'checked') {
        $(selector).prop('checked', true);
    } else {
        $(selector).prop('checked', false);
    }
}

function getCheckboxesValues(selector)
{
    return $(selector + ':checked').map(function() {
        return this.value;
    }).get();
}

function executeWindowFunctionByName(functionName, args) {
    return window[functionName](args);
}