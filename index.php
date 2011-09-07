<?php

/**
 * Uncomment this for use with Wolf 0.7.0 and above
 * Security measure for Wolf 0.7.0
 */
  if (!defined('IN_CMS')) { exit(); }
 


/**
 * This plugin allows you to automate article excerpts by number of characters.
 *
 * @package plugins
 * @subpackage truncate
 *
 * @author Dejan Andjelkovic <dejan79@gmail.com>
 * @version 0.0.2
 * @since Wolf version 0.6.0
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Dejan Andjelkovic, 2011+
 */

Plugin::setInfos(array(
    'id'          => 'truncate',
    'title'       => __('Truncate'),
    'description' => __('Automated excerpts'),
    'version'     => '0.0.2',
    'license'     => 'GPL',
    'author'      => 'Dejan Andjelkovic',
    'website'     => 'http://project79.net/',
    'update_url'  => 'http://project79.net/plugin-versions.xml',
    'require_wolf_version' => '0.6.0'
));

/*
 * Example:
 * $myarticles = truncate($article->content,300);
 * echo $myarticles;
 */

function truncate($text, $length, $suffix = '&hellip;', $isHTML = true){
		$i = 0;
		$simpleTags=array('br'=>true,'hr'=>true,'input'=>true,'image'=>true,'link'=>true,'meta'=>true);
		$tags = array();
		if($isHTML){
			preg_match_all('/<[^>]+>([^<]*)/', $text, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
			foreach($m as $o){
				if($o[0][1] - $i >= $length)
					break;
				$t = substr(strtok($o[0][0], " \t\n\r\0\x0B>"), 1);
				// test if the tag is unpaired, then we mustn't save them
				if($t[0] != '/' && (!isset($simpleTags[$t])))
					$tags[] = $t;
				elseif(end($tags) == substr($t, 1))
					array_pop($tags);
				$i += $o[1][1] - $o[0][1];
			}
		}

		// output without closing tags
		$output = substr($text, 0, $length = min(strlen($text),  $length + $i));
		// closing tags
		$output2 = (count($tags = array_reverse($tags)) ? '</' . implode('></', $tags) . '>' : '');

		// Find last space or HTML tag (solving problem with last space in HTML tag eg. <span class="new">)
		$postemp = preg_split('/<.*>| /', $output, -1, PREG_SPLIT_OFFSET_CAPTURE);
		$postemp2 = end($postemp);
		$pos = (int)end($postemp2);
		// Append closing tags to output
		$output.=$output2;

		// Get everything until last space
		$one = substr($output, 0, $pos);
		// Get the rest
		$two = substr($output, $pos, (strlen($output) - $pos));
		// Extract all tags from the last bit
		preg_match_all('/<(.*?)>/s', $two, $tags);
		// Add suffix if needed
		if (strlen($text) > $length) { $one .= $suffix; }
		// Re-attach tags
		$output = $one . implode($tags[0]);

		//added to remove  unnecessary closure
		$output = str_replace('</!-->','',$output);

		return $output;
}