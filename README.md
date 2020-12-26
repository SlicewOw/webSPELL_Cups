# webSPELL_Cups
Cup script for webSPELL NG (see https://github.com/SlicewOw/webSPELL_NG) written by SlicewOw (slicewow(at)cup-addon.de) for myRisk Gaming e.V.

Feel free to use this add-on for webSPELL NOR, but be aware that we (myRisk e.V) are not responsible for any problems.

# Demo

* https://demo.cup-addon.de/

# Precondition

* webSPELL NG is installed

# Installation
* {WiP}
* Install cup add-on
    * https://[YOUR_URL]/install-cup-addon.php
    * https://[YOUR_URL]/install-streams-addon.php
* webSPELL settings (as logged in user in admincenter)
    * make sure, the homepage url contains http/-s as prefix - otherwise you may notice broken images
* File settings
    * copy /cup/settings.php.sample and remove .sample
    * Steam API Key: fill in Steam API Key
    * Twitch API Key
    * Discord API Key
        * Create an application: https://discordapp.com/developers/applications/
        * Copy "client id" and "client secret" and paste it to settings.php
        * IMPORTANT: your hp_url must have http/https in front as a prefix!
        * Go to menu "OAuth2" and enter your "redirect" URL in the given page
    * Challonge API Key
        * Create a developer key: https://challonge.com/de/settings/developer (you need to be registered and logged in first)
        * Copy the API key and paste it to settings.php as a value of variable '$challonge_api_key'

# Communication

To talk about the add-on, I created a Slack channel. You may have some question to be answered:

* https://webspellcup-addon.slack.com
* IMPORTANT: You need to contact me in order to receive an invite!

# Optional stuff

* Cronjobs:
    * /cup/cj/cup_cj.php (once a day is enough)
    * /cup/cj/gameaccount_cj.php (should be every hour once)
    * /cup/cj/twitch_cj.php?cj_id={1-5} (every 5 minutes, use 5 cronjobs from 1 up to 5)
        * Notice: you have 5 cronjobs in the end to update stream details!
