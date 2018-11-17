<?php
/*
##########################################################################
#                                                                        #
#           Version 4       /                        /   /               #
#          -----------__---/__---__------__----__---/---/-               #
#           | /| /  /___) /   ) (_ `   /   ) /___) /   /                 #
#          _|/_|/__(___ _(___/_(__)___/___/_(___ _/___/___               #
#                       Free Content / Management System                 #
#                                   /                                    #
#                                                                        #
#                                                                        #
#   Copyright 2005-2015 by webspell.org                                  #
#                                                                        #
#   visit webSPELL.org, webspell.info to get webSPELL for free           #
#   - Script runs under the GNU GENERAL PUBLIC LICENSE                   #
#   - It's NOT allowed to remove this copyright-tag                      #
#   -- http://www.fsf.org/licensing/licenses/gpl.html                    #
#                                                                        #
#   Code based on WebSPELL Clanpackage (Michael Gruber - webspell.at),   #
#   Far Development by Development Team - webspell.org                   #
#                                                                        #
#   visit webspell.org                                                   #
#                                                                        #
##########################################################################
*/

$_language->readModule('sponsors');
$mainsponsors = safe_query(
    "SELECT * FROM " . PREFIX . "sponsors
        WHERE (displayed = '1')
        ORDER BY mainsponsor DESC, sort ASC"
);
if (mysqli_num_rows($mainsponsors)) {

    echo '<ul class="list-group">';

    while ($da = mysqli_fetch_array($mainsponsors)) {

        if (!empty($da[ 'banner_small' ])) {
            $sponsor =
                '<img src="images/sponsors/' . $da[ 'banner_small' ] . '" alt="' . htmlspecialchars($da[ 'name' ]) .
                '" class="img-responsive">';
        } else {
            $sponsor = $da[ 'name' ];
        }

        $data_array = array();
        $data_array['$sponsorID'] = $da[ 'sponsorID' ];
        $data_array['$sponsor'] = $sponsor;
        $sc_sponsors_main = $GLOBALS["_template"]->replaceTemplate("sc_sponsors_main", $data_array);
        echo $sc_sponsors_main;
    }

    echo '</ul>';

}
