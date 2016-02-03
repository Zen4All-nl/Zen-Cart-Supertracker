<?php
/*This file is part of BBClone (The PHP web counter on steroids)
 *
 * $Header: /cvs/bbclone/lib/search.php,v 1.127 2005/11/20 19:40:29 olliver Exp $
 *
 * Copyright (C) 2001-2005, the BBClone Team (see file doc/authors.txt
 * distributed with this library)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * See doc/copying.txt for details
 */

$search = array(
  "YandexPage" => array(
    "icon" => "yandex",
    "title" => "Yandex",
    "rule" => array(
      "^yandex.ru/yandpage",
      "^www.yandex.ru/yandpage",
    ),
//    "argv" => "qs",
//    "conv" => "rawurldecode(supertracker_get_arg('%s','text'))",
//    "encode" => "koi8-r",
    "argv" => "text",
    "encode" => "windows-1251",
  ),
  "Yandex" => array(
    "icon" => "yandex",
    "title" => "Yandex",
    "rule" => array(
      "^yandex.ru/yandsearch",
      "^www.yandex.ru/yandsearch",
    ),
    "argv" => "text",
    "encode" => "windows-1251",
  ),
  "YandexDirect" => array(
    "icon" => "yandex",
    "title" => "DirectYandex",
    "rule" => array(
      "^direct.yandex.ru/search",
    ),
    "argv" => "text",
    "encode" => "windows-1251",
  ),
  "YandexHighlight" => array(
    "icon" => "yandex",
    "title" => "Yandex Highlight",
    "rule" => array(
      "^hghltd.yandex.com/yandbtm",
    ),
    "argv" => "text",
    "encode" => "windows-1251",
  ),
  "YandexAddress" => array(
    "icon" => "yandex",
    "title" => "Yandex Address",
    "rule" => array(
      "^adresa.yandex.ru/search.xml",
    ),
    "argv" => "what",
    "encode" => "windows-1251",
  ),
  "YandexCatalog" => array(
    "icon" => "yandex",
    "title" => "Yandex Catalog",
    "rule" => array(
      "^search.yaca.yandex.ru/yandsearch",
    ),
    "argv" => "text",
    "encode" => "windows-1251",
  ),
  "YandexImages" => array(
    "icon" => "yandex",
    "title" => "Yandex Images",
    "rule" => array(
      "^images.yandex.ru/yandsearch",
    ),
    "argv" => "text",
    "encode" => "windows-1251",
  ),
  "YandexImagesPade" => array(
    "icon" => "yandex",
    "title" => "Yandex Images",
    "rule" => array(
      "^images.yandex.ru/yandpage",
    ),
    "argv" => "qs",
    "conv" => "rawurldecode(supertracker_get_arg('%s','text'))",
    "encode" => "koi8-r",
  ),
  /*
  "google.com" => array(
    "icon" => "google",
    "title" => "Google",
    "rule" => array(
      "^www.google.com/search",
      "^google.com/search",
    ),
    "argv" => "q"
  ),
  */
  "google" => array(
    "icon" => "google",
    "title" => "Google",
    "rule" => array(
      "^www.google.[a-z\.]+/search",
      "^google.[a-z\.]+/search",
      "^www.google.[a-z\.]+/ie",
      "^google.[a-z\.]+/ie",
    ),
    "argv" => "q"
  ),  
  "yahooimages" => array(
    "icon" => "yahoo",
    "title" => "Yahoo Images",
    "rule" => array(
      "^images.search.yahoo.com/search/images/view",
    ),
    "argv" => "p",
  ),
  "mailru" => array(
    "icon" => "mailru",
    "title" => "mail.ru",
    "rule" => array(
      "^www.go.mail.ru/search",
      "^go.mail.ru/search",
    ),
    "argv" => "q",
    "encode" => "windows-1251",
  ),
  "rambler" => array(
    "icon" => "rambler",
    "title" => "rambler.ru",
    "rule" => array(
      "^www.rambler.ru/srch",
      "^rambler.ru/srch",
      "^search.rambler.ru/srch",
    ),
    "argv" => "words",
    "encode" => "windows-1251",
  ),
  "rambler_icq" => array(
    "icon" => "rambler",
    "title" => "ramblerICQ.ru",
    "rule" => array(
      "^search.rambler.ru/cgi-bin/icqweb",
    ),
    "argv" => "words",
    "encode" => "windows-1251",
  ),
  "yagora.ru" => array(
    "icon" => "rambler",
    "title" => "rambler.ru",
    "rule" => array(
      "^www.yagora.ru/",
      "^yagora.ru/",
    ),
    "argv" => "search",
    "encode" => "windows-1251",
  ),
  "altatron.ru" => array(
    "icon" => "altatron",
    "title" => "altatron.ru",
    "rule" => array(
      "^www.altatron.ru/new/alta_page",
      "^altatron.ru/new/alta_page",
    ),
    "argv" => "SEARCHFOR",
    "encode" => "koi8-r",
  ),
  "poisk.ru" => array(
    "icon" => "altatron",
    "title" => "altatron.ru",
    "rule" => array(
      "^www.poisk.ru/cgi-bin/poisk",
      "^poisk.ru/cgi-bin/poisk",
    ),
    "argv" => "text",
    "encode" => "windows-1251",
  ),
  "search.ukr.net" => array(
    "icon" => "ukr_net",
    "title" => "search.ukr.net",
    "rule" => array(
      "^www.search.ukr.net/search.php",
      "^search.ukr.net/search.php",
    ),
    "argv" => "search_query",
    "encode" => "windows-1251",
  ),
);
?>