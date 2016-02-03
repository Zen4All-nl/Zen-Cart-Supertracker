<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function draw_geo_graph($geo_hits, $country_names, $total_hits) {

  $contents = array();
  $contents[] = array('text' => '<table cellpadding="0" cellspacing="0" border="0" width="100%">' . "\n");
  $contents[] = array('text' => '<tr class="dataTableRow">' . "\n");
  $contents[] = array('text' => '<td class="dataTableContent">' . "\n");
  $contents[] = array('text' => '<table cellpadding="2px" cellspacing="0" border="0">');
  $max_pixels = 600;
  arsort($geo_hits);
  foreach ($geo_hits as $country_code => $num_hits) {
    $country_name = $country_names[$country_code];
    $bar_length = ($num_hits / $total_hits) * $max_pixels;
    $percent_hits = round(($num_hits / $total_hits) * 100, 2);
    //Create a random colour for each bar
    srand((double)microtime() * 1000000);
    $r = dechex(rand(0, 255));
    $g = dechex(rand(0, 255));
    $b = dechex(rand(0, 255));

    $contents[] = array('text' => '<tr class="dataTableRow"><td class="dataTableContent">' . $country_name . ': </td><td class="dataTableContent"><div style="display:justify;background:#' . $r . $g . $b . '; border:1px solid #000; height:10px; width:' . $bar_length . '"></div></td><td class="dataTableContent">' . $percent_hits . '%</td></tr>');
  }
  $contents[] = array('text' => '</table></td></tr></table>');
  return $contents;
}

function build_geo_graph($geo_hits, $country_names, $total_hits) {
  $max_pixels = 400;
  arsort($geo_hits);
  $i = 0;
  $geo_graph = array();
  foreach ($geo_hits as $country_code => $num_hits) {
    $bar_length = ($num_hits / $total_hits) * $max_pixels;
    $percent_hits = round(($num_hits / $total_hits) * 100, 2);
    //Create a random colour for each bar
    srand((double)microtime() * 1000000);
    $r = dechex(rand(0, 255));
    $g = dechex(rand(0, 255));
    $b = dechex(rand(0, 255));
    $geo_graph[$i]['country'] = ($country_names[$country_code] != '' ? $country_names[$country_code] : $country_code);
    $geo_graph[$i]['hits_graph'] = '<div style="display:justify; border:1px solid #000; height:10px; background:#' . $r . $g . $b . '; width:' . $bar_length . '"></div>';
    $geo_graph[$i]['hits'] = $percent_hits . '%';
    $i++;
  }
  return $geo_graph;
}

function supertracker_get_arg($query, $argname, $convert = false) {
  $argvalue = false;
  $vars = explode('&', $query);
  foreach ($vars as $k => $var) {
    if ($var == "") {
      continue;
    }
    if (preg_match('`^' . $argname . '=(.*)$`i', $var, $argv) == 0) {
      continue;
    }
    if (!isset($argv[1])) {
      return(false);
    }
//    $argvalue = rawurldecode($argv[1]);
    $argvalue = $argv[1];
    if ($convert) {
      $eval_str = "\$argvalue = " . sprintf($convert, $argvalue) . ';';
      eval($eval_str);
    }
    break;
  }
  return($argvalue);
}

function supertracker_get_keywords($days = false) {
  global $db;
  require('includes/searchengines.php');
  $keywords_used = array();
  $keywords_row = $db->Execute("SELECT referrer, referrer_query_string
                                FROM " . TABLE_SUPERTRACKER . "
                                WHERE referrer_query_string > '' " .
          ($days ? "AND DATE_ADD(time_arrived, INTERVAL " . $days . " DAY) >= NOW() " : ""));
//  var_dump($keywords_row->RecordCount());echo '<br />';
  $cc = array();
  while (!$keywords_row->EOF) {
    $cc['total'] ++;
    $raw_search = false;
    $url = parse_url(urldecode($keywords_row->fields["referrer"] . '?' . $keywords_row->fields["referrer_query_string"]));
    if (empty($url["query"])) {
      continue;
    }
    foreach ($search as $key => $val) {
      foreach ($val['rule'] as $pattern) {
        if (preg_match('`' . $pattern . '`i', $url["host"] . $url["path"], $regs) == 0) {
          continue;
        }
        $raw_search = supertracker_get_arg($url["query"], $val["argv"], @$val["conv"]);
        /*
          if(isset($val["encode"]) && $val["encode"] != 'utf-8') {
          $raw_search = iconv($val["encode"], 'utf-8', $raw_search);
          }
          $raw_search = iconv('utf-8', 'windows-1251', $raw_search);
         */
        $cc['raw_search'] ++;
//  if(is_ruUTF8($raw_search)){var_dump($cc['total'], $raw_search, is_ruUTF8($raw_search), UTF8toCP1251($raw_search));echo '<br />';}
        if (CHARSET == 'windows-1251' && is_ruUTF8($raw_search)) {
          $raw_search = UTF8toCP1251($raw_search);
        }
        break 2;
      }
    }
    if ($raw_search === false) {
      $cc['raw_no_search'] ++;
      $key_array = explode('&', $keywords_row->fields['referrer_query_string']);
      for ($i = 0; $i < sizeof($key_array); $i++) {
        $keywords = false;
        if (substr($key_array[$i], 0, 2) == 'q=') {
          $keywords = str_replace('+', ' ', substr($key_array[$i], 2, strlen($key_array[$i]) - 2));
        }
        if (substr($key_array[$i], 0, 2) == 'p=') {
          $keywords = str_replace('+', ' ', substr($key_array[$i], 2, strlen($key_array[$i]) - 2));
        }
        if (strstr($key_array[$i], 'query=')) {
          $keywords = str_replace('+', ' ', substr($key_array[$i], 6, strlen($key_array[$i]) - 6));
        }
        if (strstr($key_array[$i], 'keyword=')) {
          $keywords = str_replace('+', ' ', substr($key_array[$i], 8, strlen($key_array[$i]) - 8));
        }
        if (strstr($key_array[$i], 'keywords=')) {
          $keywords = str_replace('+', ' ', substr($key_array[$i], 9, strlen($key_array[$i]) - 9));
        }
      }
    }
    $keywords = trim($raw_search);
    if (!empty($keywords)) {
      $keywords_used[$keywords] ++;
    }
    $keywords_row->MoveNext();
  }
//var_dump($cc);echo '<br />';
  arsort($keywords_used);
  $keywords_data = array();
  $i = 0;
  foreach ($keywords_used as $kw => $hits) {
    $keywords_data[$i]['keywords'] = $kw;
    $keywords_data[$i]['hits'] = $hits;
    $i++;
  }
  return $keywords_data;
}
