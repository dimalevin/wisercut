import {User, Company, Ajax, Advice, AdviceTechnicalDetails, Question, QuestionTechnicalDetails, Response} from './objects.js';
var ajaxReceivedContent = null;
$(document).ready(function () {
    
    // Menu navigation event
    $('.nav-link').click(function (e) {
        e.preventDefault();
        $('.main-menu .nav-link').removeClass('active');
        $('.main-menu .nav-item').removeClass('active');
        $(this).addClass('active');
        $(this).parent('.nav-item').addClass('active');
        var pageIndex = $(this).attr('data-index').toLowerCase();
        if (pageIndex == 0 && !$('.main-menu').hasClass('inner-user')) {
            location.reload();
        }
        if (pageIndex !== 'logout') {
            let request = new Ajax('index.php', 'POST', '', 'getPage', pageIndex);
            ajaxCall(request, null, true);
            getPageCookies(pageIndex);
        } else {
            sessionStorage.removeItem('page');
            let request = new Ajax('index.php', 'POST', '', 'logout', pageIndex);
            ajaxCall(request, true);
        }
    });
    
    // User registration
    $('#main-content').on('click', '#register', function () {
        $('#username').removeClass('is-invalid');
        let usertype = $('#usertype').val().toLowerCase();
        let inputs;
        switch (usertype) {
            case 'asking':
                inputs = ['#firstname', '#lastname'];
                break;
            case 'company':
                inputs = ['#firstname', '#lastname', '#company', '#description', '.form-check-input:first'];
                break;
            case 'advising':
                inputs = ['#firstname', '#lastname'];
                break;
        }
        inputs.push('#email', '#username', '#pwd');
        if (ValidateInputs(inputs)) {
            let specialties = $('input:checkbox[name="specialties"]:checked').map(function () {
                return $(this).val();
            });
            let user = new User(
                    capitalizeFirst($('#firstname').val()),
                    capitalizeFirst($('#lastname').val()),
                    $('#email').val(),
                    $('#pwd').val(),
                    $('#username').val(),
                    usertype);
            let company = new Company(
                    $('#company').val(),
                    capitalizeFirst($('#description').val()),
                    jQuery.makeArray(specialties).join(' '));
            sessionStorage.removeItem('page');
            let request = new Ajax('index.php', 'POST', '', 'register', {user: user, company: company});
            return ajaxCall(request, true).then(function (isRegistered) {
                if (!isRegistered) {
                    $('#username').val('');
                    $('#pwd').val('');
                    $('#email').val('');
                    $('#pwd_confirm').val('');
                    $('#username').addClass('is-invalid');
                    $('#pwd').addClass('is-invalid');
                    $('#email').addClass('is-invalid');
                    $('#pwd_confirm').addClass('is-invalid');

                }
            });
        }
    });
    
    // User login into the system
    $('#main-content').on('click', '#login-user', function () {
        if (ValidateInputs(['#user', '#pwd'])) {
            let username = $('#user').val().toLowerCase();
            let password = $('#pwd').val();
            sessionStorage.removeItem('page');
            let request = new Ajax('index.php', 'POST', '', 'login', {username: username, password: password});
            return ajaxCall(request, true);
        }
    });
    
    // Changing user type on registration page
    $('#main-content').on('change', '#usertype', function () {
        let usertype = this.value.toLowerCase();
        switch (usertype) {
            case 'asking':
            case 'advising':
                $('.single-user').removeClass('d-none');
                $('.company').addClass('d-none');
                $('#section1').addClass('show');
                break;
            case 'company':
                $('.company').removeClass('d-none');
                $('#section1').removeClass('show');
                break;
        }
    });
    
    // Open forgot password modal
    $('#main-content').on('click', '#forgot-psw', function () {
        $('#modal__recover-psw').modal();
    });
    
    // Send recovery password
    $('#main-content').on('click', '#send-psw', function () {
        if (ValidateInputs(['#modal__username', '#modal__email'])) {
            let username = $('#modal__username').val().toLowerCase();
            let email = $('#modal__email').val();
            let request = new Ajax('index.php', 'POST', 'json', 'recoverPassword', {username: username, email: email});
            $('#modal__recover-psw').modal('hide');
            ajaxCall(request);
        }
    });
    
    // Applying css styles on menu navigation
    $('.nav-link').each(function () {
        var pageIndex = $(this).attr('data-index').toLowerCase();
        if (pageIndex === 'logout') {
            $('.main-menu').addClass('inner-user');
            $('.nav-link').removeClass('hvr-grow-shadow');
            $('.nav-item').addClass('hvr-curl-top-left');
        }
    });
    
    // Making first item active on home page
    $('.main-menu li:eq(0), .main-menu li a:eq(0)').addClass('active');
    
    // Contact us page
    $('#main-content').on('click', '#send-contact', function (e) {
        e.preventDefault();
        var name = $('.validate-input input[name="name"]');
        var email = $('.validate-input input[name="email"]');
        var subject = $('.validate-input input[name="subject"]');
        var message = $('.validate-input textarea[name="message"]');
        var check = true;
        if ($(name).val().trim() === '') {
            showValidate(name);
            check = false;
        }
        if ($(subject).val().trim() === '') {
            showValidate(subject);
            check = false;
        }
        if ($(email).val().trim().match(/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{1,5}|[0-9]{1,3})(\]?)$/) == null) {
            showValidate(email);
            check = false;
        }
        if ($(message).val().trim() === '') {
            showValidate(message);
            check = false;
        }
        if (check) {
            $('.validate-form .input1').each(function () {
                $(this).focus(function () {
                    hideValidate(this);
                });
            });
            $('.contact1-pic').addClass('bounceOutLeft');
            let request = new Ajax('index.php', 'POST', '', 'sendContactUsForm', {name: name.val(), email: email.val(), subject: subject.val(), message: message.val()});
            ajaxCall(request);
            $('input,textarea').val('');
            setTimeout(function () {
                $('.contact1-pic').removeClass('bounceOutLeft');
                $('.contact1-pic').addClass('bounceInLeft');
            }, 2000);
        }
    });
    
    loadFirstPageData();    //TODO1 -why it's here?
    
    // Emails page company/admin user
    $(document).on('click', '.tabs li a', function (e) {
        e.preventDefault();
        $('.tabs li a').removeClass('active');
        $(this).addClass('active');
        var item = $(this).attr('data-table');
        $('#messages').find('table').hide();
        $('#messages').find('.fixed-table-toolbar').hide();
        $('#messages').find('.fixed-table-pagination').hide();
        $('#messages').find('.send-message').hide();
        $(item).fadeIn(200);
        if (item !== '#contacts') {
            $(item).closest('.bootstrap-table').children('.fixed-table-toolbar').first().fadeIn(200);
            $(item).closest('.bootstrap-table').children('.fixed-table-pagination').first().fadeIn(200);
        } else {
            $('.send-message').fadeIn(200);
            var request = new Ajax('index.php', 'POST', '', 'getData', {data_name: 'recipients'});
            return ajaxCall(request).then(function (hasData) {});   //TODO1 -?purpose of the func.?
        }
    });
    
    // Events on tables on user/company/admin user
    $(document).on('click', '.unblock-user,.block-user,.unblock-company,.block-company,.approve-company,.hide-question, .unhide-question,.tech-data,.advice-data,.send-new-message,.advice-detail,.getComments,.rating, .getResponse,.fa-unlock-alt', function () {
        let action = null;
        let change = true;
        let el = $(this);
        let request = null;
        let id = $(this).attr('data-userid');
        if ($(this).hasClass('block-user')) {
            action = 'blockUser';
            request = new Ajax('index.php', 'POST', '', action, {user_id: id});
        } else if ($(this).hasClass('unblock-user')) {
            action = 'unblockUser';
            request = new Ajax('index.php', 'POST', '', action, {user_id: id});
        } else if ($(this).hasClass('unblock-company')) {
            action = 'unblockCompany';
            request = new Ajax('index.php', 'POST', '', action, {company_id: id});
        } else if ($(this).hasClass('block-company')) {
            action = 'blockCompany';
            request = new Ajax('index.php', 'POST', '', action, {company_id: id});
        } else if ($(this).hasClass('approve-company')) {
            action = 'approveCompany';
            request = new Ajax('index.php', 'POST', '', action, {company_id: id});
        } else if ($(this).hasClass('hide-question')) {
            action = 'hideQuestion';
            request = new Ajax('index.php', 'POST', '', action, {question_id: id});
        } else if ($(this).hasClass('unhide-question')) {
            action = 'unhideQuestion';
            request = new Ajax('index.php', 'POST', '', action, {question_id: id});
        } else if ($(this).hasClass('tech-data')) {
            action = 'getData';
            change = false;
            request = new Ajax('index.php', 'POST', '', action, {question_id: id, data_name: 'questionDetails'});
            ajaxCall(request);
        } else if ($(this).hasClass('advice-data')) {
            action = 'getData';
            change = false;

            if ($('#questions-asking').length > 0) {
                $('#questions-asking').attr('question-id', id);
            }
            if ($('#questions-admin').length > 0) {
                $('#questions-admin').attr('question-id', id);
            }
            if (('#chat-modal').length > 0) {
                $('#chat-modal').attr('question-id', id);
            }
            $('#advice-table').attr('question-id', id);
            request = new Ajax('index.php', 'POST', '', action, {question_id: id, data_name: 'questionAdvices'});
            return ajaxCall(request).then(function (isAdvices) {
                if (isAdvices) {
                    if (el.hasClass('advice-data')) {
                        $('.detail-view').remove();
                        el.closest('tr').children('td').first().children()[0].click();
                        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                            var array = [];
                            array.push(window.ajaxReceivedContent.advices || window.ajaxReceivedContent);
                            if (!window.ajaxReceivedContent.advices) {
                                array = window.ajaxReceivedContent;
                            }
                            $('#advice-table').bootstrapTable({
                                data: array ? array : [],
                                exportTypes: ['excel', 'pdf'],
                                exportDataType: 'all'
                            });
                            if ($('#adviceHtml').hasClass('d-none')) {
                                $('#adviceHtml').removeClass('d-none');
                                $('#adviceHtml').addClass('d-block d-sm-none');
                            } else if ($('#adviceHtml').hasClass('d-sm-none')) {
                                $('#adviceHtml').removeClass('d-block d-sm-none');
                                $('#adviceHtml').addClass('d-none');
                            }
                        }

                    }
                }
            });
        } else if ($(this).hasClass('advice-detail')) {
            action = 'getData';
            change = false;
            request = new Ajax('index.php', 'POST', '', action, {advice_id: id, data_name: 'adviceDetails'});
            return ajaxCall(request).then(function (isAdvices) {
                if (isAdvices) {
                    let data = ajaxReceivedContent;
                    if (data) {
                        $('#advicesModal input,#advicesModal textarea').each(function () {
                            if (data[$(this).attr('name')] && data[$(this).attr('name')].length > 0) {
                                $(this).val(data[$(this).attr('name')]);
                            } else {
                                $(this).hide();
                                $(this).prev().hide();
                            }
                        });
                        $('#advicesModal').modal('show');

                    }
                }
            });
        } else if ($(this).hasClass('getComments')) {
            let questionId = $('#questions-asking,#questions-admin').attr('question-id');
            $('#chat-modal').attr('advice-id', id);
            change = false;
            request = new Ajax('index.php', 'POST', '', 'getData', {advice_id: id, question_id: questionId, data_name: 'questionAdviceComments'});
            return ajaxCall(request).then(function (isComments) {
                if (isComments) {
                    $('#chat-modal').modal('show');
                    var height = $('#chat-modal').height();
                    setTimeout(function () {
                        $('#chat-modal .modal-body').scrollTop(height);
                    }, 300);

                }
            });
        } else if ($(this).hasClass('rating')) {
            let ques_id=$('#questions-asking').attr('question-id') || $('#questions-admin').attr('question-id');
            $('#send-response').attr('question-id', ques_id);
            $('#send-response').attr('advice-id', $(this).attr('data-userid'));
            $('#send-response').attr('data-advice-userid', $(this).attr('data-advice-userid'));
            change = false;
            $('#add-response').modal('show');
            $('#txt-input-counter2').html($('#add-response #description').val().length + '/200');
        } else if ($(this).hasClass('getResponse')) {
            change = false;
            request = new Ajax('index.php', 'POST', '', 'getData', {advice_id: id, question_id: $('#chat-modal').attr('question-id'), data_name: 'responseDetails'});
            return ajaxCall(request).then(function (isComments) {
                if (isComments) {
                    if (ajaxReceivedContent) {
                        if ($('#questions-asking').length > 0) {
                            $('#add-response #score,#add-response #description,#add-response #title').addClass('disabled');
                            $('#add-response .toggle').css('pointer-events', 'none');
                            $('.tip').hide();
                            $('#send-response').hide();
                            $('label[for="best-advice"]').css('pointer-events', 'none');
                        }

                        $('#add-response #score').val(ajaxReceivedContent.score);
                        $('#add-response #description').val(ajaxReceivedContent.description);
                        $('#add-response #title').val(ajaxReceivedContent.title);
                        $('#add-response #best-advice').prop('checked', ajaxReceivedContent.is_best_advice);
                        $('#add-response').modal('show');
                    }
                }
            });
        } else if ($(this).hasClass('fa-unlock-alt')) {
            let request = new Ajax('index.php', 'POST', '', 'closeQuestion', {question_id: id});
            return ajaxCall(request).then(function (isChanged) {
                if (isChanged) {
                    el.removeClass('fa-unlock-alt');
                    el.addClass('fa-lock');
                }
            });
        }
        if (id && action && request && change) {
            return ajaxCall(request).then(function (isChanged) {
                if (isChanged) {
                    let pageIndex = getActiveMenuItem();
                    let request = new Ajax('index.php', 'POST', '', 'getPage', pageIndex);
                    ajaxCall(request);

                }
            });
        } else if ($(this).hasClass('send-new-message')) {
            let body = $('#body').val();
            let title = $('#title').val();
            if (ValidateInputs(['#body', '#title', '#contacts'])) {
                let toSend = $('#contacts').val();
                var message_details = {receiver_ids: toSend, title: title, body: body};
                let request = new Ajax('index.php', 'POST', '', 'sendMessage', {message_details});
                return ajaxCall(request).then(function (isSent) {
                    if (isSent) {
                        $('#body').val('');
                        $('#title').val('');
                        var sel = $('#contacts').selectize();
                        (sel[0].selectize).clear();
                        let pageIndex = getActiveMenuItem();
                        let request = new Ajax('index.php', 'POST', '', 'getPage', pageIndex);
                        return ajaxCall(request).then(function (isSent) {
                            $('.tabs li .new-mes').trigger('click');
                        });
                    }
                });
            }
        } else {
            if (change) {
                showAlert('error', 'Oops...', 'Network Error. <br> Please try later...');
            }
        }
    });
    
    // Logos upload company user
    $(document).on('click', '.browse,.browse-manager,.browse-company', function () {
        if ($(this).hasClass('browse-manager')) {
            $('#file').trigger('click');
        } else if ($(this).hasClass('browse-company')) {
            $('#company-logo').trigger('click');
        } else {
            var file = $(this).parents().find('.file').first();
            file.trigger('click');
        }
    });
    
    // Unblock user function
    $(document).on('click', '#send-request', function () {
        let username = $('.user-company-unblock').attr('data-userID');
        if ($('#reason').val().length > 0) {
            let reason = $('#reason').val();
            let request = new Ajax('index.php', 'POST', '', 'unblockUserRequest', {username: username, info: reason});
            return ajaxCall(request).then(function (isChanged) {
                if (isChanged) {
                    $('#request-unblock').modal('hide');
                }
            });
        }
    });
    
    // Accept the advice suggections
    $(document).on('click', '#accept', function () {
        var el = $(this);
        let question_id = $('#advice-content .carousel-inner').find('.active').attr('data-question');
        let id = $('#advice-content .carousel-inner').find('.active').attr('id');
        let request = new Ajax('index.php', 'POST', '', 'addSuggestedAdvice', {question_id: question_id, advice_id: id});
        return ajaxCall(request).then(function (isAccepted) {
            if (isAccepted) {
                el.addClass('disabled');
                el.text('Accepted');
                el.find('i').removeClass('d-none');


            }

        });
    });
    
    // Add new company user
    $(document).on('click', '#add-user-company', function () {
        let mail = $('#usermail').val();
        if (ValidateInputs(['#usermail'])) {
            let request = new Ajax('index.php', 'POST', '', 'addUser', {email: mail});
            return ajaxCall(request).then(function (isAdded) {
                if (isAdded) {
                    $('#user-company').modal('hide');
                }

            });
        }
    });
    
    // Add user response regarding to advice
    $(document).on('click', '#send-response', function () {
        let title = $('#add-response #title').val();
        let description = $('#add-response #description').val();
        let score = $('#add-response #score').val();
        let bestAdvice = $('#best-advice').prop('checked');
        let id = $('#send-response').attr('question-id');
        let advising_user_id = $('#send-response').attr('data-advice-userid');
        if (title && description && score) {
            let response = new Response();
            response.description = description;
            response.title = title;
            response.is_best_advice = bestAdvice;
            response.score = score;
            response.question_id = id;
            response.advice_id = $('#send-response').attr('advice-id');
            let request = new Ajax('index.php', 'POST', '', 'giveResponse', {advising_user_id: advising_user_id, response: response});
            return ajaxCall(request).then(function (isAccepted) {
                if (isAccepted) {
                    $('#cancel').click();
                    $('#add-response').modal('hide');
                }

            });
        }

    });
    
    // Chat modal - sending comments
    $(document).on('click', '#send-comment', function () {
        let questionId = $('#chat-modal').attr('question-id');
        let id = $('#chat-modal').attr('advice-id');
        let text = $('#chat-text').val();
        if (text && questionId && id) {
            let request = new Ajax('index.php', 'POST', '', 'addComment', {advice_id: id, question_id: questionId, comment: text});
            return ajaxCall(request).then(function (isComments) {
                if (isComments) {
                    $('#cancel').click();
                    var height = $('#chat-modal .modal-body')[0].scrollHeight;
                    setTimeout(function () {
                        $('#chat-modal .modal-body').scrollTop(height);
                    }, 300);
                }
            });
        }

    });
    
    // Send updates user settings
    $(document).on('click', '#send-settings', function () {
        let inputs = ['#firstname', '#lastname'];
        let specialties = '';
        let validate = false;
        let user_settings = null;
        let company_settings = null;
        let system_settings = null;

        if (ValidateInputs(inputs)) {
            validate = true;
            // user settings
            let firstname = $('#firstname').val();
            let lastname = $('#lastname').val();
            let duplicateToMail = $('#duplicate-mail').prop('checked');
            let allowNewsletters = $('#allow-newsletters').prop('checked');
            let img = $('#file').prop('files')[0];
            if (img) {
                let size = img.size / 1024;
                if (size <= 300) {
                    var picture = {};
                    picture.picture_str = $('#pic-preview-user').attr('src');
                    picture.picture_ext = img.type.split('/')[1];
                    user_settings = {f_name: firstname, l_name: lastname, duplicate_to_mail: duplicateToMail, allow_newsletters: allowNewsletters, picture: picture};
                    $('.preview').removeClass('red');
                    $('#pic-preview-user').removeClass('border-red');
                } else {
                    $('.preview').addClass('red');
                    $('#pic-preview-user').addClass('border-red');
                    validate = false;
                }

            } else {
                user_settings = {f_name: firstname, l_name: lastname, duplicate_to_mail: duplicateToMail, allow_newsletters: allowNewsletters};
            }

            // company settings
            if ($('#company-description').length > 0 && validate) {

                if (ValidateInputs(['.form-check-input:first'])) {
                    validate = true;
                    let arrSpElem = $('input:checkbox[name="specialties"]:checked').map(function () {
                        return $(this).val();
                    });
                    specialties = jQuery.makeArray(arrSpElem).join(' ');
                    let img = $('#company-logo').prop('files')[0];
                    if (img) {
                        let size = img.size / 1024;
                        if (size <= 300) {
                            var logo = {};
                            logo.logo_str = $('#pic-preview-company').attr('src');
                            logo.logo_ext = img.type.split('/')[1];
                            company_settings = {company_description: $('#company-description').val(), company_specialties: specialties, logo: logo};
                        } else {
                            $('#company-settings .preview').addClass('red');
                            $('#pic-preview-company').addClass('border-red');
                        }
                    } else {
                        company_settings = {company_description: $('#company-description').val(), company_specialties: specialties};
                    }
                }

            }
            // system settings
            else if ($('#questions-limit').length > 0 && validate) {
                validate = true;
                let daily_questions_limit = $('#questions-limit').val();
                let statistics_interval = $('#companies-stat').val();
                system_settings = {daily_questions_limit: daily_questions_limit, statistics_interval: statistics_interval};
            }
            if (validate) {
                var request = new Ajax('index.php', 'POST', '', 'updateSettings', {user_settings: user_settings, company_settings: company_settings, system_settings: system_settings});
                ajaxCall(request);
            }
        }
    });

    // Send question form
    $(document).on('click', '#send-question', function () {
        let isValid = true;
        let techData = new QuestionTechnicalDetails();
        let question = new Question();
        $('#question-form *').filter(':input[type="number"]:visible,:input[type="radio"]:checked:visible,select:visible').each(function () {
            if ($(this)[0].checkValidity()) {
                techData[$(this).attr('id') ? ($(this).attr('id')).replace(/-/g, '_') : $(this).attr('name')] = $(this).val();
            } else {
                isValid = false;
            }
        });
        if (isValid) {
            let questionType = capitalizeFirst($('#q-type1').val()) + ' ' + capitalizeFirst($('#q-type2').val()) + ($('#q-type3').val() ? ' ' + capitalizeFirst($('#q-type3').val()) : '');
            let description = $('#description').val();
            let title = capitalizeFirst($('#title').val());
            question.title = title;
            question.description = description;
            question.type = questionType;
            question.tech_details = techData;
            let request = new Ajax('index.php', 'POST', '', 'postQuestion', {question: question});
            return ajaxCall(request).then(function (isAdded) {
                if (isAdded) {
                    $('#new-question').modal('hide');
                    $('#new-question').find('input,textarea,select')
                            .val('')
                            .end()
                            .find('input[type=checkbox]')
                            .prop('checked', '')
                            .end();
                    if (ajaxReceivedContent) {
                        openAdvicesModal(ajaxReceivedContent);
                    } else {
                        showAlert('success', 'Success', 'Question has been saved', false, 2000);
                    }
                }
            });
        }
    });
    
    // Load advice details modal
    function openAdvicesModal(advices) {
        if (advices.length > 0) {
            var block = $('#advice-content .carousel-inner .carousel-item');
            var copy = block.clone(true);
            advices.forEach(function (item, index) {
                if (index === 0) {
                    block.find('#title').val(item.advice_details.title);
                    block.find('#description').val(item.advice_details.description);
                    block.find('#manufacturer').val(item.advice_details.manufacturer);
                    var keys = Object.keys(item.advice_tech_data);
                    for (let i = 0; i < keys.length; i++) {
                        if (item.advice_tech_data[keys[i]]) {
                            block.find('input[name=' + keys[i] + ']').val(item.advice_tech_data[keys[i]]);
                        }
                    }
                    block.attr('id', item.advice_details.id);
                    block.attr('data-question', item.advice_details.question_id);

                } else {
                    let clone = copy.clone(true);
                    clone.find('#title').val(item.advice_details.title);
                    clone.find('#description').val(item.advice_details.description);
                    clone.find('#manufacturer').val(item.advice_details.manufacturer);
                    var keys = Object.keys(item.advice_tech_data);
                    for (let i = 0; i < keys.length; i++) {
                        if (item.advice_tech_data[keys[i]]) {
                            clone.find('input[name=' + keys[i] + ']').val(item.advice_tech_data[keys[i]]);
                        }
                    }
                    clone.attr('id', item.advice_details.id);
                    clone.attr('data-question', item.advice_details.question_id);
                    clone.insertBefore('.carousel-control-prev');
                }
                let toggles = $('#' + item.advice_details.id).find('.collapsed');
                toggles.each(function () {
                    let data = $(this).attr('data-target');
                    $(this).attr('data-target', data + '-' + item.advice_details.id);
                });
                let collapsed = $('#' + item.advice_details.id).find('.collapse');
                collapsed.each(function () {
                    let data = $(this).attr('id');
                    let exist = false;
                    $(this).attr('id', data + '-' + item.advice_details.id);
                    collapsed.children('input').each(function () {
                        if ($(this).val().length === 0) {
                            $(this).hide();
                            $(this).prev().hide();
                            if (!exist) {
                                $('#' + item.advice_details.id).find('.collapsed').hide();
                            }
                        } else {
                            exist = true;
                            $('#' + item.advice_details.id).find('.collapsed').show();
                        }
                    });

                });
            });
            block.first().addClass('active');
            $('#advicesModal2').modal('show');
        }
    }
    
    // Set limit to characters count in the textarea
    $(document).on('input', 'textarea', function () {
        let currentLength = $(this).val().length;
        $('#txt-input-counter').html(currentLength + '/' + $(this).attr('maxlength'));
        if ($('#txt-input-counter2')) {
            $('#txt-input-counter2').html(currentLength + '/' + $(this).attr('maxlength'));
        }
    });
    
    // Clear question form
    $(document).on('click', '#cancel', function () {
        if ($('#new-question,#add-response,#chat-modal').length > 0) {
            $('#new-question,#add-response, #chat-modal').find('input,textarea,select')
                    .val('')
                    .end()
                    .find('input[type=checkbox]')
                    .prop('checked', '')
                    .end();
            $('#add-response #score,#add-response #description,#add-response #title').removeClass('disabled');
            $('#add-response .toggle').css('pointer-events', 'all');
            $('label[for="best-advice"]').css('pointer-events', 'all');
            $('.tip').show();
            $('#send-response').show();

        }
    });
    
    // Add to advice to question
    $(document).on('click', '.addAdvice', function () {
        $('#adviserModal').modal('show');
        $('#adviserModal').attr('data-question-id', $(this).attr('data-userid'));
        $('#adviserModal').attr('data-askid', $(this).attr('data-askid'));
    });
    
    // Send new advice
    $(document).on('click', '#send-advice', function () {
        if ($('#title, #description').val().length > 0) {
            let advice = new Advice();
            let techData = new AdviceTechnicalDetails();
            $('#adviserModal input,#adviserModal textarea').each(function () {
                techData[$(this).attr('name')] = $(this).val();
            });

            advice.title = capitalizeFirst($('#title').val());
            advice.description = $('#description').val();
            advice.tech_details = techData;

            var request = new Ajax('index.php', 'POST', '', 'giveAdvice', {question_id: $('#adviserModal').attr('data-question-id'), asking_user_id: $('#adviserModal').attr('data-askid'), advice: advice});
            return ajaxCall(request).then(function (isSent) {
                if (isSent) {
                    $('#adviserModal').modal('hide');
                    $('.addAdvice[data-userid="' + $('#adviserModal').attr('data-question-id') + '"]').addClass("disabled");
                }
                $('#adviserModal').find('input,textarea').val('').end();
            });
        }
    });
    
    // Question form modal
    $(document).on('click', '#modal__open-question', function () {

        $('#new-question .specific-details').children().fadeOut();
        $('#new-question .application').fadeOut();
        $('#new-question #machine-type').change(function () {
            $('#new-question .specific-details').children().fadeOut();
            $('#new-question #q-type2,#new-question #q-type3,#new-question .application').fadeOut();
            $('#new-question .main-details').children().prop('disabled', false);
            $('#new-question #machine-power,#new-question #machine-max-rpm').prop('disabled', false);
            var val = $(this).val();
            if (val === 'milling' || val === 'turning') {
                if (val === 'milling') {
                    $('#new-question .milling').children().fadeIn();
                    $('#new-question #q-type1').html("<option value='' selected disabled>Select type</option><option value='milling'>Milling</option>");
                } else if (val === 'turning') {
                    $('#new-question .turning').children().fadeIn();
                    $('#new-question #q-type1').html("<option value='' selected disabled>Select type</option><option value='turning'>Turning</option><option value='hole-making'>Hole making</option>");
                }
                $('#new-question #q-type2,#new-question #q-type3').html('');
                $('#new-question .application').fadeIn();
            }
        });
        $('#new-question #q-type1').change(function () {
            $('#new-question .specific-details').children('#new-question .milling').fadeOut();
            $('#new-question .specific-details').children('h2').fadeOut();
            var val = $(this).val();
            $('#new-question #q-type2').fadeIn();
            $('#new-question #q-type3').fadeOut();
            $('#new-question #q-type3').html('');
            if (val === 'milling') {
                $('#new-question #q-type2').html("<option value='' selected disabled>Select type</option><option value='shouldering'>Shouldering</option><option value='slotting'>Slotting</option>");
            } else if (val === 'turning') {
                $('#new-question .hole-making').fadeOut();
                $('#new-question .turning').fadeIn();
                $('#new-question #q-type2').html("<option value='' selected disabled>Select type</option><option value='iso-turning'>Iso Turning</option><option value='grooving'>Grooving</option><option value='parting'>Parting</option>");
            } else if (val === 'hole-making') {
                $('#new-question .specific-details').children('h2').fadeIn();
                $('#new-question .iso-turning,#new-question .grooving,#new-question .parting,#new-question .grooving-internal,#new-question .grooving-external,#new-question .grooving-face').fadeOut();
                $('#new-question .specific-details').children('#new-question .iso-turning,#new-question .grooving,#new-question .parting,#new-question .grooving-internal,#new-question .grooving-external,#new-question .grooving-face').fadeOut();
                $('#new-question .grooving').children().fadeOut();
                $('#new-question .hole-making,#new-question .parameters').fadeIn();
                $('#new-question #q-type2').html("<option selected value='' disabled>Select type</option><option value='reaming'>Reaming</option><option value='drilling'>Drilling</option><option value='threading'>Threading</option><option value='tapping'>Tapping</option>");
            }
        });
        $('#new-question #q-type2').change(function () {
            var val = $(this).val();
            $('#new-question #q-type3,#new-question .parameters').fadeOut();
            $('#new-question #q-type3').html('');
            if (val === 'slotting') {
                $('#new-question .specific-details').children('#new-question .milling, #new-question h2').fadeIn();
                $('#new-question #q-type3').fadeIn();
                $('#new-question #q-type3').html("<option value='' selected disabled>Select type</option><option value='side-slotting'>Side Slotting</option>");
            } else if (val === 'iso-turning') {
                $('#new-question #q-type3,#new-question .iso-turning').fadeIn();
                $('#new-question .specific-details').children('#new-question .iso-turning,#new-question h2').fadeIn();
                $('#new-question #q-type3').html("<option selected value='' disabled>Select type</option><option value='internal'>Internal</option><option value='external'>External</option>");
            } else if (val === 'grooving') {
                $('#new-question .specific-details').children('#new-question .grooving,#new-question h2').fadeIn();
                $('#new-question .grooving').children().fadeIn();
                $('#new-question #q-type3,#new-question .grooving').fadeIn();
                $('#new-question #q-type3').html("<option value='' selected disabled>Select type</option><option value='internal'>Internal</option><option value='external'>External</option><option value='face-groove'>Face Groove</option>");
            } else if (val === 'parting') {
                $('#new-question .specific-details').children('#new-question .parting, #new-question h2').fadeIn();
                $('#new-question .parting,#new-question .parameters').fadeIn();
                $('#new-question .parting').children().fadeIn();
            } else if ($('#new-question #q-type1').val() === 'hole-making') {
                $('#new-question .parameters').fadeIn();
            } else if (val === 'shouldering') {
                $('#new-question .specific-details').children('#new-question .milling, #new-question h2').fadeIn();
                $('#new-question .specific-details').fadeIn();
            }
        });
        $('#new-question #q-type3').change(function () {
            var val = $(this).val();
            if (val === 'internal' && $('#new-question #q-type2').val() === 'iso-turning') {
                $('#new-question .grooving-internal,#new-question .grooving-external,#new-question .grooving-face,#new-question .iso-turning').fadeOut();
                $('#new-question .specific-details').children('#new-question .iso-turning-internal,#new-question .grooving-internal,#new-question .grooving-external,#new-question .grooving-face,#new-question .iso-turning').fadeOut();
                $('#new-question .iso-turning').children('#new-question .iso-turning-internal').fadeIn();
                $('#new-question .iso-turning').fadeIn();
            } else if (val === 'external' && $('#new-question #q-type2').val() === 'iso-turning') {
                $('#new-question .iso-turning').children().fadeOut();
                $('#new-question .specific-details').children('#new-question .grooving-internal,#new-question .grooving-external,#new-question .grooving-face').fadeOut();
                $('#new-question .iso-turning').children().fadeIn();
                $('#new-question .iso-turning').fadeIn();
                $('#new-question .iso-turning-internal').fadeOut();
            } else if (val === 'internal' && $('#new-question #q-type2').val() === 'grooving') {
                $('#new-question .grooving-external,#new-question .grooving-face,#new-question .iso-turning').fadeOut();
                $('#new-question .grooving-internal').fadeIn();
            } else if (val === 'external' && $('#q-type2').val() === 'grooving') {
                $('#new-question .grooving-internal,#new-question .grooving-face,#new-question .iso-turning').fadeOut();
                $('#new-question .grooving-external').fadeIn();
            } else if (val === 'face-groove' && $('#q-type2').val() === 'grooving') {
                $('#new-question .grooving-internal,#new-question .grooving-external,#new-question .iso-turning').fadeOut();
                $('#new-question .grooving-face').fadeIn();
            }
            $('#new-question .parameters').fadeIn();
        });
        $('#new-question #material-type').change(function () {
            $('#new-question .material-details').children().prop('disabled', false);
        });
    });
    $('.js-tilt').tilt({
        scale: 1.1
    });
});

// Show alert to user
function showAlert(type, title, text, showConfirmButton = true, timer = null, onClosed = false) {
    Swal.fire({
        type: type,
        title: title,
        text: text,
        showConfirmButton: showConfirmButton,
        timer: timer,
    }).then((result) => {
        if (result.value) {
            if (onClosed) {
                $('html, body').animate({
                    scrollTop: 0
                }, 1500);
            }
        }
    });
}

// Cookies management
function getPageCookies(pageIndex) {
    if (pageIndex) {
        sessionStorage.removeItem('page');
        sessionStorage.setItem('page', pageIndex);
    } else {
        var lastPage = sessionStorage.getItem('page');
        if (lastPage) {
            let request = new Ajax('index.php', 'POST', '', 'getPage', lastPage);
            ajaxCall(request);
            getActiveMenuItem(lastPage);
        }
    }
}

// Start bootstrap table
function startTable() {
    if (ajaxReceivedContent) {
        if ($('#companies-index').length > 0) {
            $('#companies-index').bootstrapTable({
                data: ajaxReceivedContent.companies ? ajaxReceivedContent.companies : [],
                exportTypes: ['excel', 'pdf'],
                exportDataType: 'all',
            });
        } else if ($('#users-admin').length > 0) {
            $('#users-admin').bootstrapTable({
                data: ajaxReceivedContent.users ? ajaxReceivedContent.users : [],
                exportTypes: ['excel', 'pdf'],
                exportDataType: 'all'
            });
        } else if ($('#users-company').length > 0) {
            $('#users-company').bootstrapTable({
                data: ajaxReceivedContent.users ? ajaxReceivedContent.users : [],
                exportTypes: ['excel', 'pdf'],
                exportDataType: 'all'
            });
        } else if ($('#companies-admin').length > 0) {
            $('#companies-admin').bootstrapTable({
                data: ajaxReceivedContent.companies ? ajaxReceivedContent.companies : [],
                exportTypes: ['excel', 'pdf'],
                exportDataType: 'all'
            });
        } else if ($('#companies-asking').length > 0) {
            $('#companies-asking').bootstrapTable({
                data: ajaxReceivedContent.companies ? ajaxReceivedContent.companies : [],
                exportTypes: ['excel', 'pdf'],
                exportDataType: 'all'
            });
        } else if ($('#questions-admin').length > 0) {
            $('#questions-admin').bootstrapTable({
                data: ajaxReceivedContent.questions ? ajaxReceivedContent.questions : [],
                exportTypes: ['excel', 'pdf'],
                exportDataType: 'all'
            });

        } else if ($('#questions-advising').length > 0) {
            $('#questions-advising').bootstrapTable({
                data: ajaxReceivedContent.questions ? ajaxReceivedContent.questions : [],
                exportTypes: ['excel', 'pdf'],
                exportDataType: 'all'
            });

        } else if ($('#questions-asking').length > 0) {
            $('#questions-asking').bootstrapTable({
                data: ajaxReceivedContent.questions ? ajaxReceivedContent.questions : [],
                exportTypes: ['excel', 'pdf'],
                exportDataType: 'all'
            });

        } else if (ajaxReceivedContent.messages && $('#messages-in').length > 0 && $('#messages-out').length > 0) {
            $('#messages-in').bootstrapTable({
                data: ajaxReceivedContent.messages[0].messages_in ? ajaxReceivedContent.messages[0].messages_in : [],
                exportTypes: ['excel', 'pdf'],
                exportDataType: 'all'
            });
            $('#messages-out').bootstrapTable({
                data: ajaxReceivedContent.messages[1].messages_out ? ajaxReceivedContent.messages[1].messages_out : [],
                exportTypes: ['excel', 'pdf'],
                exportDataType: 'all'
            });
            $('#messages-out').closest('.bootstrap-table').children('.fixed-table-toolbar').first().fadeOut();
            $('#messages-out').closest('.bootstrap-table').children('.fixed-table-pagination').first().fadeOut();
            $('#messages-out').fadeOut();
            $('.send-message').fadeOut();
        } else if ((ajaxReceivedContent.messages_in || ajaxReceivedContent.messages_in == null) && $('#messages-in').length > 0 && !$('.tabs li a.active').hasClass('new-mes')) {
            $('#messages-in').bootstrapTable({
                data: ajaxReceivedContent.messages_in ? ajaxReceivedContent.messages_in : [],
                exportTypes: ['excel', 'pdf'],
                exportDataType: 'all'
            });
        } else if ($('.send-message').length > 0) {
            var arrayReceivers = ajaxReceivedContent.map(function (user) {
                return {name: user.name, username: user.username, id: user.id};
            });
            loadSelect(arrayReceivers);
        } else {
            $('#inner').html('<h2 class="text-center"> There is no data to display</h2>')
        }

    }
}

// Get active menu item
function getActiveMenuItem(pageIndex) {
    var index = null;
    $('.nav-link').each(function () {
        if (pageIndex) {
            index = $(this).attr('data-index').toLowerCase();
            if (index == pageIndex) {
                $(this).addClass('active');
                $(this).parent('.nav-item').addClass('active');
            } else {
                $(this).removeClass('active');
                $(this).parent('.nav-item').removeClass('active');
            }
        } else {
            if ($(this).hasClass('active')) {
                index = $(this).attr('data-index').toLowerCase();
            }
        }
    });
    return index;
}

// Validation inputs fields
function ValidateInputs(elements) {
    var isValid = true;
    if (elements) {
        $('.invalid-feedback').remove();
        $.each(elements, function (index, item) {
            let type = $(item).attr('name');
            let error = '';
            if (type) {
                switch (type) {
                    case 'username':
                        error = ValidateRules($(item).val().toLowerCase(), true, 6, 12, true, false, false);
                        break;
                    case 'password':
                        error = ValidateRules($(item).val().toLowerCase(), true, 6, 12, true, false, false);
                        if ($('#pwd_confirm').length > 0) {
                            if ($(item).val() !== $('#pwd_confirm').val()) {
                                $('#pwd_confirm').after('<div class="invalid-feedback d-block">Password did not match: Please try again...</div>');
                                isValid = false;
                            } else {
                                if ($('#pwd_confirm').next('.invalid-feedback').length > 0) {
                                    $('#pwd_confirm').next('.invalid-feedback').remove();
                                }
                            }
                        }
                        break;
                    case 'email':
                        error = ValidateRules($(item).val().toLowerCase(), true, 0, null, false, true, false);
                        break;
                    case 'lastname':
                    case 'firstname':
                    case 'company':
                        error = ValidateRules($(item).val().toLowerCase(), true, 1, null, false, false, true);
                        break;
                    case 'description':
                        error = ValidateRules($(item).val().toLowerCase(), true, 1, null, true, false, false);
                        break;
                    case 'text':
                        error = ValidateRules($(item).val(), true, 10, null, false, false, false);
                        break;
                    case 'array':
                        error = $(item).val().length == 0 ? 'At least one recipient must be selected' : '';
                        break;
                    case 'specialties':
                        let checkedCheckboxesValues =
                                $('input:checkbox[name="specialties"]:checked')
                                .map(function () {
                                    return $(this).val();
                                }).get();
                        if (checkedCheckboxesValues.length === 0) {
                            $('#spec-label').after('<div class="invalid-feedback d-block">At least one option should be selected</div>');
                            isValid = false;
                        } else {
                            if ($('#specialties label').next('.invalid-feedback')) {
                                $('#specialties label').next('.invalid-feedback').remove();
                            }
                        }
                        break;
                }
                if (error.length > 0) {
                    $(item).after('<div class="invalid-feedback d-block">' + error + '</div>');
                    isValid = false;
                }
            }
        });
    }
    return isValid;
}

// Validation rules
function ValidateRules(item, required, minlength, maxlength, alphanum, mail, alpha) {
    let message = '';
    let regx_alpha_num = /^[A-Za-z0-9 _.-]+$/;
    let regx_mail = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
    let regx_alpha = /^[a-z][a-z\s]*$/;
    if (required && item.length === 0) {
        message += 'Please fill out this field.';
    } else if (minlength && item.length < minlength) {
        message += ' The minimum length should be ' + minlength + '.';
    } else if (maxlength && item.length > maxlength) {
        message += ' The maximum length should be ' + maxlength + '.';
    } else if (alphanum && !regx_alpha_num.test(item)) {
        message += ' Only letters and numbers are accepted.';
    } else if (mail && !regx_mail.test(item)) {
        message += ' Email address is not valid.';
    } else if (alpha && !regx_alpha.test(item)) {
        message += ' Only letters are accepted.';
    }
    return message;
}

// Sending request to server
function ajaxCall(request, isReload) {
    if (request) {
        var data = JSON.stringify({'actionName': request.actionName, 'data': request.data});
        return new Promise(function (resolve, reject) {
            $.ajax({
                url: request.url,
                type: request.type,
                data: {data: data},
                beforeSend: function () {
                    $('#loader-div').show();
                },
                complete: function () {
                    $('#loader-div').hide();
                },
                success: function (response) {
                    var res = null;
                    try {
                        res = jQuery.parseJSON(response);
                    } catch (e) {
                        res = response;
                    }
                    if (res) {
                        if (res.success) {
                            if (res.html) {
                                if ($('#main-content').length > 0 && getActiveMenuItem() != 0) {
                                    $('#main-content').html(res.html);
                                } else if ($('#inner').length > 0 && getActiveMenuItem() != 0) {
                                    $('#inner').html(res.html);
                                } else if ($('#inner').length > 0 && getActiveMenuItem() == 0) {
                                    $('#inner').html(res.html);
                                    $('#companies-index').unwrap();
                                }
                            }
                            if (res.message) {
                                showAlert('success', 'Success', res.message, false, 2000);
                            } else if (res.content && res.content.settings) {
                                setSettings(res.content.settings);
                            } else if (res.content && res.content.specialties) {
                                setSpecialties(res.content.specialties);
                            } else if (res.content && res.content.question_tech_data) {
                                setTechData(res.content.question_tech_data);
                            } else if (res.content && res.content.advices) {
                                ajaxReceivedContent = res.content.advices;
                                window.ajaxReceivedContent = ajaxReceivedContent;
                            } else if (res.content && res.content.response) {
                                ajaxReceivedContent = res.content.response;

                            } else if (res.content && res.content.comments != null) {
                                if (res.content.comments.length > 0) {
                                    $('#chat-modal .modal-body').html(res.content.comments);
                                } else {
                                    $('#chat-modal .modal-body').html("There are no comments");
                                }
                            } else if (res.content) {
                                ajaxReceivedContent = res.content;
                                startTable();
                            }
                            if (isReload) {
                                setTimeout(function () {
                                    location.reload();
                                }, 2200);
                            }
                            resolve(true);
                            if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                                $('.navbar-toggler').addClass('collapsed');
                                $('.navbar-toggler').attr('aria-expanded', false);
                                $('#collapse-mobile').removeClass('show');
                            }
                        } else {
                            resolve(false);
                            showAlert(res.message_type, res.message_type, res.message, true, null, true);
                        }
                    }
                },
                error: function (xhr, desc, err)
                {
                    showAlert('error', 'Oops...', err);
                    console.log(err);
                }
            });
        });
    }
}

// Set specialties options
function setSpecialties(content) {
    if (content && $('#specialties').length > 0) {
        let specialties = content.split(' ');
        let html = '<label for="specialties" id="spec-label">Select Fields of Interests:</label>';
        for (let i = 0; i < specialties.length; i++) {
            html += '<div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value='
                    + specialties[i] + ' name="specialties">' + specialties[i] + '</label></div>';
        }
        $('#specialties').html(html);
    }
}

// Set the settings fields
function setSettings(settings) {

    // user settings
    if (settings.user_settings) {
        let userPhoto = settings.user_settings.picture_filename;
        let isDuplicateToMail = settings.user_settings.duplicate_to_mail === true;
        let isAllowNewsletters = settings.user_settings.allow_newsletters === true;
        $('#firstname').val(settings.user_settings.f_name);
        $('#lastname').val(settings.user_settings.l_name);
        $('#duplicate-mail').prop('checked', isDuplicateToMail);
        $('#allow-newsletters').prop('checked', isAllowNewsletters);
        $('#pic-preview-user').prop('src', 'Content/images/user_pictures/' + userPhoto);
    }

    // system settings
    if (settings.system_settings) {
        let systemQuestionsLimit = settings.system_settings.daily_questions_limit;
        let systemStatisticsInterval = settings.system_settings.statistics_interval;
        $('#questions-limit').val(systemQuestionsLimit);
        $('#companies-stat').val(systemStatisticsInterval);
    }

    // company settings
    if (settings.company_settings) {
        let companySpecialties = settings.company_settings.company_specialties.split(' ');
        let companyLogo = settings.company_settings.logo_filename;
        $('#company-description').val(settings.company_settings.company_description);

        if (settings.allowed_specialties && $('#specialties').length > 0) {
            setSpecialties(settings.allowed_specialties);
        }

        $('input[name="specialties"]').each(function () {
            if (companySpecialties.indexOf($(this).val()) > -1) {
                $(this).attr('checked', true);
            }
        });

        $('#pic-preview-company').prop('src', 'Content/images/companies_logos/' + companyLogo);
    }
}

// Capitalize first letter of a string
function capitalizeFirst(text) {
    return text.charAt(0).toUpperCase() + text.slice(1);
}

// Loading multiselect plugin
function loadSelect(arrayReceivers) {
    var REGEX_EMAIL = '([a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*@' +
            '(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)';
    if (arrayReceivers) {
        $('#contacts').selectize({
            persist: false,
            maxItems: null,
            valueField: 'id',
            labelField: 'name',
            searchField: ['name', 'username'],
            options: arrayReceivers,
            render: {
                item: function (item, escape) {
                    return '<div>' +
                            (item.name ? '<span class="name">' + escape(item.name) + '</span>' : '') +
                            (item.username ? '<span class="email">' + escape(item.username) + '</span>' : '') +
                            '</div>';
                },
                option: function (item, escape) {
                    var label = item.name || item.username;
                    var caption = item.name ? item.username : null;
                    return '<div>' +
                            '<span class="label">' + escape(label) + '</span>' +
                            (caption ? '<span class="caption">' + escape(caption) + '</span>' : '') +
                            '</div>';
                }
            },
            createFilter: function (input) {
                var regexpA = new RegExp('^' + REGEX_EMAIL + '$', 'i');
                var regexpB = new RegExp('^([^<]*)\<' + REGEX_EMAIL + '\>$', 'i');
                return regexpA.test(input) || regexpB.test(input);
            },
            create: function (input) {
                return {
                    id: input,
                    name: input,
                    username: input
                };
            }
        });
    }
}

// Fill tech-data form
function setTechData(question_tech_data) {
    $('#fill-question').modal('show');
    $('#fill-question select,#fill-question input,#fill-question textarea,#fill-question .form-control').prop('disabled', true);
    $('#fill-question select,#fill-question input,#fill-question textarea,#fill-question .form-control,#fill-question label').fadeIn();
    $('#fill-question #machine-type').val(question_tech_data.machine_type.toLowerCase());
    $('#fill-question #adaptation-type').val(question_tech_data.adaptation_type.toLowerCase());
    $('#fill-question #machine-power').val(question_tech_data.machine_power);
    $('#fill-question #machine-max-rpm').val(question_tech_data.machine_max_rpm);
    $('#fill-question #adaptation-size').val(question_tech_data.adaptation_size);
    $('#fill-question #material-type').val(question_tech_data.material_type);
    $('#fill-question #material-hb').val(question_tech_data.material_hb);
    $('#fill-question #material-hrc').val(question_tech_data.material_hrc);
    $('#fill-question input[name="clamping"][value=' + question_tech_data.clamping + ']').prop('checked', true);
    if (question_tech_data.adaptation_direction) {
        $('#fill-question input[name="adaptation_direction"][value=' + question_tech_data.adaptation_direction + ']').prop('checked', true).fadeIn();
    } else {
        $('#fill-question input[name="adaptation_direction"]').parent().fadeOut();
    }
    if (question_tech_data.adaptation_tool_type) {
        $('#fill-question input[name="adaptation_tool_type"][value=' + question_tech_data.adaptation_tool_type + ']').prop('checked', true).fadeIn();
    } else {
        $('#fill-question input[name="adaptation_tool_type"]').parent().fadeOut();
    }
    if (question_tech_data.boring_diameter) {
        $('#fill-question input[name="boring_diameter"]').val(question_tech_data.boring_diameter).fadeIn();
    } else {
        $('#fill-question input[name="boring_diameter"]').parent().fadeOut();
    }
    if (question_tech_data.coolant_type) {
        $('#fill-question input[name="coolant_type"][value=' + question_tech_data.coolant_type + ']').prop('checked', true).fadeIn();
    } else {
        $('#fill-question input[name="coolant_type"]').parent().fadeOut();
        $('#fill-question input[name="coolant_type"]').parent().parent().prev().fadeOut();
    }
    if (question_tech_data.corner_radius) {
        $('#fill-question input[name="corner_radius"]').val(question_tech_data.corner_radius).fadeIn();
    } else {
        $('#fill-question input[name="corner_radius"]').parent().fadeOut();
    }
    if (question_tech_data.cut_depth) {
        $('#fill-question input[name="cut_depth"]').val(question_tech_data.cut_depth).fadeIn();
    } else {
        $('#fill-question input[name="cut_depth"]').parent().fadeOut();
    }
    if (question_tech_data.cut_length) {
        $('#fill-question input[name="cut_length"]').val(question_tech_data.cut_length).fadeIn();
    } else {
        $('#fill-question input[name="cut_length"]').parent().fadeOut();
    }
    if (question_tech_data.cut_type) {
        $('#fill-question input[name="cut_type"][value=' + question_tech_data.cut_type + ']').prop('checked', true).fadeIn();
    } else {
        $('#fill-question input[name="cut_type"]').parent().parent().prev().fadeOut();
        $('#fill-question input[name="cut_type"]').parent().fadeOut();
    }
    if (question_tech_data.depth) {
        $('#fill-question input[name="depth"]').val(question_tech_data.depth).fadeIn();
    } else {
        $('#fill-question input[name="depth"]').parent().fadeOut();

    }
    if (question_tech_data.diameter) {
        $('#fill-question input[name="diameter"]').val(question_tech_data.diameter).fadeIn();
    } else {
        $('#fill-question input[name="diameter"]').parent().fadeOut();
    }
    if (question_tech_data.diameter_depth_in) {
        $('#fill-question input[name="diameter_depth_in"]').val(question_tech_data.diameter_depth_in).fadeIn();
    } else {
        $('#fill-question input[name="diameter_depth_in"]').parent().fadeOut();
    }
    if (question_tech_data.diameter_depth_out) {
        $('#fill-question input[name="diameter_depth_out"]').val(question_tech_data.diameter_depth_out).fadeIn();
    } else {
        $('#fill-question input[name="diameter_depth_out"]').parent().fadeOut();
    }
    if (question_tech_data.diameter_internal) {
        $('#fill-question input[name="diameter_internal"]').val(question_tech_data.diameter_internal).fadeIn();
    } else {
        $('#fill-question input[name="diameter_internal"]').parent().fadeOut();
    }
    if (question_tech_data.diameter_outer) {
        $('#fill-question input[name="diameter_outer"]').val(question_tech_data.diameter_outer).fadeIn();
    } else {
        $('#fill-question input[name="diameter_outer"]').parent().fadeOut();
    }
    if (question_tech_data.face_groove_type) {
        $('#fill-question input[name="face_groove_type"][value=' + question_tech_data.face_groove_type + ']').prop('checked', true).fadeIn();
    } else {
        $('#fill-question input[name="face_groove_type"]').parent().fadeOut();
    }
    if (question_tech_data.groove_depth) {
        $('#fill-question input[name="groove_depth"]').val(question_tech_data.groove_depth).fadeIn();
    } else {
        $('#fill-question input[name="groove_depth"]').parent().fadeOut();
    }
    if (question_tech_data.groove_position) {
        $('#fill-question input[name="groove_position"]').val(question_tech_data.groove_position).fadeIn();
    } else {
        $('#fill-question input[name="groove_position"]').parent().fadeOut();
    }
    if (question_tech_data.groove_width) {
        $('#fill-question input[name="groove_width"]').val(question_tech_data.groove_width).fadeIn();
    } else {
        $('#fill-question input[name="groove_width"]').parent().fadeOut();
    }
    if (question_tech_data.hole_diameter) {
        $('#fill-question input[name="hole_diameter"]').val(question_tech_data.hole_diameter).fadeIn();
    } else {
        $('#fill-question input[name="hole_diameter"]').parent().fadeOut();
    }
    if (question_tech_data.part_length) {
        $('#fill-question input[name="part_length"]').val(question_tech_data.part_length).fadeIn();
    } else {
        $('#fill-question input[name="part_length"]').parent().fadeOut();
    }
    if (question_tech_data.operation_type) {
        $('#fill-question input[name="operation_type"][value=' + question_tech_data.operation_type + ']').prop('checked', true).fadeIn();
    } else {
        $('#fill-question input[name="operation_type"]').parent().fadeOut();
        $('#fill-question input[name="operation_type"]').parent().parent().prev().fadeOut();
    }
    if (question_tech_data.overhang) {
        $('#fill-question input[name="overhang"][value=' + question_tech_data.overhang + ']').prop('checked', true).fadeIn();
    } else {
        $('#fill-question input[name="overhang"]').parent().fadeOut();
        $('#fill-question input[name="overhang"]').parent().parent().prev().fadeOut();
    }
    if (question_tech_data.part_length) {
        $('#fill-question input[name="part_length"]').val(question_tech_data.part_length).fadeIn();
    } else {
        $('#fill-question input[name="part_length"]').parent().fadeOut();
    }
    if (question_tech_data.penetration_length) {
        $('#fill-question input[name="penetration_length"]').val(question_tech_data.penetration_length).fadeIn();
    } else {
        $('#fill-question input[name="penetration_length"]').parent().fadeOut();
    }
    if (question_tech_data.r_max) {
        $('#fill-question input[name="r_max"]').val(question_tech_data.r_max).fadeIn();
    } else {
        $('#fill-question input[name="r_max"]').parent().fadeOut();
    }
    if (question_tech_data.shoulder_depth) {
        $('#fill-question input[name="shoulder_depth"]').val(question_tech_data.shoulder_depth).fadeIn();
    } else {
        $('#fill-question input[name="shoulder_depth"]').parent().fadeOut();
    }
    if (question_tech_data.shoulder_length) {
        $('#fill-question input[name="shoulder_length"]').val(question_tech_data.shoulder_length).fadeIn();
    } else {
        $('#fill-question input[name="shoulder_length"]').parent().fadeOut();
    }
    if (question_tech_data.shoulder_width) {
        $('#fill-question input[name="shoulder_width"]').val(question_tech_data.shoulder_width).fadeIn();
    } else {
        $('#fill-question input[name="shoulder_width"]').parent().fadeOut();
    }
    if (question_tech_data.surface_quality_n) {
        $('#fill-question input[name="surface_quality_n"]').val(question_tech_data.surface_quality_n).fadeIn();
    } else {
        $('#fill-question input[name="surface_quality_n"]').parent().fadeOut();
        $('#fill-question input[name="surface_quality_n"]').parent().prev().fadeOut();
    }
    if (question_tech_data.surface_quality_ra) {
        $('#fill-question input[name="surface_quality_ra"]').val(question_tech_data.surface_quality_ra).fadeIn();
    } else {
        $('#fill-question input[name="surface_quality_ra"]').parent().fadeOut();
    }
    if (question_tech_data.surface_quality_rms) {
        $('#fill-question input[name="surface_quality_rms"]').val(question_tech_data.surface_quality_rms).fadeIn();
    } else {
        $('#fill-question input[name="surface_quality_rms"]').parent().fadeOut();
    }
    if (question_tech_data.surface_quality_rt) {
        $('#fill-question input[name="surface_quality_rt"]').val(question_tech_data.surface_quality_rt).fadeIn();
    } else {
        $('#fill-question input[name="surface_quality_rt"]').parent().fadeOut();
    }
    if (question_tech_data.tolerance_r_max) {
        $('#fill-question input[name="tolerance_r_max"]').val(question_tech_data.tolerance_r_max).fadeIn();
    } else {
        $('#fill-question input[name="tolerance_r_max"]').parent().fadeOut();
    }
    if (question_tech_data.tolerance_r_min) {
        $('#fill-question input[name="tolerance_r_min"]').val(question_tech_data.tolerance_r_min).fadeIn();
    } else {
        $('#fill-question input[name="tolerance_r_min"]').parent().fadeOut();
        $('#fill-question input[name="tolerance_r_min"]').parent().prev().fadeOut();
    }
    if (question_tech_data.tolerance_w_max) {
        $('#fill-question input[name="tolerance_w_max"]').val(question_tech_data.tolerance_w_max).fadeIn();
    } else {
        $('#fill-question input[name="tolerance_w_max"]').parent().fadeOut();
    }
    if (question_tech_data.tolerance_w_min) {
        $('#fill-question input[name="tolerance_w_min"]').val(question_tech_data.tolerance_w_min).fadeIn();
    } else {
        $('#fill-question input[name="tolerance_w_min"]').parent().fadeOut();
        $('#fill-question input[name="tolerance_w_min"]').parent().prev().fadeOut();
        $('#fill-question input[name="tolerance_w_min"]').closest('div').prev().fadeOut();
    }
    if (question_tech_data.workpiece_diameter) {
        $('#fill-question input[name="workpiece_diameter"]').val(question_tech_data.workpiece_diameter).fadeIn();
    } else {
        $('#fill-question input[name="workpiece_diameter"]').parent().fadeOut();
    }
}

// Change css style in input invalid
function showValidate(input) {
    var thisAlert = $(input).parent();
    $(thisAlert).addClass('alert-validate');
}

// Change css style in input valid
function hideValidate(input) {
    var thisAlert = $(input).parent();
    $(thisAlert).removeClass('alert-validate');
}

// First load data to storage
function loadFirstPageData() {
    if ($('.main-menu').hasClass('inner-user') && $('.main-menu li').first().hasClass('active') &&
            !sessionStorage.getItem('page')) {
        let request = new Ajax('index.php', 'POST', '', 'getPage', 0);
        ajaxCall(request);
    }
}

// Cancel settings button pressed
$(document).on('click', '#discard-settings', function () {
    location.reload();
});

// Load page data on page load
$(window).on('load', function () {
    getPageCookies();
});
