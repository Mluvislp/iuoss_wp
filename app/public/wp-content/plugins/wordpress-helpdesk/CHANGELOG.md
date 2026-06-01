# Changelog
======
1.8.3
======
- FIX:	Updated composer

======
1.8.2
======
- FIX:	WPML attachments show multiple times
- FIX:	Issue with ITalian language
- FIX:	Updated phpspreadsheet

======
1.8.1
======
- FIX:	Default agents by department not working
- FIX:	Ticket exporter not working
- FIX:	Downgraded PSR library
- FIX:	PHP notices

======
1.8.0
======
- NEW:	ChatGPT Support
	https://imgur.com/a/eVOKLyj
- NEW:	Saved replies show on ticket frontend
	https://imgur.com/a/fbRXxTY
- NEW:	Replaced Invisible Recaptcha Integration with Captcha By BestWebForms
	https://imgur.com/a/BLnoz2U
- NEW:	Paramter show_search for faqs shortcode
- NEW:	PHP 8.2 support
- FIX:	Email piping not working correctly for existing users
- FIX:	Updated all vendor codes

======
1.7.31
======
- FIX:	FAQs and settings not showing
- NEW:	Disable comment notify for tickets

======
1.7.30
======
- NEW:	User account gets generated with uniquid when email not exists, but username exists
- FIX:	Load users in admin panel via AJXA
- FIX:	Default agents per department not working

======
1.7.29
======
- NEW:	FAQs are hidden too when access per topic is restricted
- FIX:	PHP Notice
- FIX:	Create ticket for users as agent not working
- FIX:	Improved FR Translations
	Thanks to barreoblique.ca 
- FIX:	Improved performance in backend
- FIX:	Greece translation issue

======
1.7.28
======
- NEW:	Topic Description shows
	https://imgur.com/a/H0TJgOE
- NEW:	Performance improvements
- FIX:	Live chat close icon not visible
- FIX:	Filter by priority in backend not working
- FIX:	Performance issues
- FIX:	Datatables Norwegian Language

======
1.7.27
======
- NEW:	Default Reply template
	https://imgur.com/a/P69JXaU
- NEW:	Custom FAQ topic image icon
	https://imgur.com/a/qRmiBkC
- NEW:	Create custom form field types
	https://imgur.com/a/a6pRP1G
- NEW:	Default Auto Reply
	https://imgur.com/a/d9RvnRC
- NEW:	Custom text before submit button
	https://imgur.com/a/6NyuEPy
- NEW:	Added CSS classes to my tickets table
- FIX:	POT strings missing
- FIX:	Saved Replies not working
- FIX:	WP recent comment widget showed ticket replies


======
1.7.26.1
======
- FIX:	Admin bar showed for guests

======
1.7.26
======
- NEW:	Added support for our Ultimate Tabs plugin
	https://imgur.com/a/C6zr900
- NEW:	Customers removed admin bar too now
- FIX:	Removed the activity dashboard 

======
1.7.25
======
- NEW:	Show all FAQs as Accordion
	https://imgur.com/a/0cGbZ4O
- NEW:	Show WooCommerce FAQs as accordion
	https://imgur.com/a/Zr2UVHW
- NEW:	Added autocomplete off to ticket forms 
- NEW:	User Role FAQ Access
	https://imgur.com/a/QsmmUhWc
- NEW:	User Role FAQ Topic Access
	https://imgur.com/a/oRNHkAx
- FIX:	Hidden FAQs show in WooCommerce products
- FIX:	Error fixed in IT-Rechtskanzlei plugin
- FIX:	Accordion icon not aligned when faq title went over 2 lines

======
1.7.24
======
- FIX:	Ticket reply in backend (comment form) not working

======
1.7.23
======
- NEW:	New field for dynamic FAQ posts widget
	Dynamic Title
- NEW:	Single ticket rating form
	https://postimg.cc/gallery/m77sR7T	
- NEW:	When both Woo form options are disabled, it will
	hide the first box select row
- NEW:	Load saved reply in ticket backend
	https://imgur.com/a/lZ2gceD
- FIX:	Agents did not see own created tickets
- FIX:	Flatsome fatal error by setting unset $_POST
- FIX:	Added wpautop to new replies mail
- FIX:	Comments closed even if option not enabled
- FIX:	Security for feedback form

======
1.7.22
======
- NEW:	Default sort order for my tickets on ticket ID DESC
- FIX:	Improved Sorting by date and ticket ID in my tickets
- FIX:	Single FAQ Schema issue
- FIX:	Removed tags from Schema.org data

======
1.7.21
======
- NEW:	Added schema.org support (rich snippet data) to FAQ Archives & Pages
	https://imgur.com/a/StmHFfg
- FIX:	get_sites function not available

======
1.7.20
======
- NEW:	Added Gutenberg support for all Post types
- NEW:	Added 3 new parameters to faqs shortcode:
	accordion
	show_faq_icon
	faq_columns
	https://imgur.com/a/AYDTUqD
	Live: https://demos.welaunch.io/wordpress-helpdesk/accordion-faqs/
- FIX:	Removed Databales CDN loading
- FIX:	Multisite register roles globally

======
1.7.19
======
- FIX:	Comments not visible when ticket closed
- FIX:	Comments not showing in DIVI theme

======
1.7.18
======
- NEW:	Close comments when a ticket is marked as solved / closed
		https://imgur.com/a/Ndim1Rw
- NEW:	Updated all translations
- FIX:	Updated font awesome in backend also
- FIX:	Data privacy checkbox not removed when deactivated
- FIX:	Removed the login URL filter as admins cant login when no page was set

======
1.7.17
======
- FIX:	Font awesome updated to v5

======
1.7.16
======
- NEW:	Dispaly Website URL in ticket frontend
- FIX:	product removed after single ticket backend saved

======
1.7.15
======
- NEW:	WooCommerce Order & Product & Envato Item display
		in single ticket page
		https://imgur.com/a/3OyBsii
- NEW:	Set order subjects yourself
		https://imgur.com/a/Wq90R6U
- FIX:	Huge performance increase when you used WooCommerce
- FIX:	Website URL for woo form fields disabled by default
- FIX:	Added wp_kses to message field in checkbox
- FIX:	Moved Data privacy checkbox below message field

======
1.7.14
======
- NEW:	Data privacy checkbox for all new ticket forms (GDPR)
		https://imgur.com/a/WMYWKN7
- NEW:	Updated translation files
- FIX:	PHP notices

======
1.7.13
======
- NEW:	Dropped Redux Framework support and added our own framework 
		Read more here: https://www.welaunch.io/en/2021/01/switching-from-redux-to-our-own-framework
		This ensure auto updates & removes all gutenberg stuff
		You can delete Redux (if not used somewhere else) afterwards
		https://www.welaunch.io/updates/welaunch-framework.zip
		https://imgur.com/a/BIBz6kz

======
1.7.12
======
- NEW:	Advanced settings > disable font awesome loading
- FIX:	JS / CSS loading when not enabled services
- FIX:	From now on all shortcodes will be removed from new tickets

======
1.7.11
======
- FIX:	User creation with empty mail / username
- FIX:	Had to drop Slack Integration due to the PHP package is now maintained
		Looking for another solution

======
1.7.10
======
- NEW:	Send feedback message + satisfied to agent
		https://imgur.com/a/R6u02NY

======
1.7.9
======
- NEW:	Agents can close tickets in fronted
- FIX:	PHP notice in faq post type

======
1.7.8
======
- NEW:	Added time caching for inbox + automatically close tickets
- FIX:	Custom fields not showing in single ticket
- FIX:	Wrong custom field validation for Envato or  

======
1.7.7
======
- NEW:	Big Performance Release 
		!! MAKE SURE YOU ARE ON LATEST VERSION OF REDUX FRAMEWORK !!
		
- FIX:	Translations updated

======
1.7.6
======
- FIX:	Create ticket "not set"
- FIX:	My Tickets title too long did not break

======
1.7.5
======
- NEW:	FAQ topics URL contains parent category in URL now
- NEW:	faqs shortcode now can contain multiple categories COMMA separated
- FIX:	INBOX PHP fatal error
- FIX:	Single FAQ back to did not show correct last FAQ topic
- FIX:	CSS issue in Flatsome with widget
- FIX:	Margin missing when using fab icon
- FIX:	CSS issue with not aligned items

======
1.7.4
======
- NEW:	Custom separate fields for WooCommerce, Simpel & Envato ticket form
- NEW:	Show title in faq topics
- NEW:	2 new Options to disable / enable show topic children or categories
- NEW:	Hide FAQs if subtopics exists
- NEW:	Better flatsome support
- NEW:	Multiple new Arguments for faqs shortcode:
        topic = '',
        show_topic_title = 'false',
        show_back_to_parent_topic = 'false',
        content = 'false',
        excerpt = 'true',
        link = 'true',
        max_faqs = '-1',
        show_children = 'false',
        hide_faqs_when_subcategories_exists = 'false',
        show_child_categories = 'true',
        columns = $this->get_option('FAQColumns'),
        order = 'ASC',
        orderby = 'menu_order',
- FIX:	Search icon fix in flatsome
- FIX:	Wrong CSS Class for before FAQs
- FIX:	Tickets in My Account of WooCommerce could not be submitted

======
1.7.3
======
- FIX:	Mail fetching not working when custom required fields created

======
1.7.2
======
- FIX:	Added array filter for required / optional new fields
		Ticket could not be created! not set!

======
1.7.1
======
- NEW:	Agents can create tickets on behalf of customers in the new ticket page:
		https://imgur.com/a/jr898qx
- NEW:	FAQ search also searches in comments: https://imgur.com/a/exeDbm9
- NEW:	Cleaned the plugin settings / moved fields
- NEW:	WooCommerce FAQ integration now can use excerpt + links. Also columns can be set now:
		https://imgur.com/a/ZUs6yHR
- NEW:	Custom fields will also be exported in Excel file
- NEW:	Topics widget can hide / show empty topics and display subcategories
		https://imgur.com/a/jtskEiH
- NEW:	Add / Create content before FAQs or Tickets
		https://imgur.com/a/g2vlVtS
- FIX:	Live Search not working in sidebar when disabled in plugin settings
- FIX:	when new ticket got an error, previous filled out fields were gone
- FIX:	Live chat could not be started because of custom fields missing
- FIX:	Attachment lightbox hidden behind chat
- FIX:	Updated POT file

======
1.7.0 
======
- NEW:	Custom Ticket Input Fields 
		You can now create custom text fields (required or optional)
		within plugin settings
		Example: https://imgur.com/a/nv4CKq0
- FIX:	Added required attribute to input fields

======
1.6.4
======
- NEW:	Added support for video uploads when creating new tickets

======
1.6.3
======
- NEW:	Added responsive feature for my tickets table

======
1.6.2
======
- FIX:	Closing div missing in knowledge base shortcode

======
1.6.1
======
- FIX:	Font Awesome not found issue

======
1.6.0
======
- NEW:	Added support for all other attachment types
- NEW:	Added security checkes for attachments

======
1.5.24
======
- FIX:	Removed Gutenberg testing code

======
1.5.23
======
- FIX:	Ticket ID placeholder not translateable
- FIX:	Update POT files
- FIX:	Adding links in ticket comments showed posts / pages search
- FIX:	New Ticket message form showed media upload button

======
1.5.22
======
- FIX:	Save custom meta box PHP error

======
1.5.21
======
- FIX:	Users with custom roles saw all tickets in the my tickets page

======
1.5.20
======
- FIX:	Responsive Issues
- FIX:	Umlaute not working in username field

======
1.5.19
======
- FIX:	Performance Increase
- FIX:	WPML for My Tickets shows other languages

======
1.5.18
======
- FIX:	JS not loaded due to datatables

======
1.5.17
======
- NEW:	Order field for FAQs
- FIX:	Renamed Rewrite Slug for saved replies caused post tags to 404 

======
1.5.16
======
- FIX:	Attachments not merged
- FIX:	FAQ Sidebar order by likes / dislikes

======
1.5.15
======
- NEW:	Moved all CDN loaded JS / CSS into this plugin folder
		better for autoptimize plugin

======
1.5.14
======
- NEW:	You can now disable datatables and set a language
		Plugin Settings > Tickets > Enable Datatables
- NEW:	Attachments in Backend now also open in Lightbox

======
1.5.13
======
- NEW:	Facebook Messenger Support
		-> Follow this link > Setting up the Plugin to get the code
		https://developers.facebook.com/docs/messenger-platform/discovery/customer-chat-plugin#steps
- NEW:	FAQ search now also appears on FAQ topics
- NEW:	FAQ search now start at 2 chars, not 3

======
1.5.12
======
- FIX:	FAQ wrong output

======
1.5.11
======
- NEW:	Datatables for the My Tickets page allows:
		- Sorting
		- Searching 
		- Entry Limit
- NEW:	Reports can now close their own tickets
- NEW:	Configure what data you want to show in the My Tickets Table:
		- Show Name
		- Show Date
		- Show Status
		- Show System
		- Show Type
- NEW:	Set FAQ Columns in a Topic Archive
		e.g. show 2 faqs next to each other
- INFO:	We will no longer develop the Desktop Notification feature (but it stays)
		We developed a new plugin Fire Push: https://www.welaunch.io/fire-push/
		- Background Notfications (when browser is closed)
		- send notification on new ticket replies
		- notfiy on new tickets created
		- message on tickets updated
- FIX:	Desktop Notifications

======
1.5.10
======
- NEW:	Button in Live FAQ result list if more than 4 results
- NEW:	Loading spinner replacing the search icon for FAQ search when typing
- NEW:	FAQ Live Search does not submit on enter
- NEW:	Option to set Maximum Live FAQ search results
- FIX:	Removed ID from FAQ search because of duplicate ID Issue
- FIX:	Added 404 Error code to wp_die function
		This prevents nginx from caching while not logged in users want to see tickets
- FIX:	Added higher z-index to chat trigger & message containers

======
1.5.9
======
- NEW:	FAQ, Topics & Tickets are not Mobile first
		Means the Title is Displayed first on Mobile (Better SEO)
- NEW:	New Tickets Count in Backend (Red Badge)
- NEW:	Automatically set Tickets to close / solved after X Days no comment / update was made
		> See General Options
- NEW:	Set a default Solved (Closed) Status in Default Settings
		Used for Close Tickets after X Days
- FIX:	FAQ Search Causing PHP Notice

======
1.5.8
======
- NEW:	Reorder Admin Panel Section alpabetically
- FIX:	Removed TGM Plugin (causing too many errors)

======
1.5.7
======
- FIX:	Inbox Mails, that could not be processed as a ticket will still 
		be marked as read, but not moved to archive folder. This prevents
		infinite loop of email fetching
- FIX:	Added more Email Inbox Message validations

======
1.5.6
======
- NEW:	Inbox will now always be fetched when logged in
- FIX:	Cronjob not running 
		To make sure the cronjob runs you can use a plugin called WP Crontrol

======
1.5.5
======
- NEW:	Video about our new Automatic Reply Feature: https://youtu.be/RR77Dwvqch0
- FIX:	Comments will not be checked for automatic replies when comment user is the same
		as the automatic reply user. This prevents infinite loop, but also suggest you 
		to better create an own user for Automatic replies (like BOT)

======
1.5.4
======
- NEW:	Decreased ticket avatar image
- NEW:	Automatic Replies (Bot) 
		Saved Replies can be used for automatic replies. You need
		to add tags to your saved replies. These will be matched against
		words from new tickets / replies.
		See Settings > Saved Replies
- FIX:	Updated options texts
- FIX:	Sidebar will show even when no tickets were submitted yet
- FIX:	Envato Purchase code validation in Backend
- FIX:	Show No agents assigned in Frontend
- FIX:	CSS tweaks
- FIX:	Vendor Packages updated

======
1.5.3
======
- NEW:  Shortcode for FAQ Search
		[[faq_search]]

======
1.5.2
======
- FIX:	Finished RU Translations
- FIX:	Chat on Mobile to near on the left

======
1.5.1
======
- NEW:  Russian Translation added
		If you have any other languages translated please send to us
- FIX:  IE11 Bug with JS
- FIX:  Missing Translations
- FIX:  Cleaned up translations files

======
1.5.0
======
- NEW:	Welcome Livechat 2.0
		- Watch Demo Video: https://youtu.be/g48IW8Qe2JM
		- Improved Design
		- New Frontend
		- New Backend for agents
		- Attachment upload possible
- NEW:	Hide Livechat when agents offline
- NEW:	Allow Attachments in Livechat
- NEW:  Filter wordpress_helpdesk_livechat_allowed
- NEW:  Added Lightbox feature for Comment Attachments


======
1.4.8
======
- FIX:	JS Gallery Error
- FIX:	WPML issue with FAQ Widgets

======
1.4.7
======
- FIX:  Agent could not be updated

======
1.4.6
======
- FIX:  Tax Meta Class Updated

======
1.4.5
======
- NEW:  Set department, type or priorty for ticket shortcode
		Example: [[new_ticket departments="24,25,17" priorities="30" types="12"]]
- FIX:  Updated Translations

======
1.4.4
======
- NEW:	Private Notes
		Add private Notes for ticktes inside the backend
- NEW:  Add Media Files inside Replies in Comments
- FIX:  Code Improvements
- FIX:  Translations for Default Departments

======
1.4.3
======
- NEW: 	Assign default agents by Departments 
		Settings > Defaults
- NEW:  FAQ Search Term Tracking (term, search counts, articles found)		
		See: wp-admin/edit.php?post_type=ticket&page=helpdesk-faq-terms
- NEW:  Merge Tickets 
		See: Single Edit Ticket in WP-admin sidebar bottom
- FIX:  Default post set fix
- FIX: 	Reporter user check removed as it caused issues with INBOX

======
1.4.2
======
- NEW: Added German Translation (status 15%)
- NEW: Added Spanish Translation (status 26%)
- FIX: translation files updated
- FIX: Ticket string not translated

======
1.4.1
======
- FIX:  Only show agents, admins and shop managers in agent list
- FIX:  Responsive My Tickets Page

======
1.4.0
======
- FIX:  Translation for account not created

======
1.3.9
======
- FIX:  Envato purchase code validation

======
1.3.8
======
- NEW: 	Added two new actions for the new ticket form:
		wordpress_helpdesk_before_new_ticket_form
		wordpress_helpdesk_after_new_ticket_form
- NEW:  Added French Translation

======
1.3.7
======
- FIX:  Updated translations

======
1.3.6
======
- NEW:  New ticket message field now supports the WSYIG editor

======
1.3.5
======
- FIX:  Issue where ticket comment form influenced other comment form

======
1.3.4
======
- FIX:  Admin CSS issue

======
1.3.3
======
- NEW:  Topic Widget for Sidebar
- NEW:  FAQ for WPMl Translation: 
		https://www.welaunch.io/helpdesk/documentation/faq/can-translate-plugin-wpml/
- FIX:  Missing div in form shortcode
- FIX:  My Tickets page on WooCommerce account page
- FIX:  Product support in WPML environment showed all products
- FIX:  custom WooCommerce order subjects filter did not work

======
1.3.2
======
- NEW:  Moved type / Project directly under Department
- NEW:  Created a back to my tickets button on new / view single ticket pages
- FIX:  WooCommerce form was not 100% width

======
1.3.1
======
- NEW:  Sidebar for my_tickets and knowledge_base now displays 
		automatically. This has been done, because not all themes
		are able to select a custom sidebar for only 1 page
- NEW:  Set a sidebar display option (none, only on FAQ OR only on Ticket pages, both)
- NEW:  Option to disable the sending of login credentials for new users
- NEW:  Hide FAQ topics for not logged in users
- FIX:  Fix where attachments file select showed up even it was disabled

======
1.3.0
======
- NEW:  IP check for FAQ likes / dislikes to avoid double votes

======
1.2.9
======
- FIX:  User exists not triggers an error to login
		This prevents users to create tickets for 
		other users
- FIX:  Added livechat texts to WPML config

======
1.2.8
======
- NEW:  Livechat guest ticket option readded
- NEW:  Livechat restyled 
		Differnet text align and background color between reporters & agents
- NEW:  The following Livechat texts can be set in the admin panel:
		Live Chat Title
		Live Chat Welcome Text
		Live Chat No Agents Online Text
		Live Chat Button Text
- FIX:  Livechat Integrations moved to Integrations Section

======
1.2.7
======
- FIX: FAQ issue
- FIX: IMAP fetching
- FIX: Dashboard PHP Notice

======
1.2.6
======
- FIX: FAQ Ajax showed the same excerpt

======
1.2.5
======
NEW: Crisp Live Chat Integration
	 Learn more here: https://crisp.chat/en/
NEW: PureChat Live Chat Integration
	 Learn more here: https://www.purechat.com/
NEW: Chatra Live Chat Integration
	 Learn more here: https://chatra.io

======
1.2.4
======
- NEW:  Set a default priority
- NEW:  Show priority in forms for reporters
- NEW:  Priorities now in export
- NEW:  Tickets by priority added to reports
- NEW:  Tickets by Source added to reports
- NEW:  Small layout review of reports page
- NEW:  Added a row for grouping in Ticket-Export Excel file
- FIX:  Attachments were not assigned
- FIX:  Missing Translations

======
1.2.3
======
- FIX:  Removed Cron after disabling Inbox

======
1.2.2
======
- NEW:  PHP IMAP Extension Check
- FIX:  Missing translation & POT file updated
- FIX:  Removed livechat for guest users 

======
1.2.1
======
- FIX:  Added missing translation strings

======
1.2.0
======
- NEW:  Support Rating Feedback
		Ask reporters for Support feedback after a ticket has been solved
		See settings > Support Rating
		Rating Email: https://www.welaunch.io/helpdesk/wp-content/uploads/sites/5/2017/10/support-rating-email.png
		Support Feedback: https://www.welaunch.io/helpdesk/wp-content/uploads/sites/5/2017/10/support-rating-feedback.png
		Rating Overview: https://www.welaunch.io/helpdesk/wp-content/uploads/sites/5/2017/10/support-rating-overview.png
- NEW:  2 x New Report charts: 
		Tickets by Reporter
		Tickets by Satisfaction
- NEW:  Guest (not logged in user) can now use livechat
- FIX:  Layout issue with avatar image in backend
- FIX:  Backend Table width adjustes
- FIX:  PHP notice in loggin class removed

======
1.1.5
======
- FIX: Cronjob issue
- FIX: Ticket not created when no from name was set
- FIX: PHP notice in desktop notifications

======
1.1.4
======
- FIX: Prevent admin access issue

======
1.1.3
======
- NEW:  Connect FAQs to product categories (WooCommerce)
		FAQs will then show up on single product pages
- NEW:  Set AJAX interval for Live Chat & Desktop Notifications
		to decrease server performance use
- FIX:  Prevent Admin Access issue

======
1.1.2
======
- FIX:  logged in error message

======
1.1.1
======
- FIX:  Only 5 FAQs showed up on archive page

======
1.1.0
======
- NEW: 	Option to use original theme template files
		Settings > Advanced Settings > Use Theme Template
		Note: This will remove all custom templates!
		It would be better to copy existing partials into your child theme
		Tutorial: https://www.welaunch.io/helpdesk/documentation/faq/override-templates-theme-support/
- NEW:  Set a sidebar position (left or right) in General Settings
- NEW:  Support for invisible Recaptcha (https://wordpress.org/plugins/invisible-recaptcha/)

======
1.0.9
======
- NEW: 	Show specific FAQs only to logged in users
- NEW: 	Hide FAQs for logged in users from Knowledge Base
		See Settings > Knowledge Base (on bottom)
- FIX:  Slider Revolution issue

======
1.0.8
======
- NEW: option to set novalidate-cert for inbox
- FIX: Issue with My Tickets page and no tickets created so far

======
1.0.7
======
- FIX: Envato Class exists check

======
1.0.6
======
- FIX: Single FAQ only displays title when no topics are created
- FIX: Screen Reader text visibility

======
1.0.5
======
- FIX:  Form 404 Errors
- FIX:  Admins could not trash tickets, faqs or saved replies
- FIX:  DataTables Issue

======
1.0.4
======
- NEW:  Theme support improved (e.g. Dante, Bridge, Total, The7)
- NEW:  Set a custom login page (settings > general)
- FIX:  Added some more translation options
- FIX:  breaking DIV in new ticket form
- FIX:  Output buffering for shortcodes

======
1.0.3
======
- NEW:  Dasboard redirect for reporters & agents
- NEW:  Ticket Priorities now possible
- NEW:  Renamed System / Projects to Departments
- NEW:  Added POT translation file & updated DE Translations
- FIX:  Ticket category color issue in backend
- FIX:  Small php notices

======
1.0.2
======
- NEW:  Rating System for FAQ Articles
		See Options > FAQ > Enable Rating
- NEW:  Disable the dislike button
- NEW:  Widgets now support order by likes / dislikes
- NEW:  Show single FAQs only to logged in users

======
1.0.1
======
- FIX: WooCommerce class misses options
- FIX: Menu does not show up
- FIX: after_widget args notice
- FIX: Livechat count issue
- FIX: Grant Admin all access rights

======
1.0.0
======
- Inital release

Live Chat
-> Agents see list of open chats by reporters
-> Leave message when no agents are online -> Ticket
-> Reporter needs to be logged in
-> Default Welcome Message
-> Enter chat via Ticket ID
-> Create a new chat -> new Ticket
-> Chat history stored as Ticket Comments

Ticket System
-> Inform Agents in Notifications
-> HTML WYSIWIG Comment Form
-> Attachments
-> Saved replies
-> Default Message
-> My Tickets (for reporter & agents)
-> Logging / History system
-> XLS export
-> Agents can reply to Email
-> Ticket Overview (assigned to, status in colors)
-> Set Inbox Folder 
-> Set Inbox Archieve Folder 

Knowledge Base
-> Widget Support
-> Most viewed / loved
-> Set a default order by
-> Custom Icon per category
-> Password Protection (can be set by WP default)
-> Multiple Layout
-> Was this helpful?

Integrations
-> Slack (https://my.slack.com/services/new/incoming-webhook)
-> WooCommerce
-> Envato

======
Future Plans
======
-> Github (https://github.com/KnpLabs/php-github-api)
-> Jira