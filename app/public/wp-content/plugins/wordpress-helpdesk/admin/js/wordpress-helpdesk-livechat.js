(function( $ ) {

	// USE STRICT
    "use strict";

    var livechat = {

        init : function (wordpress_helpdesk_settings) {

        	this.settings = wordpress_helpdesk_settings;

            livechat.liveChat();
        },
        liveChat : function() {

            var that = this;
            
            that.ticketSidebar = $('#wordpress-helpdesk-livechat-sidebar');
            that.comment_ids = [];
            that.fetched_ticket_ids = [];

            that.close = $('#wordpress-helpdesk-livechat-close');
            that.status = $('#wordpress-helpdesk-livechat-header-status');
            that.messages = $('#wordpress-helpdesk-livechat-messages');
            that.welcome = $('#wordpress-helpdesk-livechat-welcome');
            that.footer = $('#wordpress-helpdesk-livechat-footer');
            that.comment_form = $('#wordpress-helpdesk-livechat-comment-form');
            that.error_container = $('#wordpress-helpdesk-livechat-enter-chat-form-error');
            that.ticket_form = $('#wordpress-helpdesk-livechat-enter-chat-form');

            that.getSidebarTickets();
            that.getSingleTicket();
            that.liveChatComment();
            that.liveChatAttachment();
            that.getSidebarTicketsInterval = setInterval(function(){ that.getSidebarTickets() }, that.settings.liveChatAJAXInterval);
        },
        getSidebarTickets : function() {

            var that = this;

            $.ajax( {  
                type: 'post',
                url: that.settings.ajax_url,
                dataType: 'json',
                data: {
                    limit : -1,
                    fetched_ticket_ids : that.fetched_ticket_ids,
                    action : 'livechat_backend_get_tickets',
                },
                success: function( response ) {

                    if(Object.keys(response.tickets).length === 0) {
                        return false;
                    }

                    that.fetched_ticket_ids = that.fetched_ticket_ids.concat(response.fetched_ticket_ids).filter( that.onlyUnique );

                    that.renderSidebarTickets(response.tickets);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('An Error Occured: ' + jqXHR.status + ' ' + errorThrown + '! Please contact System Administrator!');
                }
            });
        },
        renderSidebarTickets : function(tickets) {

            var that = this
            var html = "";
            for (var i = 0; i < tickets.length; i++) {

               html += 
               '<a href="#" class="wordpress-helpdesk-livechat-sidebar-item" data-ticket-id=' + tickets[i]['id'] + '>' + 
                    '<div class="wordpress-helpdesk-row">' + 
                        '<div class="wordpress-helpdesk-col-sm-3">' + 
                            '<img class="wordpress-helpdesk-livechat-sidebar-item-avatar" src="' + tickets[i]['avatar'] + '">' +
                        '</div>' +
                        '<div class="wordpress-helpdesk-col-sm-9">' +
                            '<span class="wordpress-helpdesk-livechat-sidebar-item-title">' + tickets[i]['title'] + '</span>' +
                            '<br><span class="wordpress-helpdesk-livechat-sidebar-item-author">' + tickets[i]['author'] + '</span>' +
                            '<br><span class="wordpress-helpdesk-livechat-sidebar-item-id">Ticket: ' + tickets[i]['id'] + '</span>' +
                        '</div>' +
                    '</div>' +
                '</a><hr>';
            }                     
            that.ticketSidebar.prepend(html);
        },
        getSingleTicket : function() {

            var that = this;
            that.ticketSidebar.on("click", ".wordpress-helpdesk-livechat-sidebar-item", function(e) {

                $('#wordpress-helpdesk-livechat-messages').html('');
                that.comment_ids = [];
                that.settings.ticket = $(this).data('ticket-id');
                clearInterval(that.liveChatWatchCommentsInterval)
                
                $('.wordpress-helpdesk-livechat-sidebar-item').removeClass('wordpress-helpdesk-livechat-sidebar-item-active');
                $(this).addClass('wordpress-helpdesk-livechat-sidebar-item-active');

                $.ajax( {  
                    type: 'post',
                    url: that.settings.ajax_url,
                    dataType: 'json',
                    data: {
                        comment_ids : that.comment_ids,
                        ticket : that.settings.ticket,
                        all: true,
                        action : 'livechat_backend_get_ticket',
                    },
                    success: function( response ) {
                        if(response.status === "false") {
                            that.livechat.error_container.html(response.error).fadeIn();
                            return false;
                        }
                        // $('#wordpress-helpdesk-livechat-header-title').text(response.title)

                        // Watch Comment field
                        that.liveChatWatchCommentsInterval = setInterval(function(){ that.liveChatWatchComments() }, that.settings.liveChatAJAXInterval);

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
            });
        },
        liveChatRenderChatMessages : function(messages) {

            var html = "";
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
            }                     
            $('#wordpress-helpdesk-livechat-messages').append(html);
            if(typeof LuminousGallery !== 'undefined') {
                new LuminousGallery($('.wordpress-helpdesk-livechat-attachment a'), {}, {
                    caption: function(trigger) {
                        return trigger.querySelector('img').getAttribute('alt');
                    }
                });
            }
            console.log($('#wordpress-helpdesk-livechat-messages').prop("scrollHeight"));
            $('#wordpress-helpdesk-livechat-messages').animate({ scrollTop: $('#wordpress-helpdesk-livechat-messages').prop("scrollHeight")}, 1000);
        },
        liveChatComment : function() {

            var that = this;

            that.comment_form.on('submit', function(e) {
                e.preventDefault();

                var message = that.comment_form.find('input[name="helpdesk_message"]');

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
                        action : 'livechat_backend_comment_ticket',
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
            var attachmentInput = that.comment_form.find('#helpdesk_attachment');

            attachmentInput.on('change', function(event) {

                event.stopPropagation(); // Stop stuff happening
                event.preventDefault(); // Totally stop stuff happening

                clearInterval(that.liveChatWatchCommentsInterval);

                // START A LOADING SPINNER HERE
                $('.wordpress-helpdesk-livechat-comment-form-attachment').find('.fa').removeClass('fa-paperclip').addClass('fa-spinner fa-spin');

                // Create a formdata object and add the files
                var data = new FormData();
                data.append('helpdesk-attachments[]', $(this)[0].files[0]);
                data.append('action', 'livechat_backend_upload_file');
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
                        console.log('ERRORS: ' + textStatus);
                    }
                });
            });
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
                    action : 'livechat_backend_get_comments',
                },
                success: function( response ) {
                    if(response.status === "false") {
                        clearInterval(that.liveChatWatchCommentsInterval)
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
    };

    $(document).ready(function() {
    	if( $('body.toplevel_page_helpdesk-livechat').length > 0) {
    		livechat.init(wordpress_helpdesk_settings);
    	}
	});

})( jQuery );
