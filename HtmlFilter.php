<?php

/*
	XSS prevention code, slightly modified from: 
	http://framework.korsengineering.com/?Doc_Comments.show&file=libs/HtmlFilter.php
*/
class HtmlFilter{ //Version: 3.0
    var $OPENS = 1;
    var $CLOSES = 2;
    var $SINGLE = 3;

    var $goodTagsRE = 'i|b|em|strong|a|hr|ul|ol|li|img|span|div|br|font|h1|h2|h3|h4|h5|h6|sub|sup|p';
    var $goodAttrs = array('href', 'src', 'class', 'alt', 'title', 'style', 'size', 'target', 'align', 'border', 'id', 'width', 'height', 'rel');
    var $goodProtocolsRE = 'http|ftp|mailto|https|sftp|torrent';
    var $defaultProtocol = 'http://'; //prepended to URLs without above protocols
    var $linkAttrs = array('href', 'src'); //these are checked for protocols
    var $wrapLength = 80;
    var $encoding = 'UTF-8'; //used for htmlspecialchars
    var $php523 = false;
    
    function HtmlFilter($goodTagsRE = NULL, $goodAttrs = NULL, $goodProtocolsRE = NULL, $wrapLength = NULL){
        if ($goodTagsRE != NULL) { $this->goodTagsRE = $goodTagsRE; }
        if ($goodAttrs != NULL) { $this->goodAttrs = $goodAttrs; }
        if ($goodProtocolsRE != NULL) { $this->goodProtocolsRE = $goodProtocolsRE; }
        if ($wrapLength != NULL) { $this->wrapLength = $wrapLength; }
        if (version_compare(phpversion(), '5.2.2')==1){
        	$this->php523=true;
        }
    }

    function cleanBreak($html) {
        $html = $this->clean($html);
        if ($this->wrapLength < 1) {
            return $html;
        } else {
            return wordwrap($html, $this->wrapLength, ' ', TRUE);
        }
    }

    function escape($htmls){ //need for preg_replace_callback in clean
    	if($this->php523){
	        return @htmlspecialchars($htmls[0], ENT_QUOTES, $this->encoding, FALSE);
    	}else{
	        return @htmlspecialchars($htmls[0], ENT_QUOTES, $this->encoding);
    	}
    }
    
    function clean($html){
        $html = preg_replace_callback('#(<!--.*?-->)#s', array($this, 'escape'), $html); //escape comments
        $html = str_replace('<br>', '<br />', $html); //common non-compliant code replacement
        //match approved tags in all possible forms
        $tagOpenRE = "<(?:$this->goodTagsRE)(?:\s.*?)?/?>"; //opening or empty
        $tagCloseRE = "</(?:$this->goodTagsRE)\s*?>"; //closing
        $parts = preg_split("#($tagOpenRE|$tagCloseRE)#si", $html, -1, PREG_SPLIT_DELIM_CAPTURE);

        $openTags = array(); //a stack to find mismatched tags
        foreach ($parts as $key => $part) {
            if ($key % 2 == 0) { //even - text, possibly with bad tags
            	if($this->php523){
	                $parts[$key] = @htmlspecialchars($part, ENT_QUOTES, $this->encoding, FALSE);
            	}else{
	                $parts[$key] = @htmlspecialchars($part, ENT_QUOTES, $this->encoding);
            	}
            } else { //odd - an aproved tag
                $tagInfo = $this->parseTag($part);
                $worthyTag = TRUE;
                foreach ($tagInfo['attrs'] as $attrName => $attrValue) {
                    if (!in_array($attrName, $this->goodAttrs)) {
                        $worthyTag = FALSE;
                    }
                    if (in_array($attrName, $this->linkAttrs)) { //if attribute is a link, enforce a good protocol
                        if (!preg_match("#^$this->goodProtocolsRE:#i", $attrValue)) {
                            $tagInfo['attrs'][$attrName] = $this->defaultProtocol.$attrValue;
                        }
                    }
                }
                if ($tagInfo['type'] == $this->OPENS) {
                    $openTags[] = array('name' => $tagInfo['name'], 'key' => $key);
                } elseif ($tagInfo['type'] == $this->CLOSES) {
                    $open = $openTags[sizeof($openTags) - 1];
                    if ($open['name'] == $tagInfo['name']) {
                        array_pop($openTags); //this tag closes an open one
                    } else {
                        $worthyTag = FALSE; //we expect another tag
                    }
                }
                if ($worthyTag) {
                    $parts[$key] = $this->unparseTag($tagInfo);
                } else {
	            	if($this->php523){
    	                $parts[$key] = @htmlspecialchars($part, ENT_QUOTES, $this->encoding, FALSE);
	            	}else{
    	                $parts[$key] = @htmlspecialchars($part, ENT_QUOTES, $this->encoding);
	            	}
                }
            }
        }
        foreach ($openTags as $open) { //comment out all unclosed tags
            $key = $open['key'];
        	if($this->php523){
            	$parts[$key] = @htmlspecialchars($parts[$key], ENT_QUOTES, $this->encoding, FALSE);
        	}else{
            	$parts[$key] = @htmlspecialchars($parts[$key], ENT_QUOTES, $this->encoding);
        	}
        }
        return join('', $parts);
    }

    function unparseTag($tagInfo){ //escapes only attr content, the rest is handled in clean()
        $attrs = '';
        foreach ($tagInfo['attrs'] as $name => $value) {
        	if($this->php523){
	            $value = @htmlspecialchars($value, ENT_QUOTES, $this->encoding, FALSE);
        	}else{
	            $value = @htmlspecialchars($value, ENT_QUOTES, $this->encoding);
        	}
            $attrs .= ' '.$name.'="'.$value.'"';
        }

        if ($tagInfo['type'] == $this->OPENS) {
            return "<$tagInfo[name]$attrs>";
        } elseif ($tagInfo['type'] == $this->CLOSES) {
            return "</$tagInfo[name]>";
        } else {
            return "<$tagInfo[name]$attrs />";
        }
    }

    function parseTag($tagStr){
        $tagInfo = array();
        if ($tagStr{1} == '/') {
            $tagInfo['type'] = $this->CLOSES;
        } elseif ($tagStr{strlen($tagStr) - 2} == '/') {
            $tagInfo['type'] = $this->SINGLE;
        } else {
            $tagInfo['type'] = $this->OPENS;
        }

        $matches = array();
        preg_match("#^</?([\w\d_:]+)#", $tagStr, $matches);
        $tagInfo['name'] = $matches[1];
        $tagInfo['attrs'] = array();
        if ($tagInfo['type'] != $this->CLOSES) {
            $attrsRE = '#\s+([\w\d_:]+)\w*=\w*(["|\'])(.*?)\2#';
            $offset = strlen($tagInfo['name']) + 1;
            preg_match_all($attrsRE, $tagStr, $matches, PREG_SET_ORDER, $offset);

            foreach ($matches as $match) {
                $tagInfo['attrs'][ $match[1] ] = $match[3];
            }
        }
        return $tagInfo;
    }
}