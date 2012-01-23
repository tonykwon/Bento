<?php
/*     XHTML Screen Scraper PHP Class version 0.3
    ------------------------------------------
    Copyright (c) 2004 Antonio Mota Rodrigues - antoniorodrigues_at_omnisinal.com
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
    
    Keywords: html scraper, html screen scraper, html to array, convert html tables into arrays
    extract data from web pages, get html page as array, scraping, scrapers, xhtml to array,
    xhtml2array
*/


class scraper {

    
    // ----------------------------------
    function browse($s_url, $s_user_agent) {

        //print "scraper: browse: Calling $s_url...\n";
		
        $o_ch = curl_init();

        curl_setopt ($o_ch, CURLOPT_URL, $s_url);
        curl_setopt ($o_ch, CURLOPT_USERAGENT, $s_user_agent);
        curl_setopt ($o_ch, CURLOPT_HEADER, 0);
        curl_setopt ($o_ch, CURLOPT_RETURNTRANSFER, 1);
        $s_html = curl_exec ($o_ch);
        curl_close ($o_ch);
        unset($o_ch);
        
        // Clean html ---------------------
        for ($ascii = 0; $ascii <= 9; $ascii++) $s_html = str_replace(chr($ascii), "", $s_html);
        for ($ascii = 11; $ascii < 32; $ascii++) $s_html = str_replace(chr($ascii), "", $s_html);
        for ($ascii = 127; $ascii <= 255; $ascii++) $s_html = str_replace(chr($ascii), "", $s_html);

        if (!$s_html) return false;
        return $s_html;

    } //end function
    
    
    // ------------------------------------------------------------------
    function extract ($s_html, $s_start_pattern, $s_end_pattern ) {
        
        // print "scraper: OK. extracting...\n";

        $a_result = array();

        // Cut first block -----------------------
        $i_pos = strpos($s_html, $s_start_pattern);
        $s_html = substr($s_html, $i_pos);

        // Cut last block ----------------------
        $i_pos = strpos($s_html, $s_end_pattern);
        $s_html = substr($s_html, 0, $i_pos);
		
		$s_html = str_replace($s_start_pattern,"",$s_html);
		$s_html = str_replace($s_end_pattern,"",$s_html);
		
		return $s_html; 

    } // end function

} // end class
?> 