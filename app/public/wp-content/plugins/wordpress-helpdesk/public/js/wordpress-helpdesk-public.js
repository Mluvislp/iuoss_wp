(function( $ ) {
    'use strict';

    // Create the defaults once
    var pluginName = "helpdesk",
        defaults = {
            bla: "",
        };

    // The actual plugin constructor
    function Plugin ( element, options ) {
        this.element = element;
        this.settings = $.extend( {}, defaults, options );
        this._defaults = defaults;

        this._name = pluginName;
        this.init();
    }

    // Avoid Plugin.prototype conflicts
    $.extend( Plugin.prototype, {
        init: function() {
            var that = this;
            this.window = $(window);
            this.documentHeight = $( document ).height();
            this.windowHeight = this.window.height();

            this.comment_ids = [];
            this.desktopNotifications.comment_ids = [];

            if(this.settings.integrationsWooCommerce == 1){
                this.woocommerceForm();
            }
            // if(this.settings.FAQShowSearch == 1){
                this.FAQSearch();
            // }
            if(this.settings.enableDesktopNotifications == 1){
                this.desktopNotifications();
            }

            if(this.settings.enableLiveChat == 1){
                this.liveChatNew();
            }
            // if(this.settings.enableLiveChat == 1){
            //     this.liveChat();
            // }
            if(this.settings.FAQRatingEnable == 1){
                this.FAQRating();
            }
            this.mytickets();
            this.attachments();
            this.openAI();
            this.savedReplies();
            this.faqAccordion();

            setTimeout(function(){ $('.ticket #comment-tmce').trigger('click'); }, 3000);

        },
        faqAccordion : function() {

            $('.wordpress-helpdesk-faq-accordion').on('click', '.wordpress-helpdesk-faq-title-link', function(e) {
                e.preventDefault();

                var $this = $(this);
                var icon = $this.find('.wordpress-helpdesk-faq-accordion-icon');

                if(icon.hasClass('fa-chevron-down')) {
                    icon.removeClass('fa-chevron-down');
                    icon.addClass('fa-chevron-up');
                } else {
                    icon.removeClass('fa-chevron-up');
                    icon.addClass('fa-chevron-down');
                }

                $(this).next('.wordpress-helpdesk-faq-content-container').toggle();
            }); 

        },
        // WooCommerce dynamic fields
        woocommerceForm : function() {

            var links = $('.wordpress-helpdesk-woo-form-show');

            links.on('click', function(e) {
                e.preventDefault();
                var $this = $(this);

                var toShow = $this.data('show');
                $('.wordpress-helpdesk-order-form').addClass('wordpress-helpdesk-hidden');
                $('.wordpress-helpdesk-product-form').addClass('wordpress-helpdesk-hidden');
                $('.wordpress-helpdesk-other-form').addClass('wordpress-helpdesk-hidden');

                $('.wordpress-helpdesk-' + toShow + '-form').removeClass('wordpress-helpdesk-hidden');
                $('.wordpress-helpdesk-form').fadeIn();
            });
        },
        // Add encoding to Comment Form to save attachments
        attachments : function() {
            var comment_form = $('.single-ticket #commentform');

            if(comment_form.length > 0) {
                $('.single-ticket #commentform')[0].encoding = 'multipart/form-data';
            }

            if(typeof LuminousGallery !== 'undefined') {
                new LuminousGallery($('.wordpress-helpdesk-ticket-attachments a.is-image, .wordpress-helpdesk-comment-attachments a.is-image'), {}, {
                    caption: function(trigger) {
                        return trigger.querySelector('img').getAttribute('alt');
                    }
                });
            }
        },
        // Add Datatables to my tickets
        mytickets : function() {
            var my_tickets_table = $('.wordpress-helpdesk-my-tickets-table');

            if(my_tickets_table.length > 0 && this.settings.myTicketsDatatablesEnable == "1") {
                var datatableOptions = {
                    "order": [[ 0, "desc" ]],
                    'language': {
                        'url' : this.settings.myTicketsDatatablesLanguageURL
                    },
                    responsive: true
                };

                my_tickets_table.DataTable(datatableOptions);
            }
        },
        
        // FAQ Search
        FAQSearch : function () {

            var that = this;
            var searchTerms = $('.wordpress-helpdesk-faq-searchterm');
            var delayTimer;

            $('.wordpress-helpdesk-faq-searchform').on('submit', function(e) {

                e.preventDefault();

                $(this).find('.wordpress-helpdesk-faq-searchterm').trigger('keyup');

                return false;
            });

            searchTerms.each(function(i, index) {

                var searchTerm = $(this);
                var resultContainer = searchTerm.siblings('.wordpress-helpdesk-faq-live-search-results');
                var searchIcon = searchTerm.siblings('.searchform-submit').find('.fa-search');
                
                searchTerm.on('keyup', function(e) {
                    resultContainer.fadeOut();

                    var $this = $(this);
                    var term = $this.val();

                    if(term.length > 1) {

                        searchIcon.removeClass('fa-search').addClass('fa-spinner fa-spin');

                        clearTimeout(delayTimer);
                        delayTimer = setTimeout(function() {
                            $.ajax({
                                type : 'post',
                                url : that.settings.ajax_url,
                                dataType : 'json',
                                data : {
                                    term : term,
                                    action : 'search_faqs'
                                },
                                success : function( response ) {
                                    if( (searchTerm.attr('name') == "subject") && response.count == 0){
                                        return false;
                                    } else {
                                        resultContainer.fadeIn().html(response.message);
                                    }
                                    searchIcon.removeClass('fa-spinner fa-spin').addClass('fa-search');
                                }
                             });
                        }, 700);
                    }
                });

                $(document).click(function(e) {
                    if ( $(e.target).closest('.wordpress-helpdesk-faq-live-search-results').length === 0 ) {
                        resultContainer.fadeOut();
                    }
                });
            });
        },
        openAI : function() {

            var that = this;

            $(document).on('click', '.wordpress-helpdesk-openai-chat-button', function(e) {
               
                e.preventDefault();

                var $this = $(this);
                var originalButtonText = $this.text();
                $this.text('Loading ...');

                $.ajax({
                    type : 'post',
                    url : that.settings.ajax_url,
                    dataType : 'json',
                    data : {
                        message : $('.wordpress-helpdesk-single-ticket-description').text(),
                        action : 'helpdesk_openai_get_message'
                    },
                    success : function( response ) {

                        $this.text(originalButtonText);

                        if(!response.status) {
                            alert(response.message);
                            return;
                        }

                        var $_editorTextArea = $('#comment');
                        $_editorTextArea.is(':visible') ? $_editorTextArea.val(response.message) : tinyMCE.get('comment').setContent(response.message);
                    }
                 });
            });
        },
        savedReplies : function() {

            var that = this;

            $(document).on('change', '#select-saved-reply', function(e) {
               
                e.preventDefault();

                var option = $(this);
                var replyID = option.val();

                if(replyID.length > 0) {

                    $.ajax({
                        type : 'post',
                        dataType : 'json',
                        url : that.settings.ajax_url,
                        data : {
                            action : 'get_saved_reply',
                            id : replyID,
                        },
                        success : function( response ) {
                            console.log(response);
                            if(response.status == "true") {
                                var $_editorTextArea = $('#comment');
                                $_editorTextArea.is(':visible') ? $_editorTextArea.val(response.reply.post_content) : tinyMCE.get('comment').setContent(response.reply.post_content);
                            }
                        }
                    });
                }
            });
        },
        
        // FAQ Search
        FAQRating : function () {

            var that = this;
            var likeButton = $('.wordpress-helpdesk-faq-rating-like');
            var likeCount = $('#wordpress-helpdesk-faq-rating-like-count');
            var dislikeButton = $('.wordpress-helpdesk-faq-rating-dislike');
            var dislikeCount = $('#wordpress-helpdesk-faq-rating-dislike-count');
            
            likeButton.on('click', function(e) {
               
                e.preventDefault();

                var $this = $(this);
                var post_id = $this.data('post_id');
                $.ajax({
                    type : 'post',
                    url : that.settings.ajax_url,
                    dataType : 'json',
                    data : {
                        post_id : post_id,
                        action : 'count_likes'
                    },
                    success : function( response ) {
                        likeCount.text(response);
                    }
                 });
            });

            if(dislikeButton.length > 0) {
                dislikeButton.on('click', function(e) {
                   
                    e.preventDefault();

                    var $this = $(this);
                    var post_id = $this.data('post_id');
                    $.ajax({
                        type : 'post',
                        url : that.settings.ajax_url,
                        dataType : 'json',
                        data : {
                            post_id : post_id,
                            action : 'count_dislikes'
                        },
                        success : function( response ) {
                            dislikeCount.text(response);
                        }
                     });
                });
            }
        },
        desktopNotifications : function () {

            var that = this;
            var showWelcome = that.getCookie('desktopNotificationsShowWelcome');

            if(that.isEmpty(showWelcome)) {
                Push.create(that.settings.desktopNotificationsWelcomeTitle, {
                        body: that.settings.desktopNotificationsWelcomeText,
                        icon: that.settings.desktopNotificationsIcon,
                        timeout: that.settings.desktopNotificationsTimeout,
                });
                that.createCookie('desktopNotificationsShowWelcome', 'false', that.settings.desktopNotificationsWelcomeTimeout);
            }

            $.ajax( {
                type: 'post',
                url: that.settings.ajax_url,
                dataType: 'json',
                data: {
                    action : 'desktop_notifications_get_comment_ids',
                },
                success: function( response ) {
                    if(response.message == "Not logged in") {
                        return false;
                    }
                    that.desktopNotifications.comment_ids = that.desktopNotifications.comment_ids.concat(response.comment_ids)
                    that.desktopNotificationsInterval = setInterval(function(){ that.desktopNotificationsCheck() }, that.settings.desktopNotificationsAJAXInterval);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('An Error Occured: ' + jqXHR.status + ' ' + errorThrown + '! Please contact System Administrator!');
                }
            } );
            
        },
        desktopNotificationsCheck : function() {

            var that = this;
            $.ajax( {
                type: 'post',
                url: that.settings.ajax_url,
                dataType: 'json',
                data: {
                    comment_ids : that.desktopNotifications.comment_ids,
                    action : 'desktop_notifications_get_new_comments',
                },
                success: function( response ) {
                    if(response.status == "false") {
                        return;
                    }
                    that.desktopNotifications.comment_ids.push(response.comment_id);

                    Push.create(response.title, {
                        body: response.body,
                        link: response.link,
                        icon: that.settings.desktopNotificationsIcon,
                        timeout: that.settings.desktopNotificationsTimeout,
                    });
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('An Error Occured: ' + jqXHR.status + ' ' + errorThrown + '! Please contact System Administrator!');
                }
            } );
        },
        liveChatNew : function() {
            var that = this;

            that.livechat = {};

            that.livechat.trigger = $('#wordpress-helpdesk-livechat-trigger');
            if(that.livechat.trigger.length < 1) {
                return;
            }

            that.liveChatCheckAllowed(function(allowed) {
                if(allowed == false || allowed == 'false') {
                    return;
                }

                that.livechat.trigger.fadeIn();

                that.livechat.content = $('#wordpress-helpdesk-livechat-content');
                that.livechat.close = $('#wordpress-helpdesk-livechat-close');
                that.livechat.title = $('#wordpress-helpdesk-livechat-header-title');
                that.livechat.status = $('#wordpress-helpdesk-livechat-header-status');
                that.livechat.messages = $('#wordpress-helpdesk-livechat-messages');
                that.livechat.welcome = $('#wordpress-helpdesk-livechat-welcome');
                that.livechat.footer = $('#wordpress-helpdesk-livechat-footer');
                that.livechat.comment_form = $('#wordpress-helpdesk-livechat-comment-form');
                that.livechat.error_container = $('#wordpress-helpdesk-livechat-enter-chat-form-error');
                that.livechat.success_container = $('#wordpress-helpdesk-livechat-enter-chat-form-success');
                that.livechat.ticket_form = $('#wordpress-helpdesk-livechat-enter-chat-form');
                that.livechat.chat_messages = $('#wordpress-helpdesk-livechat-chat-messages');

                that.liveChatComment();
                that.liveChatAttachment();
                that.liveChatEnterChatForm();

                that.livechat.trigger.on('click', function(e) {
                    that.livechat.trigger.fadeOut(function() {
                        that.livechat.content.slideDown();
                        that.liveChatCheckStatus();
                    });
                });

                that.livechat.close.on('click', function(e) {
                    that.livechat.content.slideUp(function() {
                        that.livechat.error_container.fadeOut();
                        that.livechat.success_container.fadeOut();
                        that.deleteCookie('wordpress-helpdesk-livechat-ticket');
                        that.comment_ids = [];
                        that.settings.ticket = '';
                        that.livechat.chat_messages.html('');
                        console.log(that.liveChatWatchCommentsInterval);
                        clearInterval(that.liveChatWatchCommentsInterval);
                        that.livechat.trigger.fadeIn();
                        that.livechat.title.text('Live Chat');
                    });
                });

                that.livechat.ticket_cookie = that.getCookie('wordpress-helpdesk-livechat-ticket');
                if(that.livechat.ticket_cookie !== "") {
                    that.settings.ticket = that.livechat.ticket_cookie;
                    that.livechat.trigger.trigger('click');
                }
            });
        },
        liveChatCheckAllowed : function(callback) {
            var that = this;

            $.ajax({
                type : 'post',
                url : that.settings.ajax_url,
                data : {
                    action : 'livechat_frontend_check_allowed'
                },
                success : function( response ) {
                    callback(response);
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log(xhr);
                    console.log(ajaxOptions);
                    console.log(thrownError);
                    callback(false);
                }
             });
        },
        liveChatCheckStatus : function() {

            var that = this;
            $.ajax({
                type : 'post',
                url : that.settings.ajax_url,
                dataType: 'json',
                data : {
                    action : 'livechat_frontend_check_status'
                },
                success : function( response ) {

                    that.livechat.status.text(response.status);
                    that.livechat.welcome.html(response.welcome);
                    that.livechat.ticket_form.html(response.enter_chat_fields);

                    // Check Ticket Cookie
                    if(!(that.isEmpty(that.settings.ticket))) {
                        that.liveChatGetTicket(true);
                    } else {
                        $('.wordpress-helpdesk-livechat-enter-chat').fadeIn();
                    }
                }
             });
        },
        liveChatEnterChatForm : function() {

            var that = this;

            that.livechat.ticket_form.on('submit', function(e) {
                e.preventDefault();

                that.livechat.ticket_form_username = that.livechat.ticket_form.find('input[name="helpdesk_username"]');
                that.livechat.ticket_form_email = that.livechat.ticket_form.find('input[name="helpdesk_email"]');
                that.livechat.ticket_form_subject = that.livechat.ticket_form.find('input[name="helpdesk_subject"]');
                that.livechat.ticket_form_message = that.livechat.ticket_form.find('input[name="helpdesk_message"]');
                that.livechat.ticket_form_ticket = that.livechat.ticket_form.find('input[name="ticket"]');

                if(that.livechat.ticket_form_ticket.length > 0 && !that.isEmpty(that.livechat.ticket_form_ticket.val())) {
                    that.settings.ticket = that.livechat.ticket_form_ticket.val();
                    that.liveChatGetTicket(true);
                    // that.setOpenChat(that.settings.ticket);
                    // that.liveChatCheckClosedInterval = setInterval(function(){ that.liveChatWatchClosed() }, that.settings.liveChatAJAXInterval);
                } else {

                    if(that.livechat.ticket_form_username.length > 0 && that.isEmpty(that.livechat.ticket_form_username.val())) {
                        that.livechat.ticket_form_username.css('border-color', 'red');
                        return;
                    } else {
                        that.livechat.ticket_form_username.css('border-color', 'none');
                    }

                    if(that.livechat.ticket_form_email.length > 0 && that.isEmpty(that.livechat.ticket_form_email.val())) {
                        that.livechat.ticket_form_email.css('border-color', 'red');
                        return;
                    } else {
                        that.livechat.ticket_form_email.css('border-color', 'none');
                    }

                    if(that.livechat.ticket_form_subject.length > 0 && that.isEmpty(that.livechat.ticket_form_subject.val())) {
                        that.livechat.ticket_form_subject.css('border-color', 'red');
                        return;
                    } else {
                        that.livechat.ticket_form_subject.css('border-color', 'none');
                    }

                    if(that.livechat.ticket_form_message.length > 0 && that.isEmpty(that.livechat.ticket_form_message.val())) {
                        that.livechat.ticket_form_message.css('border-color', 'red');
                        return;
                    } else {
                        that.livechat.ticket_form_message.css('border-color', 'none');
                    }

                    $.ajax( {
                        type: 'post',
                        url: that.settings.ajax_url,
                        dataType: 'json',
                        data: {
                            helpdeskTicket : 'Chat',
                            helpdesk_username : that.livechat.ticket_form_username.val(),
                            helpdesk_email : that.livechat.ticket_form_email.val(),
                            helpdesk_subject : that.livechat.ticket_form_subject.val(),
                            helpdesk_message : that.livechat.ticket_form_message.val(),
                            action : 'livechat_frontend_create_ticket',
                        },
                        success: function( response ) {
                            if(response.status == "true") {
                                that.livechat.error_container.fadeOut();
                                that.livechat.success_container.html(response.message).fadeIn();
                                that.settings.ticket = response.ticket;

                                that.liveChatGetTicket(true);
                            } else {
                                that.livechat.success_container.fadeOut();
                                that.livechat.error_container.html(response.message).fadeIn();
                                return false;
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.log('An Error Occured: ' + jqXHR.status + ' ' + errorThrown + '! Please contact System Administrator!');
                        }
                    } );
                }
            });
        },
        liveChatComment : function() {

            var that = this;

            that.livechat.comment_form.on('submit', function(e) {
                e.preventDefault();

                var message = that.livechat.comment_form.find('input[name="helpdesk_message"]');

                if(message.length > 0 && that.isEmpty(message.val())) {
                    message.css('border-color', 'red');
                    return;
                }

                $.ajax( {
                    type: 'post',
                    url: that.settings.ajax_url,
                    dataType: 'json',
                    data: {
                        helpdesk_post_id: that.settings.ticket,
                        helpdesk_message : message.val(),
                        action : 'livechat_frontend_comment_ticket',
                    },
                    success: function( response ) {
                        if(response.status == "true") {
                            message.val('');
                        } else {
                            console.log('An error occured');
                            console.log(response);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log('An Error Occured: ' + jqXHR.status + ' ' + errorThrown + '! Please contact System Administrator!');
                    }
                } );
            });
        },
        liveChatAttachment : function() {

            var that = this;
            var attachmentInput = that.livechat.comment_form.find('#helpdesk_attachment');            

            attachmentInput.on('change', function(event) {

                clearInterval(that.liveChatWatchCommentsInterval);

                event.stopPropagation(); // Stop stuff happening
                event.preventDefault(); // Totally stop stuff happening

                // START A LOADING SPINNER HERE
                $('.wordpress-helpdesk-livechat-comment-form-attachment').find('.fa').removeClass('fa-paperclip').addClass('fa-spinner fa-spin');

                // Create a formdata object and add the files
                var data = new FormData();
                data.append('helpdesk-attachments[]', $(this)[0].files[0]);
                data.append('action', 'livechat_frontend_upload_file');
                data.append('ticket', that.settings.ticket)

                $.ajax({
                    url: that.settings.ajax_url,
                    type: 'POST',
                    data: data,
                    cache: false,
                    dataType: 'json',
                    processData: false, // Don't process the files
                    contentType: false, // Set content type to false as jQuery will tell the server its a query string request
                    success: function(data, textStatus, jqXHR)
                    {
                        $('.wordpress-helpdesk-livechat-comment-form-attachment').find('.fa').addClass('fa-paperclip').removeClass('fa-spinner fa-spin');
                        that.liveChatWatchCommentsInterval = setInterval(function(){ that.liveChatWatchComments() }, that.settings.liveChatAJAXInterval);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        $('.wordpress-helpdesk-livechat-comment-form-attachment').find('.fa').addClass('fa-paperclip').removeClass('fa-spinner fa-spin');
                        console.log(jqXHR);
                        console.log(textStatus);
                    }
                });
            });
        },
        liveChatGetTicket : function(all) {

            all = all || false;

            var that = this;
            that.livechat.chat_messages.html('');
            $.ajax( {  
                type: 'post',
                url: that.settings.ajax_url,
                dataType: 'json',
                data: {
                    comment_ids : that.comment_ids,
                    all : all,
                    ticket : that.settings.ticket,
                    action : 'livechat_frontend_get_ticket',
                },
                success: function( response ) {
                    if(response.status === "false") {
                        clearInterval(that.liveChatWatchCommentsInterval)
                        that.livechat.error_container.html(response.error).fadeIn();
                        return false;
                    }
                    that.livechat.title.text(response.title)
                    $('.wordpress-helpdesk-livechat-enter-chat').fadeOut();

                    that.createCookie('wordpress-helpdesk-livechat-ticket', that.settings.ticket, 10);

                    that.livechat.footer.fadeIn();

                    // Watch Comment field
                    that.liveChatWatchCommentsInterval = setInterval(function(){ that.liveChatWatchComments() }, that.settings.liveChatAJAXInterval);
                    console.log(that.liveChatWatchCommentsInterval);
                    that.comment_ids = that.comment_ids.concat(response.comment_ids).filter( that.onlyUnique );


                    if(Object.keys(response.chat).length === 0) {
                        return false;
                    }

                    that.liveChatRenderChatMessages(response.chat);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('An Error Occured: ' + jqXHR.status + ' ' + errorThrown + '! Please contact System Administrator!');
                }
            });
        },
        liveChatRenderChatMessages : function(messages) {

            var html = "";
            var that = this;

            for (var i = 0; i < messages.length; i++) {
                
                var agentCSS = "";
                if(messages[i].agent == true) {
                    var agentCSS = "wordpress-helpdesk-livechat-message-agent";
                }
                html += '<div class="wordpress-helpdesk-livechat-message-container wordpress-helpdesk-clearfix ' + agentCSS + '">' +
                            '<div class="wordpress-helpdesk-livechat-author">'+
                                '<img src="' + messages[i]['author_img'] +'" class="wordpress-helpdesk-livechat-author-image">' +
                                '<span class="wordpress-helpdesk-livechat-author-name">' + messages[i]['author_name'] +'</span>' + 
                            '</div>' +
                            '<div class="wordpress-helpdesk-livechat-message">';

                if(messages[i]['attachment_thumb'] !== "") {
                    html +=    '<div class="wordpress-helpdesk-livechat-attachment">' +
                                    '<a href="' + messages[i]['attachment_url'] + '" class="wordpress-helpdesk-livechat-attachment-link">' +
                                        '<img src="' + messages[i]['attachment_thumb'] + '" class="wordpress-helpdesk-livechat-attachment-thumb" />' +
                                    '</a>' +
                                '</div>';
                }
                                 // '<span class="chat-time">' + messages[i]['time'] + '</span>' +
                html +=         messages[i]['content'] + 
                             '</div>' +
                         '</div>';
                         // '<hr>';
            }
            that.livechat.chat_messages.append(html);

            if(typeof LuminousGallery !== 'undefined') {
                new LuminousGallery($('.wordpress-helpdesk-livechat-attachment a'), {}, {
                    caption: function(trigger) {
                        return trigger.querySelector('img').getAttribute('alt');
                    }
                });
            }

            $('#wordpress-helpdesk-livechat-messages').animate({ scrollTop: $('#wordpress-helpdesk-livechat-messages').prop("scrollHeight")}, 1000);
        },
        liveChatWatchComments : function() {

            var that = this;
            $.ajax( {  
                type: 'post',
                url: that.settings.ajax_url,
                dataType: 'json',
                data: {
                    comment_ids : that.comment_ids,
                    ticket : that.settings.ticket,
                    action : 'livechat_frontend_get_comments',
                },
                success: function( response ) {
                    if(response.status === "false") {
                        clearInterval(that.liveChatWatchComments)
                        that.livechat.error_container.html(response.error).fadeIn();
                        return false;
                    }
                    
                    that.comment_ids = that.comment_ids.concat(response.comment_ids).filter( that.onlyUnique );

                    if(Object.keys(response.chat).length === 0) {
                        return false;
                    }
                    that.liveChatRenderChatMessages(response.chat);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('An Error Occured: ' + jqXHR.status + ' ' + errorThrown + '! Please contact System Administrator!');
                }
            });
        },
        onlyUnique : function(value, index, self) { 
            return self.indexOf(value) === index;
        },
        //////////////////////
        ///Helper Functions///
        //////////////////////
        isEmpty: function(obj) {

            if (obj == null)        return true;
            if (obj.length > 0)     return false;
            if (obj.length === 0)   return true;

            for (var key in obj) {
                if (hasOwnProperty.call(obj, key)) return false;
            }

            return true;
        },
        sprintf: function parse(str) {
            var args = [].slice.call(arguments, 1),
                i = 0;

            return str.replace(/%s/g, function() {
                return args[i++];

            });
        },
        getCookie: function(cname) {
            var name = cname + "=";
            var ca = document.cookie.split(';');
            for(var i=0; i<ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0)==' ') c = c.substring(1);
                if (c.indexOf(name) === 0) return c.substring(name.length, c.length);
            }
            return "";
        },
        createCookie: function(name, value, minutes) {
            var expires = "";

            if (minutes) {
                var date = new Date();
                date.setTime(date.getTime()+(minutes * 60 * 1000));
                var expires = "; expires="+date.toGMTString();
            }

            document.cookie = name + "=" + value+expires + "; path=/";
        },
        deleteCookie: function(name) {
            this.createCookie(name, '', -10);
        }
    } );

    // Constructor wrapper
    $.fn[ pluginName ] = function( options ) {
        return this.each( function() {
            if ( !$.data( this, "plugin_" + pluginName ) ) {
                $.data( this, "plugin_" +
                    pluginName, new Plugin( this, options ) );
            }
        } );
    };

    $(document).ready(function() {

        $( "body" ).helpdesk( 
            helpdesk_options
        );

    } );

})( jQuery );