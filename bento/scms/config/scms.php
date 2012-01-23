<?php
{
  "version":"1.0",
  "state":"not installed",
  "public":{
    "logged_in":false,
    "div":{
      "logged_in":{
        "scms":"scms",
        "facebook":"facebook",
        "twitter":"twitter"
      },
      "not_logged_in":{
        "scms":"no_scms",
        "facebook":"no_facebook",
        "twitter":"no_twitter"
      },
      "no_load":"no_load",
      "timestamp":"scms_timestamp"
    },
    "timestamp":{
      "just_now":"Just now",
      "seconds_ago":"Seconds ago",
      "minute_ago":"Minute ago",
      "minutes_ago":"Minutes ago",
      "hour_ago":"Hour ago",
      "hours_ago":"Hours ago",
      "yesterday":"Just now",
      "days_ago":"Days ago",
      "weeks_ago":"Weeks ago",
      "months_ago":"Months ago"
    },
	"app":{
		"mode":"data",
		"querystring":"app"
	},
	"notification_div":"scms_notification",
	"notifications_div":"scms_notifications"
  },
  "private":{
	"plugins":[],
	"directory":{
			"plugins":"plugins",
			"themes":"themes",
			"templates":"templates",
			"pages":"pages",
			"feeds":"feeds",
			"includes":"includes",
			"help":"help",
			"mail":"mail",
			"admin":"admin",
			"theme":false,
			"page":false,
			"feed":false,
			"include":false
			},
    "facebook":{
      "app_id":"",
      "key":"",
      "secret":"",
      "perms":"email,user_birthday,user_location,user_photos,read_stream,publish_stream,offline_access"
    },
    "twitter":{
      "key":"",
      "secret":""
    },
    "feed_variables":{
      
    },
	"message":{
		"querystring":"bento_scms_message",
		"text":"You have been logged out or your permissions have changed."
	},
    "agents":[
      "facebookexternalhit\/1.1 (+http:\/\/www.facebook.com\/externalhit_uatext.php)"
    ]
  }
}
?>