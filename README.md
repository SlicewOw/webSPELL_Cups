# webSPELL_Cups
Cup script for webSPELL NOR (see https://github.com/webSPELL-NOR/webSPELL-NOR) written by SlicewOw (slicewow(at)myrisk-gaming.de) for myRisk Gaming e.V.

Feel free to use this add-on for webSPELL NOR, but be aware that we (myRisk e.V) are not responsible for any problems.

# Demo

* https://cup-addon.slicewow.de/

# Precondition

* webSPELL NOR is installed

# Installation
* {WiP}
* File settings
    * copy /cup/settings.php.sample and remove .sample
    * Steam API Key: fill in Steam API Key
    * Twitch API Key
* webSPELL settings
    * make sure, the homepage url is with http/-s as prefix - otherwise you may notice broken images

# Optional stuff

* Cronjobs: 
    * /cup/cj/cup_cj.php (once a day is enough)
    * /cup/cj/gameaccount_cj.php (should be every hour once)
    * /cup/cj/twitch_cj.php?cj_id={1-5} (every 5 minutes, use 5 cronjobs from 1 up to 5)

