<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

vimport ('~/libraries/Smarty/libs/SmartyBC.class.php');

class Vtiger_Viewer extends SmartyBC {

	const DEFAULTLAYOUT = 'v7';
	const DEFAULTTHEME  = 'softed';
	static $currentLayout;
	
	// Turn-it on to analyze the data pushed to templates for the request.
	protected static $debugViewer = false;
	
	/**
	 * log message into the file if in debug mode.
	 * @param type $message
	 * @param type $delimiter 
	 */
	protected function log($message, $delimiter="\n") {
		static $file = null;
		if ($file == null) $file = dirname(__FILE__) . '/../../logs/viewer-debug.log';
		if (self::$debugViewer) {
			file_put_contents($file, $message.$delimiter, FILE_APPEND);
		}
	}

	/**
	 * Constructor - Sets the templateDir and compileDir for the Smarty files
	 * @param <String> - $media Layout/Media name
	 */
	function __construct($media='') {
		parent::__construct();

		$THISDIR = dirname(__FILE__);

		$templatesDir = '';
		$compileDir = '';
		if(!empty($media)) {
			self::$currentLayout = $media;
			$templatesDir = $THISDIR . '/../../layouts/'.$media;
			$compileDir = $THISDIR . '/../../test/templates_c/'.$media;
		}
		if(!$templatesDir || !file_exists($templatesDir)) {
			self::$currentLayout = self::getDefaultLayoutName();
			$templatesDir = $THISDIR . '/../../layouts/'.self::getDefaultLayoutName();
			$compileDir = $THISDIR . '/../../test/templates_c/'.self::getDefaultLayoutName();
		}

		if (!file_exists($compileDir)) {
			mkdir($compileDir, 0777, true);
		}
		$this->setTemplateDir(array($templatesDir));
		$this->setCompileDir($compileDir);		

		// FOR SECURITY
		// Escape all {$variable} to overcome XSS
		// We need to use {$variable nofilter} to overcome double escaping
		// TODO: Until we review the use disabled.
		//$this->registerFilter('variable', array($this, 'safeHtmlFilter'));
		
		// FOR DEBUGGING: We need to have this only once.
		static $debugViewerURI = false;
		if (self::$debugViewer && $debugViewerURI === false) {
			$debugViewerURI = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
			if (!empty($_POST)) {
				$debugViewerURI .= '?' . http_build_query($_POST);
			} else {
				$debugViewerURI = $_SERVER['REQUEST_URI'];
			}
			
			$this->log("URI: $debugViewerURI, TYPE: " . $_SERVER['REQUEST_METHOD']);
		}
	}
	
	function safeHtmlFilter($content, $smarty) {
		//return htmlspecialchars($content,ENT_QUOTES,UTF-8);
		// NOTE: to_html is being used as data-extraction depends on this
		// We shall improve this as it plays role across the product.
		return to_html($content);
	}

	/**
	 * Function to get the current layout name
	 * @return <String> - Current layout name if not empty, otherwise Default layout name
	 */
	public static function getLayoutName() {
		if(!empty(self::$currentLayout)) {
			return self::$currentLayout;
		}
		return self::getDefaultLayoutName();
	}

	/**
	 * Function to return for default layout name
	 * @return <String> - Default Layout Name
	 */
	public static function getDefaultLayoutName(){
        return self::DEFAULTLAYOUT;
	}

	/**
	 * Function to get the module specific template path for a given template
	 * @param <String> $templateName
	 * @param <String> $moduleName
	 * @return <String> - Module specific template path if exists, otherwise default template path for the given template name
	 */
	public function getTemplatePath($templateName, $moduleName='') {
		$moduleName = str_replace(':', '/', $moduleName);
		$completeFilePath = $this->getTemplateDir(0). DIRECTORY_SEPARATOR . "modules/$moduleName/$templateName";
		if(!empty($moduleName) && file_exists($completeFilePath)) {
			return "modules/$moduleName/$templateName";
		} else {
			// Fall back lookup on actual module, in case where parent module doesn't contain actual module within in (directory structure)
			if(strpos($moduleName, '/') > 0) {
				$moduleHierarchyParts = explode('/', $moduleName);
				$actualModuleName = $moduleHierarchyParts[count($moduleHierarchyParts)-1];
				$baseModuleName = $moduleHierarchyParts[0];
				$fallBackOrder = array (
					"$actualModuleName",
					"$baseModuleName/Vtiger"
				);

				foreach($fallBackOrder as $fallBackModuleName) {
					$intermediateFallBackFileName = 'modules/'. $fallBackModuleName .'/'.$templateName;
					$intermediateFallBackFilePath = $this->getTemplateDir(0). DIRECTORY_SEPARATOR . $intermediateFallBackFileName;
					if(file_exists($intermediateFallBackFilePath)) {
						return $intermediateFallBackFileName;
					}
				}
			}
			return "modules/Vtiger/$templateName";
		}
	}

	/** @Override */
	public function assign($tpl_var, $value = null, $nocache = false) {
		// Reject unexpected value assignments.
		if ($tpl_var == 'SELECTED_MENU_CATEGORY') {
			if ($value && preg_match("/[^a-zA-Z0-9_-]/", $value, $m)) {
				return;
			}
		}
		return parent::assign($tpl_var, $value, $nocache);	
	}
	
	/**
	 * Function to display/fetch the smarty file contents
	 * @param <String> $templateName
	 * @param <String> $moduleName
	 * @param <Boolean> $fetch
	 * @return html data
	 */
	public function view($templateName, $moduleName='', $fetch=false) {
		$templatePath = $this->getTemplatePath($templateName, $moduleName);
		$templateFound = $this->templateExists($templatePath);
		
		// Logging
		if (self::$debugViewer) {
			$templatePathToLog = $templatePath;
			$qualifiedModuleName = str_replace(':', '/', $moduleName);
			// In case we found a fallback template, log both lookup and target template resolved to.
			if (!empty($moduleName) && strpos($templatePath, "modules/$qualifiedModuleName/") !== 0) {
				$templatePathToLog = "modules/$qualifiedModuleName/$templateName > $templatePath";
			}
			$this->log("VIEW: $templatePathToLog, FOUND: " . ($templateFound? "1" : "0"));
			foreach ($this->tpl_vars as $key => $smarty_variable) {
				// Determine type of value being pased.
				$valueType = 'literal';
				if (is_object($smarty_variable->value)) $valueType = get_class($smarty_variable->value);
				else if (is_array($smarty_variable->value)) $valueType = 'array';
				$this->log(sprintf("DATA: %s, TYPE: %s", $key, $valueType));
			}
		}
		// END
		
		if ($templateFound) {
			if($fetch) {
				return $this->fetch($templatePath);
			} else {
				$this->display($templatePath);
			}
			return true;
		}
		
		return false;
	}

	/**
	 * Static function to get the Instance of the Class Object
	 * @param <String> $media Layout/Media
	 * @return Vtiger_Viewer instance
	 */
	static function getInstance($media='') {
		$instance = new self($media);
		return $instance;
	}

}

function vtemplate_path($templateName, $moduleName='') {
	$viewerInstance = Vtiger_Viewer::getInstance();
	$args = func_get_args();
	return call_user_func_array(array($viewerInstance, 'getTemplatePath'), $args);
}

/**
 * Generated cache friendly resource URL linked with version of Vtiger
 */
function vresource_url($url) {
    global $vtiger_current_version;
    if (stripos($url, '://') === false) {
        // hậu tố cache-bust: tăng khi đổi JS/CSS để trình duyệt tải lại (giữ nguyên version thật)
        $url = $url .'?v='.$vtiger_current_version.'.6';
    }
    return $url;
}

/**
 * Gộp nhiều file JS local thành 1 file cache (giữ NGUYÊN thứ tự truyền vào).
 * Cache key = md5(path + mtime từng file + version) → đổi file JS tự sinh cache mới.
 * Trả URL file combined (versioned) để router static phục vụ kèm cache+gzip.
 * $files: mảng path tương đối docroot (ĐÃ theo đúng thứ tự nạp).
 */
function vcombine_js($files) {
    global $vtiger_current_version;
    $root = rtrim(getcwd(), '/');
    $paths = array(); $sig = '';
    foreach ($files as $f) {
        $f = ltrim(trim($f), '/');
        if ($f === '') continue;
        $full = $root . '/' . $f;
        if (is_file($full)) { $paths[] = $full; $sig .= $f . '|' . filemtime($full) . ';'; }
    }
    if (empty($paths)) return '';
    $key = md5($sig . $vtiger_current_version);
    $cacheDir = $root . '/cache/jscombine';
    $cacheFile = $cacheDir . '/' . $key . '.js';
    if (!is_file($cacheFile)) {
        if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);
        $buf = '';
        foreach ($paths as $p) {
            // ";\n" giữa các file: chặn ASI dính dòng cuối/đầu (cách các minifier hay dùng)
            $buf .= "\n;/* " . basename($p) . " */\n" . file_get_contents($p) . "\n";
        }
        @file_put_contents($cacheFile . '.tmp', $buf);
        @rename($cacheFile . '.tmp', $cacheFile); // atomic, tránh phục vụ file dở
    }
    return 'cache/jscombine/' . $key . '.js?v=' . $vtiger_current_version . '.6';
}

/**
 * Render toàn bộ script tags của JSResources dưới dạng 1 file gộp.
 * 43 file lib/base hardcoded (chung mọi trang) + $SCRIPTS (module-specific) +
 * 2 file cuối — gộp theo ĐÚNG thứ tự gốc. File external (http/https/CDN) giữ tag riêng.
 */
function vrender_combined_js($scripts) {
    $head = array(
        'layouts/v7/lib/jquery/purl.js',
        'layouts/v7/lib/jquery/select2/select2.min.js',
        'layouts/v7/lib/jquery/jquery.class.min.js',
        'layouts/v7/lib/jquery/jquery-ui-1.12.0.custom/jquery-ui.js',
        'layouts/v7/lib/todc/js/popper.min.js',
        'layouts/v7/lib/todc/js/bootstrap.min.js',
        'libraries/jquery/jstorage.min.js',
        'layouts/v7/lib/jquery/jquery-validation/jquery.validate.min.js',
        'layouts/v7/lib/jquery/jquery.slimscroll.min.js',
        'libraries/jquery/jquery.ba-outside-events.min.js',
        'libraries/jquery/defunkt-jquery-pjax/jquery.pjax.js',
        'libraries/jquery/multiplefileupload/jquery_MultiFile.js',
        'resources/jquery.additions.js',
        'layouts/v7/lib/bootstrap-notify/bootstrap-notify.min.js',
        'layouts/v7/lib/jquery/websockets/reconnecting-websocket.js',
        'layouts/v7/lib/jquery/jquery-play-sound/jquery.playSound.js',
        'layouts/v7/lib/jquery/malihu-custom-scrollbar/jquery.mousewheel.min.js',
        'layouts/v7/lib/jquery/malihu-custom-scrollbar/jquery.mCustomScrollbar.js',
        'layouts/v7/lib/jquery/autoComplete/jquery.textcomplete.js',
        'layouts/v7/lib/jquery/jquery.qtip.custom/jquery.qtip.js',
        'libraries/jquery/jquery-visibility.min.js',
        'layouts/v7/lib/momentjs/moment.js',
        'layouts/v7/lib/jquery/daterangepicker/moment.min.js',
        'layouts/v7/lib/jquery/daterangepicker/jquery.daterangepicker.js',
        'layouts/v7/lib/jquery/jquery.timeago.js',
        'libraries/jquery/ckeditor/ckeditor.js',
        'libraries/jquery/ckeditor/adapters/jquery.js',
        'layouts/v7/lib/anchorme_js/anchorme.min.js',
        'layouts/v7/modules/Vtiger/resources/Class.js',
        'layouts/v7/resources/helper.js',
        'layouts/v7/resources/application.js',
        'layouts/v7/modules/Vtiger/resources/Utils.js',
        'layouts/v7/modules/Vtiger/resources/validation.js',
        'layouts/v7/lib/bootbox/bootbox.js',
        'layouts/v7/modules/Vtiger/resources/Base.js',
        'layouts/v7/modules/Vtiger/resources/Vtiger.js',
        'layouts/v7/modules/Calendar/resources/TaskManagement.js',
        'layouts/v7/modules/Import/resources/Import.js',
        'layouts/v7/modules/Emails/resources/EmailPreview.js',
        'layouts/v7/modules/Vtiger/resources/Base.js',
        'layouts/v7/modules/Google/resources/Settings.js',
        'layouts/v7/modules/Vtiger/resources/CkEditor.js',
        'layouts/v7/modules/Documents/resources/Documents.js',
    );
    $tail = array(
        'layouts/v7/resources/v7_client_compat.js',
        'libraries/bootstrap/js/less.min.js',
    );
    $local = $head; $externalTags = array();
    if (is_array($scripts)) {
        foreach ($scripts as $js) {
            $src = is_object($js) ? $js->getSrc() : (string)$js;
            if ($src === '') continue;
            if (preg_match('#^(https?:)?//#i', $src)) {
                $externalTags[] = '<script type="text/javascript" src="' . htmlspecialchars($src, ENT_QUOTES) . '"></script>';
            } else {
                $local[] = $src;
            }
        }
    }
    $local = array_merge($local, $tail);
    $url = vcombine_js($local);
    $out = '';
    if ($externalTags) $out .= implode("\n", $externalTags) . "\n"; // external giữ tag riêng (thường rỗng)
    if ($url !== '') $out .= '<script type="text/javascript" src="' . $url . '"></script>';
    return $out;
}

function getPurifiedSmartyParameters($param){
    return htmlentities($_REQUEST[$param]);
}
