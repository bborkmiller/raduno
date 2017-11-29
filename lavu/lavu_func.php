<?php
/*
* Original code from Troy Kelly -- His license statement below vv
*
* Demonstration script to retrieve and present data from the POSLavu API
* This work is licensed under the Creative Commons Attribution-ShareAlike 3.0 Unported License.
* To view a copy of this license, visit http://creativecommons.org/licenses/by-sa/3.0/.
*
* Originally by Troy Kelly - Aperim Pty Ltd - http://aperim.com/
* Examples from services provided by posExtend - http://posextend.com/
*
* Change Log
* 20120915 TDK Created initial example
*/

function plGetData($arrPLSystem, $strTable=NULL, $strColumn=NULL, $strValue=NULL, $strValueMin=NULL, $strValueMax=NULL, $intLimit=50) {
	if(!$arrPLSystem) return false; // If no information about the POSLavu API Connection is passed - exit
	if(!$strTable) return false; // If no table has been selected - exit
	
	$arrAuthData=array();
	$arrControlData=array();
	$arrSearchData=array();
	
	$arrAuthData['dataname']=$arrPLSystem['dataname'];
	$arrAuthData['key']=$arrPLSystem['key'];
	$arrAuthData['token']=$arrPLSystem['token'];
	$arrControlData['valid_xml']='1';
	$arrSearchData['table']=$strTable;
	
	if(!is_null($strColumn)) $arrSearchData['column']=$strColumn;
	if(!is_null($strValue)) $arrSearchData['value']=$strValue;
	if(!is_null($strValueMin)) $arrSearchData['value_min']=$strValueMin;
	if(!is_null($strValueMax)) $arrSearchData['value_max']=$strValueMax;
	if(!is_null($intLimit)) $arrSearchData['limit']=$intLimit;
	
	$arrPostVars=array_merge($arrAuthData,$arrControlData,$arrSearchData);
	//print_r($arrPostVars);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $arrPLSystem['url']);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $arrPostVars);
	$contents = curl_exec($ch);
	
	// The POSLavu API doesn't always returned fully formed / true XML
	// So, it needs to be "tidy'd" up a little
	// There isn't anything too amazing going on here, and TIDY (http://php.net/manual/en/book.tidy.php)
	// is included on nearly all PHP installs.

	$strFixedContents=tidyParseXML($contents);

	return $strFixedContents;
}

function tidyParseXML($strStringToClean, $arrTidyConfig=NULL) {

	$arrDefaultConfig = array(
		'input-xml' => true,
		'show-body-only' => false,
		'clean' => true,
		'char-encoding' => 'utf8',
		'add-xml-decl' => true,
		'add-xml-space' => true,
		'output-html' => false,
		'output-xml' => true,
		'output-xhtml' => false,
		'numeric-entities' => false,
		'ascii-chars' => false,
		'doctype' => 'strict',
		'bare' => true,
		'fix-uri' => true,
		'indent' => true,
		'indent-spaces' => 4,
		'tab-size' => 4,
		'wrap-attributes' => true,
		'wrap' => 0,
		'indent-attributes' => true,
		'join-classes' => false,
		'join-styles' => false,
		'enclose-block-text' => true,
		'fix-bad-comments' => true,
		'fix-backslash' => true,
		'replace-color' => false,
		'wrap-asp' => false,
		'wrap-jste' => false,
		'wrap-php' => false,
		'write-back' => true,
		'drop-proprietary-attributes' => false,
		'hide-comments' => false,
		'hide-endtags' => false,
		'literal-attributes' => false,
		'drop-empty-paras' => true,
		'enclose-text' => true,
		'quote-ampersand' => true,
		'quote-marks' => false,
		'quote-nbsp' => true,
		'vertical-space' => true,
		'wrap-script-literals' => false,
		'tidy-mark' => true,
		'merge-divs' => false,
		'repeated-attributes' => 'keep-last',
		'break-before-br' => true,
	);

	if(!is_array($arrTidyConfig)) {
		$arrTidyConfig = &$arrDefaultConfig;
	}

	$tidy = new tidy();
	$strReturn = $tidy->repairString($strStringToClean, $arrTidyConfig, 'UTF8');
	unset($tidy);
	unset($arrTidyConfig);
	return($strReturn);
}

function xmlstr_to_array($xmlstr) {
	$doc = new DOMDocument();
	$doc->loadXML($xmlstr);
	$root = $doc->documentElement;
	$output = domnode_to_array($root);
	$output['@root'] = $root->tagName;
	return $output;
}

function domnode_to_array($node) {
	$output = array();
	switch ($node->nodeType) {
		case XML_CDATA_SECTION_NODE:
		case XML_TEXT_NODE:
			$output = trim($node->textContent);
			break;
		case XML_ELEMENT_NODE:
			for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) {
				$child = $node->childNodes->item($i);
				$v = domnode_to_array($child);
				if(isset($child->tagName)) {
					 $t = $child->tagName;
					 if(!isset($output[$t])) {
						 $output[$t] = array();
					 }
					 $output[$t][] = $v;
				}
				elseif($v || $v === '0') {
					 $output = (string) $v;
				}
			}
			if($node->attributes->length && !is_array($output)) { //Has attributes but isn't an array
				$output = array('@content'=>$output); //Change output into an array.
			}
			if(is_array($output)) {
				if($node->attributes->length) {
					 $a = array();
					 foreach($node->attributes as $attrName => $attrNode) {
						 $a[$attrName] = (string) $attrNode->value;
					 }
					 $output['@attributes'] = $a;
				}
				foreach ($output as $t => $v) {
					 if(is_array($v) && count($v)==1 && $t!='@attributes') {
						 if(is_array($v[0]) && count($v[0])==0){
							 $output[$t] = NULL;
						 }else{
							 $output[$t] = $v[0];
						 }
					 }
				}
			}
			break;
		}
	return $output;
}
?>