<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');

// --- Get extension configuration ---
$extConf = array();
$extKey = 'iconfont';

if ( strlen($_EXTCONF) ) {
	$extConf = unserialize($_EXTCONF);
}
// default is fontawesome
if ( ! isset($extConf['iconFont']) || trim($extConf['iconFont']) == '' ) {
	$extConf['iconFont'] = 'fontawesome';
}

// --- Fontawesome or custom ---
switch ( $extConf['iconFont'] ) {
	case 'custom':
		if ( isset($extConf['customIconDefinitionFile']) && file_exists(\TYPO3\CMS\Core\Core\Environment::getPublicPath() .'/'. $extConf['customIconDefinitionFile']) ) {
			$iconDefinitionFile = $extConf['customIconDefinitionFile'];
		} else {
			$iconDefinitionFile = '';
		}
		$fontCssFile = isset($extConf['customFontCssFile'])?$extConf['customFontCssFile']:'';
		$stylesheetDir = isset($extConf['customStylesheetDirectory'])?$extConf['customStylesheetDirectory']:'';
		$iconsInOptionTags = 0; // may be enabled via TCEFORM for custom fonts
		break;

	default:
		$iconDefinitionFile = 'typo3conf/ext/iconfont/ext_tables_fontawesome.php';
		$fontCssFile = 'typo3conf/ext/iconfont/Resources/Public/Lib/font-awesome/css/font-awesome.min.css';
		$stylesheetDir = 'EXT:iconfont/Resources/Public/Css/';
		$iconsInOptionTags = 1;
		break;
}

// --- Load, set configuration ---
if ( $iconDefinitionFile != '' ) {
	$iconFontOption = array(array('', 0));

	// Load array with icons (overwrites $iconFontOption)
	include(\TYPO3\CMS\Core\Core\Environment::getPublicPath().'/' . $iconDefinitionFile);

	// Add field
	$tempColumn = array(
		'tx_iconfont_icon' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:iconfont/Resources/Private/Language/locallang_db.xlf:tt_content.tx_iconfont_icon',
			'config' => array (
				'type' => 'select',
				'renderType' => 'selectSingle',
				'default' => '0',
				'size' => 1,
				'maxitems' => 1,
				'items' => $iconFontOption,
				'suppress_icons' => 1, // don't show icons below select box
				'iconsInOptionTags' => $iconsInOptionTags, // show icons in select options
			)
		)
	);

	// Add to tt_content
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $tempColumn, 1);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('tt_content', 'header', 'tx_iconfont_icon', 'after:header_layout');
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('tt_content', 'headers', 'tx_iconfont_icon', 'after:header_layout');

	// Add to pages
	if ( isset($extConf['showIconFieldOnPage']) && $extConf['showIconFieldOnPage'] == 1 ) {
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $tempColumn, 1);
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('pages', 'title', '--linebreak--, tx_iconfont_icon', 'after:subtitle');
	}

	// Default TS for iconfont
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extKey, 'Configuration/TypoScript', 'Icon Font');

	if (TYPO3_MODE == "BE")   {
		if ( $stylesheetDir != '' ) {
			$GLOBALS['TBE_STYLES']['skins'][$extKey]['name'] = $extKey;
			$GLOBALS['TBE_STYLES']['skins'][$extKey]['stylesheetDirectories']['css'] = $stylesheetDir;
		}

	}

	// Page TSconfig
	if ( $fontCssFile != '' ) {
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
RTE.default.contentCSS >
RTE.default.contentCSS.default = typo3/sysext/rtehtmlarea/res/contentcss/default.css
RTE.default.contentCSS.iconfont = ' . $fontCssFile . '
');
	}

}
