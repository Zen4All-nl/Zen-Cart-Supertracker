<?php

/**
 * @package supertracker
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @developer Created by Mark Stephens, http://www.phpworks.co.uk
 * @developer Added keywords filters by Monika Mathé, http://www.monikamathe.com
 * @developer Added keywords processing by Andrew Berezin, http://eCommerce-Service.com
 * @developer Ported to Zen-Cart by Andrew Berezin, http://eCommerce-Service.com
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version Based $Id: supertracker.php v 3.20b 21 Mar 2006 Mark Stephens $
 * @version $Id: supertracker.php, v 1.0.0 09.05.2007 17:40 Andrew Berezin andrew@ecommerce-service.com $
 * @Security Fix - SQL Injection on Update (Line 172), Mathew Chan 25 Feb 2009
 */
class supertracker {

  function __construct() {
  }

  function update() {
    global $db, $cPath, $cPath_array, $breadcrumb;
//// **** CONFIGURATION SECTION  **** ////

    if (XTRACKING_EXCLUDED_IPS != '') {
      $ex_array = explode(',', str_replace(' ', '', XTRACKING_EXCLUDED_IPS));
      foreach ($ex_array as $key => $ex_ip) {
        if ($_SERVER['REMOTE_ADDR'] == $ex_ip) {
          return;
        }
      }
    }

    if (XTRACKING_EXCLUDED_UA != '') {
      $ex_array = explode(',', XTRACKING_EXCLUDED_UA);
      foreach ($ex_array as $key => $ex_ua) {
        if (strpos($_SERVER['HTTP_USER_AGENT'], trim($ex_ua)) !== false) {
          return;
        }
      }
    }

    if (!isset($spider_flag)) {
      $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
      $spider_flag = false;
      if (zen_not_null($user_agent)) {
        $spiders = file(DIR_WS_INCLUDES . 'spiders.txt');
        for ($i = 0, $n = sizeof($spiders); $i < $n; $i++) {
          if (zen_not_null($spiders[$i])) {
            if (is_integer(strpos($user_agent, trim($spiders[$i])))) {
              $spider_flag = true;
              break;
            }
          }
        }
      } else {
        $spider_flag = true;
      }
    }

    if (XTRACKING_EXCLUDE_BOTS == 'true' && $spider_flag) {
      return;
    }

    $user_agent = zen_db_prepare_input($_SERVER['HTTP_USER_AGENT']);

    $existing_session = false;

    $thirty_mins_ago = date('Y-m-d H:i:s', (time() - (30 * 60)));
    //Find out if this user already appears in the supertracker db table
    //First thing to try is customer_id, if they are signed in
    if (isset($_SESSION['customer_id'])) {
      $tracking_data = $db->Execute("SELECT *
                                     FROM " . TABLE_SUPERTRACKER . "
                                     WHERE customer_id ='" . (int)$_SESSION['customer_id'] . "'
                                     AND last_click > '" . $thirty_mins_ago . "'
                                     LIMIT 1");
      if (!$tracking_data->EOF) {
        $existing_session = true;
      }
    }

    //Next, we try this: compare first 2 parts of the IP address (Class B), and the browser
    //Identification String, which give us a good chance of locating the details for a given user. I reckon
    //that the chances of having more than 1 user within a 30 minute window with identical values
    //is pretty small, so hopefully this will work and should be more reliable than using Session IDs....
    if (!$existing_session) {
      $ip_parts = explode('.', $_SERVER['REMOTE_ADDR']);
      $tracking_data = $db->Execute("SELECT *
                                     FROM " . TABLE_SUPERTRACKER . "
                                     WHERE browser_string ='" . zen_db_input($user_agent) . "'
                                     AND ip_address like '" . $ip_parts[0] . '.' . $ip_parts[1] . "%'
                                     AND last_click > '" . $thirty_mins_ago . "'
                                     LIMIT 1");
      if (!$tracking_data->EOF) {
        $existing_session = true;
      }
    }

    //If that didn't work, and we have something in the cart, we can use that to try and find the
    //record instead. Obviously, people with things in their cart don't just appear from nowhere!
    if (!$existing_session) {
      if ($_SESSION['cart']->count_contents() > 0) {
        $tracking_data = $db->Execute("SELECT *
                                       FROM " . TABLE_SUPERTRACKER . "
                                       WHERE cart_total ='" . $_SESSION['cart']->show_total() . "'
                                       AND last_click > '" . $thirty_mins_ago . "'
                                       LIMIT 1");
        if (!$tracking_data->EOF) {
          $existing_session = true;
        }
      }
    }

    //Having worked out if we have a new or existing user session lets record some details....!
    if ($existing_session) {
      //Existing tracked session, so just update relevant existing details
      $completed_purchase = $tracking_data->fields['completed_purchase'];
      $categories_viewed = unserialize($tracking_data->fields['categories_viewed']);
      $cart_contents = unserialize($tracking_data->fields['cart_contents']);
      $cart_total = $tracking_data->fields['cart_total'];
      $order_id = $tracking_data->fields['order_id'];
      if (isset($_SESSION['customer_id'])) {
        $customer_id = (int)$_SESSION['customer_id'];
      } else {
        $customer_id = (int)$tracking_data->fields['customer_id'];
      }
      //Find out if the customer has added something to their cart for the first time
      if ($_SESSION['cart']->count_contents() > 0) {
        $tracking_data->fields['added_cart'] = 'true';
      }

      //Has a purchase just been completed?
      if (($_GET['main_page'] == FILENAME_CHECKOUT_SUCCESS) && ($completed_purchase != 'true')) {
        $completed_purchase = 'true';
        $order_result = $db->Execute("SELECT orders_id
                                      FROM " . TABLE_ORDERS . "
                                      WHERE customers_id = '" . $customer_id . "'
                                      ORDER BY date_purchased DESC");
        if (!$order_result->EOF) {
          $order_id = $order_result->fields['orders_id'];
        }
      }

      //If customer is looking at a product, add it to the list of products viewed
      if ($_GET['main_page'] == 'product_info' || $_GET['main_page'] == 'document_info') {

        if (!strstr($tracking_data->fields['products_viewed'], '*' . (int)$_GET['products_id'] . '?')) {
          //Product hasn't been previously recorded as viewed
          $tracking_data->fields['products_viewed'] .= '*' . (int)$_GET['products_id'] . '?';
        }
      }

      //Store away their cart contents
      //But, the cart is dumped at checkout, so we don't want to overwrite the stored cart contents
      //In this case
      $current_cart_contents = serialize($_SESSION['cart']->contents);
      if (strlen($current_cart_contents) > 6) {
        $cart_contents = $current_cart_contents;
        $cart_total = $_SESSION['cart']->show_total();
      }

      //If we are on index.php, but looking at category results, make sure we record which category
      if ($_GET['main_page'] == FILENAME_DEFAULT) {
        if (isset($_GET['cPath'])) {
          $cat_id = zen_db_prepare_input($_GET['cPath']);
          $cat_id_array = explode('_', $cat_id);
          $cat_id = $cat_id_array[sizeof($cat_id_array) - 1];
          $categories_viewed[$cat_id] = 1;
        }
      }
//var_dump($tracking_data->fields);echo '<br />';
      $categories_viewed = serialize($categories_viewed);

      $page_title = zen_db_input($breadcrumb->last() . (isset($_GET['page']) ? ' (' . sprintf(PREVNEXT_TITLE_PAGE_NO, $_GET['page']) . ')' : ''));

      $db->Execute("UPDATE " . TABLE_SUPERTRACKER . "
                    SET last_click = NOW(),
                        exit_page='" . zen_db_input(zen_db_prepare_input(urldecode($_SERVER['REQUEST_URI']))) . "',
                        exit_page_name='" . $page_title . "',
                        num_clicks=num_clicks+1,
                        added_cart='" . zen_db_input($tracking_data->fields['added_cart']) . "',
                        categories_viewed='" . zen_db_input($categories_viewed) . "',
                        products_viewed='" . zen_db_input($tracking_data->fields['products_viewed']) . "',
                        customer_id='" . (int)$customer_id . "',
                        completed_purchase='" . zen_db_input($completed_purchase) . "',
                        cart_contents='" . zen_db_input($cart_contents) . "',
                        cart_total = '" . zen_db_input($cart_total) . "',
                        order_id = '" . (int)$order_id . "'
                    WHERE tracking_id='" . (int)$tracking_data->fields['tracking_id'] . "'");

    } else {
      //New vistor, so record referrer, etc
      //Next line defines pages on which a new visitor should definitely not be recorded
      $prohibited_pages = 'login,checkout_shipping,checkout_payment,checkout_process,checkout_confirmation,checkout_success';
//      $prohibited_pages = '';
      if (!strpos($prohibited_pages, $_GET['main_page'])) {
        if (isset($_SERVER['HTTP_REFERER'])) {
          $refer_data = zen_db_prepare_input(urldecode($_SERVER['HTTP_REFERER']));
          $refer_url_array = parse_url($refer_data);
          $referrer = $refer_url_array['scheme'] . '://' . $refer_url_array['host'] . $refer_url_array['path'];
          $referrer_query_string = $refer_url_array['query'];
        } else {
          $referrer = '';
          $referrer_query_string = '';
        }

        if(is_file(DIR_WS_INCLUDES . "GeoLiteCity.dat")) {
          include(DIR_WS_INCLUDES . "geoipcity.inc");
          $gi = geoip_open(DIR_WS_INCLUDES . "GeoLiteCity.dat", GEOIP_STANDARD);
          $record = geoip_record_by_addr($gi, $_SERVER['REMOTE_ADDR']);
          $country_name = $record->country_name;
          $country_code = strtolower($record->country_code);
          $region_name = $GEOIP_REGION_NAME[$record->country_code][$record->region];
          $city_name = $record->city;
//          $country_code = strtolower(geoip_country_code_by_addr($gi, $_SERVER['REMOTE_ADDR']));
          geoip_close($gi);
        } else {
          $country_code = '--';
        }

        $this_page = zen_db_input(zen_db_prepare_input(urldecode($_SERVER['REQUEST_URI'])));

        $page_title = zen_db_input($breadcrumb->last() . (isset($_GET['page']) ? ' (' . sprintf(PREVNEXT_TITLE_PAGE_NO, $_GET['page']) . ')' : ''));

        $products_viewed = '';
        //If customer is looking at a product, add it to the list of products viewed
        if ($_GET['main_page'] == 'product_info' || $_GET['main_page'] == 'document_info') {
          if (!strstr($tracking_data->fields['products_viewed'], '*' . (int)$_GET['products_id'] . '?')) {
            //Product hasn't been previously recorded as viewed
            $products_viewed .= '*' . (int)$_GET['products_id'] . '?';
          }
        }

        $query = "INSERT INTO " . TABLE_SUPERTRACKER . " (ip_address, browser_string, country_code,country_name,country_region,country_city, referrer, referrer_query_string, landing_page, landing_page_name, exit_page, exit_page_name, time_arrived, last_click, products_viewed)
                  VALUES ('" . zen_db_input($_SERVER['REMOTE_ADDR']) . "', '" . zen_db_input($user_agent) . "', '" . zen_db_input($country_code) . "', '" . zen_db_input($country_name) . "', '" . zen_db_input($region_name) . "', '" . zen_db_input($city_name) . "', '" . zen_db_input($referrer) . "', '" . zen_db_input($referrer_query_string) . "', '" . $this_page . "', '" . $page_title . "', '" . $this_page . "', '" . $page_title . "', NOW(), NOW(), '" . zen_db_input($products_viewed) . "')";
        $db->Execute($query);

      }//end if for prohibited pages
    }//end else
  }

//End function update
}

//End Class