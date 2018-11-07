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

namespace webspell;

class Language {

    public $language = 'en';
    public $module = array();
    private $language_path = 'languages/';

    public function setLanguage($to, $admin = false, $pluginpath=false) {

        if ($pluginpath) {
            $this->language_path = $pluginpath . $this->language_path;
        }

        if ($admin AND $pluginpath) {
            $this->language_path = __DIR__ . '/../../' . $pluginpath . 'languages/';
        }

        $langs = array();
        foreach (new \DirectoryIterator($this->language_path) as $fileInfo) {
            if ($fileInfo->isDot() === false && $fileInfo->isDir() === true) {
                $langs[] = $fileInfo->getFilename();
            }
        }

        if (in_array($to, $langs)) {
            $this->language = $to;
            $this->language_path = 'languages/';
            return true;
        } else {
            return false;
        }

    }

    public function getRootPath() {
        return $this->language_path;
    }

    public function readModule($module, $add = false, $admin = false, $pluginpath = false) {

        global $default_language;

        if ($pluginpath) {
            $langFolder = __DIR__ . '/../../' . $pluginpath . $this->language_path;
        } else {
            $langFolder = __DIR__ . '/../../' . $this->language_path;
        }

        if ($admin) {
            $folderPath = '%s%s/admin/%s.php';
        } else {
            $folderPath = '%s%s/%s.php';
        }

        $languageFallbackTable = array(
            $this->language,
            $default_language,
            'en'
        );

        $module = str_replace(array('\\', '/', '.'), '', $module);
        foreach ($languageFallbackTable as $folder) {
            $path = sprintf($folderPath, $langFolder, $folder, $module);
            if (file_exists($path)) {
                $module_file = $path;
                break;
            }
        }
        if (!isset($module_file)) {
            return false;
        }
        if (isset($module_file)) {
            include($module_file);
            if (!$add) {
                $this->module = array();
            }
            foreach ($language_array as $key => $val) {
                $this->module[ $key ] = $val;
            }
        }

        if ($admin && ($module != 'convenience')) {
            $this->readModule('convenience', true, true, false);
        }

        return true;
    }
    public function replace($template)
    {
        foreach ($this->module as $key => $val) {
            $template = str_replace('%' . $key . '%', $val, $template);
        }
        return $template;
    }
    public function getTranslationTable()
    {
        $map = array();
        foreach ($this->module as $key => $val) {
            $newKey = '%' . $key . '%';
            $map[ $newKey ] = $val;
        }
        return $map;
    }
}
?>